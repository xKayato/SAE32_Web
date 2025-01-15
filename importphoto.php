<?php
// Vérifier si les données ont été reçues
$data = json_decode(file_get_contents("php://input"), true);


// Clé de sécurité pour vérifier l'authenticité de la requête
$passid = "12D-Jsk-DQl_qs8DQS--_3J2__";

// Vérifier la clé de sécurité
if (isset($_GET['passid']) && $_GET['passid'] == $passid) {
    if (isset($data['image']) && isset($data['filename'])) {
        $encodedImage = $data['image'];
        $filename = basename($data['filename']); // Sécuriser le nom de fichier

        // Décoder l'image en Base64
        $decodedImage = base64_decode($encodedImage);
        if ($decodedImage === false) {
            http_response_code(400);
            echo json_encode(["message" => "Erreur lors du décodage de l'image"]);
            exit;
        }

        // Définir le chemin de stockage
        $filePath = "uploads/" . $filename;

        // Enregistrer l'image sur le serveur
        if (file_put_contents($filePath, $decodedImage)) {
            echo json_encode(["message" => "Image enregistrée avec succès !", "path" => $filePath]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erreur lors de l'enregistrement de l'image"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Données manquantes"]);
    }
} else {
    echo json_encode(["error" => "Mot de passe incorrect"]);
}
?>
