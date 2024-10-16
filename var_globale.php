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
// Vérification si l'utilisateur est un administrateur
$is_admin=false; 
if ($userId !=0){
    $sqlAdminCheck = "SELECT role FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($sqlAdminCheck);
    $stmt->bind_param("i", $_SESSION['connected_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); 
    if ($user && $user['role']==='admin'){
        $is_admin=true;
    }
    $stmt-> close();
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
            // Vérification si l'utilisateur est admin via la session
            if ($is_admin) {
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

function handleLikes($userId, $mysqli) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
        $postId = intval($_POST['post_id']);
        if ($postId > 0) {
            $checkLikeQuery = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
            $stmt = $mysqli->prepare($checkLikeQuery);
            $stmt->bind_param("ii", $userId, $postId);
            $stmt->execute();
            $likeResult = $stmt->get_result();

            if ($likeResult->num_rows > 0) {
                // Si le like existe déjà, on le supprime
                $removeLikeQuery = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
                $stmt = $mysqli->prepare($removeLikeQuery);
                $stmt->bind_param("ii", $userId, $postId);
                $stmt->execute();
            } else {
                // Si le like n'existe pas, on l'ajoute
                $insertLikeQuery = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
                $stmt = $mysqli->prepare($insertLikeQuery);
                $stmt->bind_param("ii", $userId, $postId);
                $stmt->execute();
            }

            // Redirection vers la même page avec l'ancre du post
            $currentPage = $_SERVER['PHP_SELF'];
            $queryString = $_SERVER['QUERY_STRING'];
            $redirectUrl = $currentPage . ($queryString ? "?$queryString" : "") . "#post-" . $postId;
            header("Location: $redirectUrl");
            exit();
        }
    }
}

function hasUserLikedPost($userId, $postId, $mysqli) {
    $checkLikeQuery = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $mysqli->prepare($checkLikeQuery);
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}


?>
