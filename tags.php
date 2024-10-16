<?php

include 'var_globale.php';
redirect_login();

echo $head;
?>
<div id="wrapper">
    <aside>
        <?php
        // Vérifier si un tag est sélectionné
        if ($tagId == 0) {
            // Section Présentation
            echo '<section>';
            echo '<h3>Présentations</h3>';
            echo '<p>Sur cette page vous trouverez les derniers messages de tous les tags.</p>';
            echo '</section>';

        } else {
            // Si un tag spécifique est sélectionné, afficher les détails pour ce tag
            // Requête pour récupérer les informations du tag sélectionné
            $stmt = $mysqli->prepare("SELECT * FROM tags WHERE id = ?");
            $stmt->bind_param("i", $tagId);
            $stmt->execute();
            $lesInformations = $stmt->get_result();
            $tag = $lesInformations->fetch_assoc();


            // Section Présentation avec le tag sélectionné
            echo '<section>';
            echo '<h3>Présentations</h3>';
            echo '<p>Sur cette page vous trouverez les derniers messages comportant le mot-clé : ' . htmlspecialchars($tag['label']) . '</p>';
            echo '</section>';
        }
        ?>

        <!-- Liste des tags -->
        <section>
            <h3>Tags</h3>
            <ul>
                <?php
                // Utilisation d'une requête préparée pour éviter les injections SQL
                $tagsQuery = "SELECT * FROM tags";
                $result = $mysqli->query($tagsQuery);

                if ($result) {
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
            // Requête pour récupérer les posts avec le tag sélectionné
            $laQuestionEnSql = "SELECT 
                posts.content,
                posts.created,
                users.id as author_id,
                users.alias as author_name,
                count(likes.id) as like_number,
                GROUP_CONCAT(DISTINCT tags.label) AS taglist
                FROM posts_tags as filter 
                JOIN posts ON posts.id=filter.post_id
                JOIN users ON users.id=posts.user_id
                LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                LEFT JOIN likes      ON likes.post_id  = posts.id 
                WHERE filter.tag_id = ? 
                GROUP BY posts.id
                ORDER BY posts.created DESC";

            $stmt = $mysqli->prepare($laQuestionEnSql);
            $stmt->bind_param("i", $tagId);
            $stmt->execute();
            $lesInformations = $stmt->get_result();

            if (!$lesInformations) {
                echo("Échec de la requête : " . $mysqli->error);
            }

            // Affichage des posts
            while ($post = $lesInformations->fetch_assoc()) {
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
                        <small>♥&nbsp<?php echo $post['like_number']; ?></small>
                        <?php
                        // Afficher les tags comme liens cliquables
                        $tagsArray = explode(',', $post['taglist']);
                        foreach ($tagsArray as $tag) {
                            // On crée une requête préparée pour récupérer les tags
                            $checkTagId = "SELECT id FROM tags WHERE label = ?";
                            $stmt = $mysqli->prepare($checkTagId);
                            $stmt->bind_param("s", $tag);
                            $stmt->execute();
                            $TagResult = $stmt->get_result();
                            $TagId = $TagResult->fetch_assoc();

                            // S'assurer que l'ID du tag a bien été récupéré avant de l'utiliser dans le lien
                            if ($TagId) {
                                echo '<a href="tags.php?tag_id=' . $TagId["id"] . '" class="tag-link">#' . htmlspecialchars($tag) . '</a> ';
                            } else {
                                echo '<span>#' . htmlspecialchars($tag) . '</span> ';  // Cas où le tag n'est pas trouvé
                            }
                        }
                        ?>
                    </footer>
                </article>
            <?php }
        }
        ?>
    </main>
</div>
</body>
</html>
