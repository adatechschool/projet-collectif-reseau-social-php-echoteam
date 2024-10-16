<?php

include 'var_globale.php';
redirect_login();

echo $head;
?>

<div id="wrapper">
    <aside>
        <?php
        // Récupérer les informations de l'utilisateur
        $laQuestionEnSql = "SELECT * FROM `users` WHERE id= '$userId' ";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        $user = $lesInformations->fetch_assoc();
        ?>

        <img src="user.jpg" alt="Portrait de l'utilisatrice" />
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez tous les messages des utilisateur·ices
                auxquel·les est abonné·e l'utilisateur·ice :
                <?php echo $userId; ?>
            </p>
        </section>
    </aside>
    <main id="main-content">

        <?php
        // Requête SQL pour récupérer les posts et le nombre total de likes
        $laQuestionEnSql = "SELECT posts.id,
            posts.content,
            posts.created,
            users.alias AS author_name,
            users.id AS author_id,
            COUNT(DISTINCT likes.id) AS like_number,
            GROUP_CONCAT(DISTINCT likes.user_id) AS liked_by,
            GROUP_CONCAT(DISTINCT tags.label) AS taglist
        FROM followers
        JOIN users ON users.id = followers.followed_user_id
        JOIN posts ON posts.user_id = users.id
        LEFT JOIN posts_tags ON posts.id = posts_tags.post_id
        LEFT JOIN tags ON posts_tags.tag_id = tags.id
        LEFT JOIN likes ON likes.post_id = posts.id
        WHERE followers.following_user_id = '$userId'
        GROUP BY posts.id
        ORDER BY posts.created DESC;";

        $lesInformations = $mysqli->query($laQuestionEnSql);
        if (!$lesInformations) {
            echo("Échec de la requête : " . $mysqli->error);
        }

        // Gestion des likes
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['post_id'])) {
                $postId = $_POST['post_id']; // ID du post

                // Vérifier si l'utilisateur a déjà liké ce post
                $checkLikeQuery = "SELECT * FROM likes WHERE user_id = '$userId' AND post_id = '$postId'";
                $likeResult = $mysqli->query($checkLikeQuery);

                if ($likeResult->num_rows > 0) {
                    // L'utilisateur a déjà liké, donc on retire le like
                    $removeLikeQuery = "DELETE FROM likes WHERE user_id = '$userId' AND post_id = '$postId'";
                    $mysqli->query($removeLikeQuery); // Exécution de la requête
                } else {
                    // L'utilisateur n'a pas liké, donc on ajoute le like
                    $insertLikeQuery = "INSERT INTO likes (user_id, post_id) VALUES ('$userId', '$postId')";
                    $mysqli->query($insertLikeQuery); // Exécution de la requête
                }

                // Redirection pour éviter la répétition du POST lors du rechargement de la page
                header("Location: " . $_SERVER['REQUEST_URI'] . "#" . $postId);
                exit();
            }
        }

        // Boucle d'affichage des posts
        while ($post = $lesInformations->fetch_assoc()) {
            // Vérifier si l'utilisateur a déjà liké ce post
            $checkLikeQuery = "SELECT * FROM likes WHERE user_id = '$userId' AND post_id = '" . $post['id'] . "'";
            $likeResult = $mysqli->query($checkLikeQuery);
            $userHasLiked = ($likeResult->num_rows > 0);
            ?>
            <article id="<?php echo $post['id']; ?>">
                <h3><time><?php echo $post['created'] ?></time></h3>
                <address><a href="wall.php?user_id=<?php echo $post['author_id'] ?>"><?php echo $post['author_name'] ?></a></address>
                <div><p><?php echo $post['content'] ?></p></div>
                <footer>
                    <small>
                        <form method="post" class="like-form">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="like-button">
                                <?php echo $userHasLiked ? '♥' : '♡'; ?>
                            </button>
                        </form>
                    </small>
                    <!-- Affichage du nombre total de likes pour le post -->
                    <small>&nbsp<?php echo $post['like_number']; ?> likes</small>
                    <?php
                    // Afficher les tags comme liens cliquables
                    $tagsArray = explode(',', $post['taglist']);
                    foreach ($tagsArray as $tag) {
                        // On crée une requête qui permet de récupérer les tags
                        $checkTagId= "SELECT id FROM `tags` WHERE label ='$tag'";
                        $TagResult = $mysqli->query($checkTagId);
                        $TagId = $TagResult->fetch_assoc();
                        echo '<a href="tags.php?tag_id=' .  $TagId["id"]. '" class="tag-link">#' . htmlspecialchars($tag) . '</a> ';
                    }
                    ?>
                </footer>
            </article>
        <?php } ?>

    </main>
</div>

</body>
</html>
