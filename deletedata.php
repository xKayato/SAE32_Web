<?php
// Inclure la connexion à la base de données
include('connect.php');

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

$passid = "SalutJeSuisUnMotDePassePourDelete";

if (isset($_GET['passid']) && $_GET['passid'] == $passid) {
    if (isset($_GET['table'])) {
        $table = htmlspecialchars($_GET['table']);


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


        // Vérifier si la table est autorisée
        if (in_array($table, $tables_autorisees)) {
            // Récupérer les champs autorisés pour cette table
            $champs_autorises = $champs_par_table[$table];
            $conditions = [];
            $params = [];

            // Construire les conditions de suppression dynamiquement
            foreach ($_GET as $key => $value) {
                if ($key !== 'passid' && $key !== 'table' && in_array($key, $champs_autorises)) {
                    $conditions[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            // Vérifier qu'il y a des conditions pour éviter une suppression massive accidentelle
            if (empty($conditions)) {
                echo json_encode(["error" => "Aucune condition spécifiée pour la suppression"]);
                exit;
            }

            // Construire la requête SQL
            $sql = "DELETE FROM $table WHERE " . implode(" AND ", $conditions);

            // Exécuter la requête
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(["success" => "Données supprimées avec succès dans la table '$table'"]);
                } else {
                    echo json_encode(["message" => "Aucune ligne affectée. Vérifiez vos conditions"]);
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