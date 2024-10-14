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

        <img src="user.jpg" alt="Portrait de l'utilisatrice" />
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez tous les messages de l'utilisatrice : <?php echo htmlspecialchars($user['alias']); ?></p>
        </section>

        <article>
            <?php
            $viewedUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $userId;

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
                    <h3><?php echo $isFollowing ? 'Désabonner' : 'S\'abonner'; ?> à <?php echo htmlspecialchars($user['alias'], ENT_QUOTES, 'UTF-8'); ?></h3>
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

    <main>
        <article>
            <h2>Poster un message</h2>
            <form action="" method="post">
                <input type="hidden" name="auteur" value="<?php echo $_SESSION['connected_id']; ?>">
                <dl>
                    <dt><label for="message">Message</label></dt>
                    <dd><textarea name="message" required></textarea></dd>
                </dl>
                <input type="submit" value="Poster">
            </form>

            <?php
            if (isset($_POST['message'])) {
                $authorId = $_SESSION['connected_id'];
                $postContent = $_POST['message'];

                $postContent = $mysqli->real_escape_string($postContent);
                $lInstructionSql = "INSERT INTO posts (user_id, content, created) VALUES (?, ?, NOW())";
                $stmt = $mysqli->prepare($lInstructionSql);
                $stmt->bind_param("is", $authorId, $postContent);
                if ($stmt->execute()) {
                    echo "Message posté avec succès.";
                } else {
                    echo "Impossible d'ajouter le message: " . $mysqli->error;
                }
            }
            ?>
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
            echo("Échec de la requête : " . $mysqli->error);
        }

        while ($post = $lesInformations->fetch_assoc()) {
            ?>
            <article>
                <h3>
                    <time><?php echo $post['created']; ?></time>
                </h3>
                <div>
                    <address>
                        <a href="wall.php?user_id=<?php echo $post['author_id']; ?>" title="<?php echo $post['author_name']; ?>">
                            <?php echo htmlspecialchars($post['author_name']); ?>
                        </a>
                    </address>
                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                </div>
                <footer>
                    <small>♥ <?php echo $post['like_number']; ?></small>
                    <a href=""><?php echo htmlspecialchars($post['taglist']); ?></a>
                </footer>
            </article>
        <?php } ?>
    </main>
</div>
