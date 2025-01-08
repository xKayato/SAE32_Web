<?php
//SQLite
try {
  $path = "/var/www/html/database_oeuvres.db";
  $conn = new PDO("sqlite:" . $path);
} catch (PDOException $e) {
  echo "Erreur : " . $e->getMessage();
}
?>
