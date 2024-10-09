<?php
session_start();
if (!isset($_SESSION['connected_id'])) {
    // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['connected_id'];
?>
<!doctype html>
<html lang="fr">
<?php
include 'var_globale.php';
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
                         <?php echo $userId['id'] ?>
                    </p>
                </section>
            </aside>
            <main>
                <article>
                    <h2>Poster un message</h2>
                    <?php
                    $laQuestionEnSql = "SELECT * FROM users";
                    $lesInformations = $mysqli->query($laQuestionEnSql);
                    while ($user = $lesInformations->fetch_assoc())
                    {
                        $listAuteurs[$user['id']] = $user['alias'];
                    }

                    $enCoursDeTraitement = isset($_POST['auteur']);
                    if ($enCoursDeTraitement) {
                        // on ne fait ce qui suit que si un formulaire a été soumis.
                        // On récupère ce qu'il y a dans le formulaire
                        $authorId = $_SESSION['connected_id'];
                        $postContent = $_POST['message'];

                        // Verification des champs et création des messages d'erreur éventuels
                        $erreurs = [];
                        if (empty($postContent)) {
                            $erreurs[] = "Le message est requis.";
                        }

                        if (count($erreurs) === 0) { //(si aucune erreur n'est inscrite dans le tableau de message d'erreur, le code s'execute)
                            //Petite sécurité pour éviter les injections sql
                            $authorId = intval($mysqli->real_escape_string($authorId));
                            $postContent = $mysqli->real_escape_string($postContent);
                            //construction de la requête
                            $lInstructionSql = "INSERT INTO posts "
                                    . "(id, user_id, content, created, parent_id) "
                                    . "VALUES (NULL, "
                                    . $authorId . ", "
                                    . "'" . $postContent . "', "
                                    . "NOW(), "
                                    . "NULL);";
                            //Execution
                            $ok = $mysqli->query($lInstructionSql);
                            if (!$ok) {
                                echo "Impossible d'ajouter le message: " . $mysqli->error;
                            } else {
                                echo "Message posté avec succès.";                            }
                        } else {
                            // sinon, affiche les erreurs (manque un email ou pseudo ou mot de passe)
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


                    <?php
                $laQuestionEnSql = 
                "SELECT posts.content, 
                    posts.created,    
                    users.alias as author_name,
                    users.id as author_id,
                    COUNT(likes.id) as like_number,
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                   
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE posts.user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";

                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                while ($post = $lesInformations->fetch_assoc())
                {
                    ?>                
                    <article>
                        <h3>
                            <time ><?php echo $post['created'] ?></time>
                        </h3>
                       
                        <div>
                        <address>
    <a href="wall.php?user_id=<?php echo $post['author_id'] ?>" title="<?php echo $post['author_name'] ?>">
        <?php echo $post['author_name'] ?>
    </a>
</address>
                            <p><?php echo $post['content']?></p>
                        </div>                                            
                        <footer>
                            <small>♥ <?php echo $post['like_number']?></small>
                            <a href=""><?php echo $post['taglist']?></a>
                        </footer>
                    </article>
                <?php } ?>

            </main>
        </div>
    </body>
</html>
