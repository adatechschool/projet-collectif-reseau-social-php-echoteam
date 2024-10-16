<?php
include 'var_globale.php';
redirect_login();

echo $head;
?>
    <div id="wrapper">
        <aside>
            <?php
            // Section Présentation
            echo '<section>';
            echo '<h3>Présentations</h3>';
            echo '<p>' . ($tagId == 0 ?
                            'Sur cette page vous trouverez les derniers messages de tous les tags.' :
                            'Sur cette page vous trouverez les derniers messages comportant le mot-clé : ' . htmlspecialchars(fetchTagLabel($tagId, $mysqli)) . '</p>');
            echo '</section>';
            ?>

            <!-- Liste des tags -->
            <section>
                <h3>Tags</h3>
                <ul>
                    <?php
                    $tagsQuery = "SELECT * FROM tags";
                    $result = $mysqli->query($tagsQuery);

                    if ($result && $result->num_rows > 0) {
                        while ($tagItem = $result->fetch_assoc()) {
                            echo "<li><a href='tags.php?tag_id=" . $tagItem['id'] . "'>" . htmlspecialchars($tagItem['label']) . "</a></li>";
                        }
                    } else {
                        echo "<li>Aucun tag disponible</li>";
                    }
                    ?>
                </ul>
            </section>
        </aside>

        <main>
            <?php
            // Si un tag spécifique est sélectionné, récupérer et afficher les posts associés
            if ($tagId != 0) {
                $posts = fetchPostsByTag($tagId, $mysqli);
                if (!$posts) {
                    echo "Échec de la requête : " . $mysqli->error;
                } else {
                    handleLikes($userId, $mysqli);

                    // Affichage des posts
                    foreach ($posts as $post) {
                        $userHasLiked = hasUserLikedPost($userId, $post['post_id'], $mysqli);
                        ?>
                        <article>
                            <h3>
                                <time><?php echo htmlspecialchars($post['created']); ?></time>
                            </h3>
                            <address>
                                <a href="wall.php?user_id=<?php echo $post['author_id']; ?>"><?php echo htmlspecialchars($post['author_name']); ?></a>
                            </address>
                            <div>
                                <p><?php echo htmlspecialchars($post['content']); ?></p>
                            </div>
                            <footer>
                                <small>
                                    <form method="post" class="like-form" style="display: inline;"> <!-- Ajout du style inline -->
                                        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                        <button type="submit" class="like-button" style="background: none; border: none;"> <!-- Style du bouton pour un meilleur alignement -->
                                            <?php echo $userHasLiked ? '♥' : '♡'; ?>
                                        </button>
                                    </form>
                                    <span><?php echo $post['like_number']; ?></span> likes <!-- Affiche le nombre de likes à côté -->
                                </small>
                                <?php
                                // Affichage des tags
                                echo implode(' ', array_map('htmlspecialchars', explode(',', $post['taglist'])));
                                ?>
                            </footer>

                        </article>
                        <?php
                    }
                }
            }
            ?>
        </main>
    </div>
    </body>
    </html>

<?php
// Functions

function fetchTagLabel($tagId, $mysqli) {
    $stmt = $mysqli->prepare("SELECT label FROM tags WHERE id = ?");
    $stmt->bind_param("i", $tagId);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result->fetch_assoc())['label'] ?? '';
}

function fetchPostsByTag($tagId, $mysqli) {
    $laQuestionEnSql = "SELECT 
        posts.id AS post_id,
        posts.content,
        posts.created,
        users.id AS author_id,
        users.alias AS author_name,
        COUNT(likes.id) AS like_number,
        GROUP_CONCAT(DISTINCT tags.label) AS taglist
        FROM posts_tags AS filter 
        JOIN posts ON posts.id = filter.post_id
        JOIN users ON users.id = posts.user_id
        LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
        LEFT JOIN tags ON posts_tags.tag_id = tags.id 
        LEFT JOIN likes ON likes.post_id = posts.id 
        WHERE filter.tag_id = ? 
        GROUP BY posts.id
        ORDER BY posts.created DESC";

    $stmt = $mysqli->prepare($laQuestionEnSql);
    $stmt->bind_param("i", $tagId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

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
