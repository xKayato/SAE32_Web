<?php
try {
  $path = "/var/www/html/database_oeuvres.db"; // Chemin vers la base de données SQLite
  $conn = new PDO("sqlite:" . $path); // Connexion à la base de données SQLite
} catch (PDOException $e) {
  echo "Erreur : " . $e->getMessage();
}
?>
