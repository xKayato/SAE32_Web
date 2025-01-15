<?php

// Clé de sécurité pour vérifier l'authenticité de la requête
$passid = "SUD24k_7DjQufy8137slsjQ809";

// Vérifier la clé de sécurité
if (isset($_GET['passid']) && $_GET['passid'] == $passid) {
    // Vérifier si le paramètre 'title' est passé dans l'URL
    if (isset($_GET['title'])) {
        $title = $_GET['title']; // Récupérer le nom de l'œuvre (ex: nomOeuvre [type])
        
        // Nettoyer le titre pour éviter des problèmes de sécurité (ex: injection de code)
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        
        // Remplacer les espaces par des underscores (_)
        $title = str_replace(" ", "_", $title);
        
        // Construire le nom de fichier de l'image (par exemple: nomOeuvre_type.jpg)
        $imageFileName = $title . ".jpg";
        
        // Définir le chemin vers le dossier 'uploads'
        $imagePath = 'uploads/' . $imageFileName;

        // Vérifier si le fichier existe
        if (file_exists($imagePath)) {
            // Spécifier le type de contenu (image JPEG)
            header('Content-Type: image/jpeg');
            header('Content-Disposition: inline; filename="' . $imageFileName . '"');
            
            // Lire le fichier et l'envoyer au client
            readfile($imagePath);
        } else {
            // Si l'image n'existe pas, envoyer une erreur 404
            header("HTTP/1.0 404 Not Found");
            echo "Image not found";
        }
    } else {
        // Si le paramètre 'title' est manquant, envoyer une erreur 400
        header("HTTP/1.0 400 Bad Request");
        echo "Missing title parameter";
    }
}else {
    echo json_encode(["error" => "Mot de passe incorrect"]);
}
?>
