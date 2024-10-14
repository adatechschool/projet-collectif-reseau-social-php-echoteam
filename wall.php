<?php
include 'var_globale.php';
redirect_login();

echo $head;
?>

<div id="wrapper">

    <aside>

        <?php
        $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        $user = $lesInformations->fetch_assoc();
        ?>

        <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez tous les messages de l'utilisatrice :
                <?php echo $userId ?>
            </p>
        </section>

        <article>
            <?php
            // On détermine l'utilisateur affiché
            if (isset($_GET['user_id'])) {
                $viewedUserId = intval($_GET['user_id']);
            } else {
                $viewedUserId = $userId; // On utilise l'utilisateur connecté par défaut
            }

            // On vérifie si l'utilisateur consulté est différent de l'utilisateur connecté
            if ($viewedUserId !== $_SESSION['connected_id']) {
                // Vérification si l'utilisateur suit déjà l'auteur
                $laQuestionEnSql = "SELECT * FROM followers WHERE followed_user_id = ? AND following_user_id = ?";
                $stmtVerif = $mysqli->prepare($laQuestionEnSql);
                $stmtVerif->bind_param("ii", $viewedUserId, $_SESSION['connected_id']);
                $stmtVerif->execute();
                $resultVerif = $stmtVerif->get_result();

                if ($resultVerif->num_rows > 0) {
                    // L'utilisateur suit déjà l'auteur
                    echo "<p>Vous suivez déjà cet utilisateur.</p>";
                    $isFollowing = true;
                } else {
                    // L'utilisateur ne suit pas encore l'auteur
                    echo "<p>Vous ne suivez pas cet utilisateur.</p>";
                    $isFollowing = false;
                }

                // Affichage du formulaire d'abonnement/désabonnement
                ?>
                <section>
                    <h3><?php echo $isFollowing ? 'Désabonner' : 'S\'abonner'; ?> à <?php echo htmlspecialchars($user['alias'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <form action="" method="post">
                        <input type="hidden" name="subscriber_id" value="<?php echo $_SESSION['connected_id']; ?>">
                        <input type="hidden" name="author_id" value="<?php echo $viewedUserId; ?>">
                        <input type="submit" name="<?php echo $isFollowing ? 'unsubscribe' : 'subscribe'; ?>" value="<?php echo $isFollowing ? 'Abonné' : 'S\'abonner'; ?>">
                    </form>
                </section>
                <?php
            }

            // Traitement de l'abonnement
            if (isset($_POST['subscribe'])) {
                $subscriberId = intval($_POST['subscriber_id']);
                $authorId = intval($_POST['author_id']);

                // Vérification que les IDs ne sont pas identiques
                if ($subscriberId !== $authorId) {
                    // Requête pour insérer dans une table d'abonnements
                    $lInstructionSql = "INSERT INTO followers (followed_user_id, following_user_id) VALUES (?, ?)";
                    $stmt = $mysqli->prepare($lInstructionSql);
                    $stmt->bind_param("ii", $authorId, $subscriberId); // Inverse les IDs pour suivre l'utilisateur

                    if ($stmt->execute()) {
                        echo "<p>Vous vous êtes abonné avec succès.</p>";
                    } else {
                        echo "<p>Échec de l'abonnement : " . $mysqli->error . "</p>";
                    }
                } else {
                    echo "<p>Vous ne pouvez pas vous abonner à vous-même.</p>";
                }
            }

            // Traitement de la désinscription
            if (isset($_POST['unsubscribe'])) {
                $subscriberId = intval($_POST['subscriber_id']);
                $authorId = intval($_POST['author_id']);

                // Requête pour supprimer l'abonnement
                $lInstructionSql = "DELETE FROM followers WHERE followed_user_id = ? AND following_user_id = ?";
                $stmt = $mysqli->prepare($lInstructionSql);
                $stmt->bind_param("ii", $authorId, $subscriberId);

                if ($stmt->execute()) {
                    echo "<p>Vous vous êtes désabonné avec succès.</p>";
                } else {
                    echo "<p>Échec de la désinscription : " . $mysqli->error . "</p>";
                }
            }
            ?>

        </article>
    </aside>
    <main>
        <article>
            <h2>Poster un message</h2>
            <?php
            // Poster un message
            $laQuestionEnSql = "SELECT * FROM users";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            while ($user = $lesInformations->fetch_assoc()) {
                $listAuteurs[$user['id']] = $user['alias'];
            }

            $enCoursDeTraitement = isset($_POST['auteur']);
            if ($enCoursDeTraitement) {
                // on ne fait ce qui suit que si un formulaire a été soumis.
                // On récupère ce qu'il y a dans le formulaire
                $authorId = $_SESSION['connected_id'];
                $postContent = $_POST['message'];

                // Vérification des champs et création des messages d'erreur éventuels
                $erreurs = [];
                if (empty($postContent)) {
                    $erreurs[] = "Le message est requis.";
                }

                if (count($erreurs) === 0) {
                    // Petite sécurité pour éviter les injections SQL
                    $authorId = intval($mysqli->real_escape_string($authorId));
                    $postContent = $mysqli->real_escape_string($postContent);
                    // Construction de la requête
                    $lInstructionSql = "INSERT INTO posts (id, user_id, content, created, parent_id) VALUES (NULL, ?, ?, NOW(), NULL)";
                    $stmt = $mysqli->prepare($lInstructionSql);
                    $stmt->bind_param("is", $authorId, $postContent);

                    if ($stmt->execute()) {
                        echo "Message posté avec succès.";
                    } else {
                        echo "Impossible d'ajouter le message: " . $mysqli->error;
                    }
                } else {
                    // sinon, affiche les erreurs
                    foreach ($erreurs as $erreur) {
                        echo "<p style='color: red;'>$erreur</p>";
                    }
                }
            }
            ?>
            <form action="wall.php" method="post">
                <input type="hidden" name="auteur" value="<?php echo $_SESSION['connected_id']; ?>">
                <dl>
                    <dt><label for="message">Message</label></dt>
                    <dd><textarea name="message" aria-describedby="message-error"></textarea></dd>
                </dl>
                <input type="submit">
            </form>

        </article>
        <?php
        $laQuestionEnSql =
        "SELECT posts.content, 
                posts.created,    
                users.alias as author_name,
                users.id as author_id,
                COUNT(likes.id) as like_number,
                GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                FROM posts
                JOIN users ON users.id = posts.user_id
                LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                LEFT JOIN tags ON posts_tags.tag_id = tags.id 
                LEFT JOIN likes ON likes.post_id = posts.id 
                WHERE posts.user_id = '$userId' 
                GROUP BY posts.id
                ORDER BY posts.created DESC";

        $lesInformations = $mysqli->query($laQuestionEnSql);
        if (!$lesInformations) {
            echo("Échec de la requete : " . $mysqli->error);
        }

        while ($post = $lesInformations->fetch_assoc()) {
            ?>
            <article>
                <h3>
                    <time><?php echo $post['created'] ?></time>
                </h3>
                <div>
                    <address>
                        <a href="wall.php?user_id=<?php echo $post['author_id'] ?>" title="<?php echo $post['author_name'] ?>">
                            <?php echo $post['author_name'] ?>
                        </a>
                    </address>
                    <p><?php echo $post['content'] ?></p>
                </div>
                <footer>
                    <small>♥ <?php echo $post['like_number'] ?></small>
                    <a href=""><?php echo $post['taglist'] ?></a>
                </footer>
            </article>
        <?php } ?>

    </main>
</
