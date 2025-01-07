<?php
// Liste des tables autorisées
$tables_autorisees = ['Oeuvre', 'User', 'Like', 'Avis', 'Type'];

// Champs autorisés par table
$champs_par_table = [
    'Oeuvre' => ['idOeuvre', 'nomOeuvre', 'dateSortie', 'actif', 'auteur_studio', 'type'],
    'User' => ['login', 'mdp', 'acces'],
    'Like' => ['login', 'idAvis', 'like'],
    'Avis' => ['idAvis', 'texteAvis', 'note', 'date', 'idOeuvre', 'login'],
    'Type' => ['nomType']
];
?>