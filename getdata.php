<?php
// Connexion à la base de données
include('connect.php');

header('Content-Type: application/json');

// Clé de sécurité
$passid = "SalutJeSuisUnMotDePassePourGet";

// Vérification du mot de passe
if (isset($_GET['passid']) && $_GET['passid'] == $passid) {
    // Vérifier si le paramètre 'table' est présent
    if (isset($_GET['table'])) {
        $table = htmlspecialchars($_GET['table']);

        include('tables.php');

        // Vérifier si la table demandée est autorisée
        if (in_array($table, $tables_autorisees)) {
            $champs_disponibles = $champs_par_table[$table];

            // Champs à afficher
            $champs_a_afficher = isset($_GET['fields']) 
                ? array_map('htmlspecialchars', explode(',', $_GET['fields'])) 
                : $champs_disponibles;

            // Ajouter des champs supplémentaires pour les jointures
            if ($table == 'Avis' && !in_array('nomOeuvre', $champs_a_afficher)) {
                $champs_a_afficher[] = 'nomOeuvre'; // Inclure le champ de la table Oeuvre
            }

            if (empty($champs_a_afficher)) {
                echo json_encode(["error" => "Aucun champ valide spécifié pour l'affichage"]);
                exit;
            }

            // Construire la liste des champs avec leurs préfixes
            $champs_str = implode(", ", array_map(function ($champ) use ($table, $champs_par_table) {
                if ($table == 'Avis' && $champ == 'nomOeuvre') {
                    return "Oeuvre.nomOeuvre"; // Champ venant de la jointure
                }
                return $table . "." . $champ; // Champs standards
            }, $champs_a_afficher));

            // Construction de la requête SQL
            $sql = "SELECT $champs_str FROM $table";

            // Ajouter une jointure si nécessaire
            if ($table == 'Avis') {
                $sql .= " LEFT JOIN Oeuvre ON Avis.idOeuvre = Oeuvre.idOeuvre";
            }

            $conditions = [];
            $params = [];

            // Ajouter les filtres dynamiques pour les colonnes disponibles
            foreach ($champs_disponibles as $champ) {
                if (isset($_GET[$champ])) {
                    $conditions[] = "$table.$champ = :$champ";
                    $params[":$champ"] = htmlspecialchars($_GET[$champ]);
                }
            }

            // Ajouter les conditions WHERE si nécessaire
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);

                // Récupérer les résultats
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Retourner les résultats en JSON
                if ($data) {
                    echo json_encode($data);
                } else {
                    echo json_encode(["message" => "Aucune donnée trouvée"]);
                }
            } catch (PDOException $e) {
                // Gérer les erreurs SQL
                echo json_encode(["error" => "Erreur : " . $e->getMessage()]);
            }
        } else {
            echo json_encode(["error" => "Table non autorisée"]);
        }
    } else {
        echo json_encode(["error" => "Paramètre 'table' manquant"]);
    }
} else {
    echo json_encode(["error" => "Mot de passe incorrect"]);
}
?>