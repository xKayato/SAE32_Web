<?php
// Inclure la connexion à la base de données
include('connect.php');

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

$passid = "SalutJeSuisUnMotDePassePourUpdate";

if (isset($_GET['passid']) && $_GET['passid'] == $passid) {
    if (isset($_GET['table'])) {
        $table = htmlspecialchars($_GET['table'], ENT_QUOTES);

        include('tables.php');

        // Vérifier si la table est autorisée
        if (in_array($table, $tables_autorisees)) {
            $conditions = [];
            $updates = [];
            $params = [];

            // Identifier les champs "new" pour la mise à jour
            foreach ($_GET as $key => $value) {
                $key = htmlspecialchars($keys, ENT_QUOTES);
                if (strpos($key, 'new') === 0) { // Si ça commence par "new"
                    $field = substr($key, 3); // Retirer "new" pour obtenir le nom du champ
                    $updates[] = "$field = :new$field";  // Utiliser :new$field pour ne pas interférer avec la condition
                    $params[":new$field"] = $value;
                }
            }

            // Identifier les conditions
            foreach ($_GET as $key => $value) {
                $key = htmlspecialchars($keys, ENT_QUOTES);
                if ($key !== 'passid' && $key !== 'table' && strpos($key, 'new') !== 0) {
                    $conditions[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            // Vérifier qu'il y a des champs à mettre à jour
            if (empty($updates)) {
                echo json_encode(["error" => "Aucune donnée à mettre à jour spécifiée"]);
                exit;
            }

            // Construire la requête SQL
            $sql = "UPDATE $table SET " . implode(", ", $updates);
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Exécuter la requête
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["success" => "Données mises à jour avec succès"]);
                } else {
                    echo json_encode(["message" => "Aucune ligne affectée"]);
                }
            } catch (PDOException $e) {
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
