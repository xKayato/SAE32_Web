<?php
// Inclure la connexion à la base de données
include('connect.php');

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

$passid = "SalutJeSuisUnMotDePassePourImport";

if (isset($_GET['passid']) && $_GET['passid'] == $passid) {
    // Vérifier si le paramètre 'table' est présent dans l'URL
    if (isset($_GET['table'])) {
        $table = htmlspecialchars($_GET['table']); // Sécuriser le nom de la table

        // Liste des tables autorisées
        $tables_autorisees = ['Oeuvre', 'User', 'Like', 'Avis', 'Type'];

        // Champs autorisés par table
        $champs_par_table = [
            'Oeuvre' => ['nomOeuvre', 'dateSortie', 'actif', 'auteur_studio', 'type'],
            'User' => ['login', 'mdp', 'acces'],
            'Like' => ['login', 'idAvis', 'like'],
            'Avis' => ['texteAvis', 'note', 'date', 'idOeuvre', 'login'],
            'Type' => ['nomType']
        ];

        // Vérifier si la table demandée est autorisée
        if (in_array($table, $tables_autorisees) && isset($champs_par_table[$table])) {

            // Obtenir les champs pour la table demandée
            $champs = $champs_par_table[$table];
            $valeurs = [];

            // Vérifier que tous les champs sont présents dans les paramètres GET
            foreach ($champs as $champ) {
                if (isset($_GET[$champ])) {
                    // Assurer que les données sont valides
                    $valeur = $_GET[$champ];
                    if ($champ == 'dateSortie' && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $valeur)) {
                        echo json_encode(["error" => "Le champ '$champ' doit être une date valide (format: YYYY-MM-DD)"]);
                        exit;
                    }
                    $valeurs[$champ] = htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8'); // Appliquer htmlspecialchars pour éviter les XSS
                } else {
                    echo json_encode(["error" => "Le champ '$champ' est manquant pour la table '$table'"]);
                    exit;
                }
            }

            // Construire la requête SQL d'insertion dynamiquement
            $champs_str = implode(", ", array_keys($valeurs));
            $placeholders = implode(", ", array_fill(0, count($valeurs), "?"));
            $sql = "INSERT INTO $table ($champs_str) VALUES ($placeholders)";

            // Préparer et exécuter la requête d'insertion
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute(array_values($valeurs));

                // Confirmer l'insertion
                echo json_encode(["success" => "Données insérées avec succès dans la table '$table'"]);
            } catch (PDOException $e) {
                // Gérer les erreurs d'insertion
                echo json_encode(["error" => "Erreur d'insertion : " . $e->getMessage()]);
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
