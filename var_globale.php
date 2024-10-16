<?php
session_start();

if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);
} elseif (isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
} elseif (isset($_SESSION['connected_id'])) {  // Vérification si la session est définie
    $userId = $_SESSION['connected_id'];
} else {
    // Si aucune méthode pour obtenir l'ID utilisateur n'est trouvée, redirection ou gestion
    $userId = 0; // Ou une autre gestion d'erreur, par exemple une redirection vers login.php
}

$mysqli = new mysqli("localhost", "root", "", "socialnetwork");



if (isset($_GET['tag_id'])) {
    $tagId = intval($_GET['tag_id']);
}
    else {
        $tagId = 0;
    }


function redirect_login() {
    if (!isset($_SESSION['connected_id'])) {
        // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
        header("Location: login.php");
        exit();
    }
}
// Stocke l'en-tête dans une variable
$head = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC</title> 
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <header>
        
        <nav id="menu">
        
            <a href="news.php">Actualités</a>
            <a href="wall.php">Mur</a>
            <a href="feed.php">Flux</a>
            <a href="tags.php">Mots-clés</a>';
//            echo $_SESSION['role'];
            // Vérification si l'utilisateur est admin via la session
            if (isset($_SESSION['role']) && $_SESSION['role']==='admin') {
                $head .= '<a href="admin.php">Admin</a>';
             }
           // vu sur internet à verif  (!empty($_SESSION['droit']) && $_SESSION['droit'] === "admin" )
           $head.= '</nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php">Paramètres</a></li>
                <li><a href="followers.php">Mes suiveurs</a></li>
                <li><a href="subscriptions.php">Mes abonnements</a></li>
                <li><a href="login.php">Connexion</a></li>
            </ul>
        </nav>
        
       
    </header>
';
//gestion des likes :


//$Leslikes = $mysqli->query($insertLikeQuery);
//if ( ! $Leslikes)
//{
//    echo("Échec de la requete : " . $mysqli->error);
//}

?>
