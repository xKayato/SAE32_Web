<?php
// Connexion à la base de données
include('connect.php');

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

// Clé de sécurité pour vérifier l'authenticité de la requête
$passid = "Aij84k_-2RRS6d51dq6FSd698-(_45";

// Vérifier la clé de sécurité
if (isset($_GET['passid']) && $_GET['passid'] == $passid) {
    // Vérifier si le paramètre 'table' est présent
    if (isset($_GET['table'])) {
        $table = htmlspecialchars($_GET['table']); // Sécuriser les paramètres de table

        include('tables.php');

        // Vérifier si la table demandée est autorisée
        if (in_array($table, $tables_autorisees)) {
            $champs_disponibles = $champs_par_table[$table];

            // Champs à afficher
            $champs_a_afficher = isset($_GET['fields']) 
                ? array_map('htmlspecialchars', explode(',', $_GET['fields'])) 
                : $champs_disponibles;

            // Ajouter des champs supplémentaires pour les jointures
            if ($table == 'Avis') {
                if (!in_array('nomOeuvre', $champs_a_afficher)) {
                    $champs_a_afficher[] = 'nomOeuvre'; // Inclure le champ de la table Oeuvre
                }
                if (!in_array('type', $champs_a_afficher)) {
                    $champs_a_afficher[] = 'type'; // Inclure le champ type de la table Oeuvre
                }
            }

            if (empty($champs_a_afficher)) {
                echo json_encode(["error" => "Aucun champ valide spécifié pour l'affichage"]);
                exit;
            }

            // Construire la liste des champs avec leurs préfixes
            $champs_str = implode(", ", array_map(function ($champ) use ($table, $champs_par_table) {
                if ($table == 'Avis' && ($champ == 'nomOeuvre' || $champ == 'type')) {
                    return "Oeuvre.$champ"; // Champs venant de la jointure
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
                    // Déséchapper les caractères spéciaux si nécessaire
                    $value = stripslashes($_GET[$champ]); // Déséchapper les caractères comme \/
                    // Encodage des valeurs avant de les insérer dans la requête SQL
                    $conditions[] = "$table.$champ = :$champ";
                    $params[":$champ"] = htmlspecialchars($value); // Appliquer htmlspecialchars uniquement après déséchappement
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
                    // Si des données sont trouvées, vérifier s'il s'agit de l'image demandée
                    if (isset($_GET['title'])) {
                        $title = urldecode($_GET['title']);
                        $imagePath = '/path/to/images/' . $title . '.jpg'; // Chemin vers l'image sur le serveur

                        if (file_exists($imagePath)) {
                            header('Content-Type: image/jpeg');
                            readfile($imagePath); // Retourner l'image
                            exit; // Terminer le script après avoir envoyé l'image
                        } else {
                            echo json_encode(["error" => "Image non trouvée"]);
                            exit;
                        }
                    }

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
