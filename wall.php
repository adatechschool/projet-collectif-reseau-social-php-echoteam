<?php
include 'var_globale.php';
redirect_login();

echo $head;
?>

<div id="wrapper">
    <aside>

        <?php
        $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId'";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        $user = $lesInformations->fetch_assoc();
        ?>

        <img src="user.png" alt="Portrait de l'utilisatrice" />
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez tous les messages de l'utilisatrice :
                <?php echo $user['alias'] ?>
            </p>
        </section>

        <article>
            <?php
            $viewedUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $userId;

            //Gestion de l'abonnement à un utilisateur :

            if ($viewedUserId !== $_SESSION['connected_id']) {
                $laQuestionEnSql = "SELECT * FROM followers WHERE followed_user_id = ? AND following_user_id = ?";
                $stmtVerif = $mysqli->prepare($laQuestionEnSql);
                $stmtVerif->bind_param("ii", $viewedUserId, $_SESSION['connected_id']);
                $stmtVerif->execute();
                $resultVerif = $stmtVerif->get_result();

                $isFollowing = $resultVerif->num_rows > 0;

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $subscriberId = intval($_POST['subscriber_id']);
                    $authorId = intval($_POST['author_id']);

                    if (isset($_POST['subscribe']) && $subscriberId !== $authorId) {
                        $lInstructionSql = "INSERT INTO followers (followed_user_id, following_user_id) VALUES (?, ?)";
                        $stmt = $mysqli->prepare($lInstructionSql);
                        $stmt->bind_param("ii", $authorId, $subscriberId);
                        if ($stmt->execute()) {
                            $message = "Vous vous êtes abonné avec succès.";
                            $isFollowing = true;
                        } else {
                            $message = "Échec de l'abonnement : " . $mysqli->error;
                        }
                    } elseif (isset($_POST['unsubscribe'])) {
                        $lInstructionSql = "DELETE FROM followers WHERE followed_user_id = ? AND following_user_id = ?";
                        $stmt = $mysqli->prepare($lInstructionSql);
                        $stmt->bind_param("ii", $authorId, $subscriberId);
                        if ($stmt->execute()) {
                            $message = "Vous vous êtes désabonné avec succès.";
                            $isFollowing = false;
                        } else {
                            $message = "Échec de la désinscription : " . $mysqli->error;
                        }
                    }
                }

                echo "<p>Vous " . ($isFollowing ? "suivez déjà" : "ne suivez pas") . " cet utilisateur.</p>";
                ?>
                <section>
                    <?php if ($userId == $_SESSION['connected_id']): ?>
                        <h3>
                            <?php echo $isFollowing ? 'Se désabonner de' : 'S\'abonner à'; ?>
                            <?php echo htmlspecialchars($user['alias'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                    <?php endif; ?>

                    <form action="" method="post">
                        <input type="hidden" name="subscriber_id" value="<?php echo $_SESSION['connected_id']; ?>">
                        <input type="hidden" name="author_id" value="<?php echo $viewedUserId; ?>">
                        <input type="submit" name="<?php echo $isFollowing ? 'unsubscribe' : 'subscribe'; ?>" value="<?php echo $isFollowing ? 'Désabonner' : 'S\'abonner'; ?>">
                    </form>
                </section>
                <?php if (isset($message)): ?>
                    <p><?php echo $message; ?></p>
                <?php endif; ?>
            <?php } ?>
        </article>
    </aside>
    <main id="main-content">

        <!--        Poster un message : -->

        <?php
        if ($userId == $_SESSION['connected_id']) {
            echo '<article>
            <h2>Poster un message</h2>
            <form action="" method="post">
                <input type="hidden" name="auteur" value="' . $_SESSION['connected_id'] . '">
                <dl>
                    <dt><label for="message">Message</label></dt>
                    <dd><textarea id="message" name="message" required></textarea></dd>
                    <dt><label for="mot-clé">mot-clé</label></dt>
                    <dd> <input type="text" id="mot_cle" name="tags"><div>(séparés par une virgule)</div></dd>
                    
                </dl>
                <input type="submit" value="Poster">
            </form>
        </article>';
        }
        ?>

        <?php
        if (isset($_POST['message'])) {
            $authorId = $_SESSION['connected_id'];
            $postContent = $_POST['message'];
            $postTags = $_POST['tags'];

            // Échapper le contenu du message pour éviter les injections SQL
            $postContent = $mysqli->real_escape_string($postContent);

            // Insérer le post dans la table `posts`
            $lInstructionSql = "INSERT INTO posts (user_id, content, created) VALUES (?, ?, NOW())";
            $stmt = $mysqli->prepare($lInstructionSql);
            $stmt->bind_param("is", $authorId, $postContent);

            if ($stmt->execute()) {
                $postId = $stmt->insert_id; // Récupérer l'ID du post inséré

                // Gérer les tags
                if (!empty($postTags)) {
                    // Split les tags par virgule et enlever les espaces
                    $tagsArray = array_map('trim', explode(',', $postTags));

                    foreach ($tagsArray as $tag) {
                        // Vérifier si le tag existe déjà
                        $tag = $mysqli->real_escape_string($tag);
                        $checkTagQuery = "SELECT id FROM tags WHERE label = ?";
                        $tagStmt = $mysqli->prepare($checkTagQuery);
                        $tagStmt->bind_param("s", $tag);
                        $tagStmt->execute();
                        $tagResult = $tagStmt->get_result();

                        if ($tagResult->num_rows > 0) {
                            // Si le tag existe, récupérer son ID
                            $tagRow = $tagResult->fetch_assoc();
                            $tagId = $tagRow['id'];
                        } else {
                            // Si le tag n'existe pas, l'ajouter
                            $insertTagQuery = "INSERT INTO tags (label) VALUES (?)";
                            $insertTagStmt = $mysqli->prepare($insertTagQuery);
                            $insertTagStmt->bind_param("s", $tag);
                            if ($insertTagStmt->execute()) {
                                $tagId = $insertTagStmt->insert_id; // Récupérer l'ID du nouveau tag
                            } else {
                                echo "Erreur lors de l'ajout du tag: " . $mysqli->error;
                                continue; // Passer au tag suivant en cas d'erreur
                            }
                        }

                        // Lier le post au tag
                        $linkPostTagQuery = "INSERT INTO posts_tags (post_id, tag_id) VALUES (?, ?)";
                        $linkPostTagStmt = $mysqli->prepare($linkPostTagQuery);
                        $linkPostTagStmt->bind_param("ii", $postId, $tagId);
                        if (!$linkPostTagStmt->execute()) {
                            echo "Erreur lors de la liaison du tag au post: " . $mysqli->error;
                        }
                    }
                }

            } else {
                echo "Impossible d'ajouter le message: " . $mysqli->error;
            }
        }
        ?>

        </article>

        <?php

        // Gestion des likes
        // Requête SQL pour récupérer les posts et le nombre total de likes
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
        WHERE posts.user_id = '$userId'  -- Filtrer uniquement les posts de l'utilisateur
        GROUP BY posts.id
        ORDER BY posts.created DESC;";

        $lesInformations = $mysqli->query($laQuestionEnSql);
        if (!$lesInformations) {
            echo("Échec de la requête : " . $mysqli->error);
        }


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
                            <button type="submit" class="like-button" style="background: none; border: none;">
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
                        // On crée une requête qui permet de récupérer l'ID des tags
                        $checkTagId = "SELECT id FROM `tags` WHERE label = ?";
                        $stmtTag = $mysqli->prepare($checkTagId);
                        $stmtTag->bind_param("s", $tag);
                        $stmtTag->execute();
                        $TagResult = $stmtTag->get_result();
                        $TagId = $TagResult->fetch_assoc();

                        // Vérifier si un tag a été trouvé
                        if ($TagId) {
                            echo '<a href="tags.php?tag_id=' . $TagId["id"] . '" class="tag-link">#' . htmlspecialchars($tag) . '</a> ';
                        }
                        else {
                            echo '#' ;

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
