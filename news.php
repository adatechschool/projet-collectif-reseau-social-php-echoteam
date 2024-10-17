<?php

include 'var_globale.php';
redirect_login();

echo $head;

// Gestion des likes avec la fonction handleLikes
handleLikes($userId, $mysqli); // Appel de la fonction avant de récupérer les posts
?>

<div id="wrapper">
    <aside>
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez les derniers messages de
                tous les utilisatrices du site.</p>
        </section>
    </aside>
    <main id="main-content">

        <?php

        $laQuestionEnSql = "SELECT posts.id,
            posts.content,
            posts.created,
            users.alias AS author_name,
            users.id AS author_id,
            COUNT(DISTINCT likes.id) AS like_number,
            GROUP_CONCAT(DISTINCT likes.user_id) AS liked_by,
            GROUP_CONCAT(DISTINCT tags.label) AS taglist
        FROM posts
        JOIN users ON users.id = posts.user_id
        LEFT JOIN posts_tags ON posts.id = posts_tags.post_id
        LEFT JOIN tags ON posts_tags.tag_id = tags.id
        LEFT JOIN likes ON likes.post_id = posts.id
        GROUP BY posts.id
        ORDER BY posts.created DESC;";

        $lesInformations = $mysqli->query($laQuestionEnSql);
        if (!$lesInformations) {
            echo("Échec de la requête : " . $mysqli->error);
        }

        // Boucle d'affichage des posts
        while ($post = $lesInformations->fetch_assoc()) {
            // Vérifier si l'utilisateur a aimé le post en utilisant hasUserLikedPost
            $userHasLiked = hasUserLikedPost($userId, $post['id'], $mysqli); // Appel à la fonction

            ?>
            <article id="<?php echo $post['id']; ?>">
                <h3><time><?php echo $post['created']; ?></time></h3>
                <address><a href="wall.php?user_id=<?php echo $post['author_id']; ?>"><?php echo $post['author_name']; ?></a></address>
                <div><p><?php echo nl2br(htmlspecialchars(stripslashes($post['content']), ENT_QUOTES, 'UTF-8')); ?></p></div>
                <footer>
                    <small>
                        <form method="post" class="like-form">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="like-button" style="background: none; border: none;">
                                <?php echo $userHasLiked ? '♥' : '♡'; ?>
                            </button>
                        </form>
                    </small>
                    <small>&nbsp;<?php echo $post['like_number']; ?> likes</small>
                    <?php
                    // Afficher les tags comme liens cliquables
                    $tagsArray = explode(',', $post['taglist']);
                    foreach ($tagsArray as $tag) {
                        // On crée une requête qui permet de récupérer les tags
                        $checkTagId = "SELECT id FROM `tags` WHERE label ='$tag'";
                        $TagResult = $mysqli->query($checkTagId);
                        $TagId = $TagResult->fetch_assoc();
                        // Vérifier si un tag a été trouvé
                        if ($TagId) {
                            echo '<a href="tags.php?tag_id=' . $TagId["id"] . '" class="tag-link">#' . htmlspecialchars($tag) . '</a> ';
                        } else {
                            echo '#' . htmlspecialchars($tag) . ' ';
                        }
                    }
                    ?>
                </footer>
            </article>
        <?php } ?>

    </main>
</div>

</body>
</html>
