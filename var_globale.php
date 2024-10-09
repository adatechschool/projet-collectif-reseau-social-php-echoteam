<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "socialnetwork");

if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
} elseif (isset($_GET['tag_id'])) {
    $tagId = intval($_GET['tag_id']);
}

// Stocke l'en-tête dans une variable
$head = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Mur</title> 
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <header>
        <a href="admin.php"><img src="resoc.jpg" alt="Logo de notre réseau social"/></a>
        <nav id="menu">
            <a href="news.php">Actualités</a>
            <a href="wall.php?user_id=' . $_SESSION['connected_id'] . '">Mur</a>
            <a href="feed.php?user_id=' . $_SESSION['connected_id'] . '">Flux</a>
            <a href="tags.php?tag_id=' . $_SESSION['connected_id'] . '">Mots-clés</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php?user_id=' . $_SESSION['connected_id'] . '">Paramètres</a></li>
                <li><a href="followers.php?user_id=' . $_SESSION['connected_id'] . '">Mes suiveurs</a></li>
                <li><a href="subscriptions.php?user_id=' . $_SESSION['connected_id'] . '">Mes abonnements</a></li>
                <li><a href="login.php">Connexion</a></li>
            </ul>
        </nav>
    </header>
';

?>
