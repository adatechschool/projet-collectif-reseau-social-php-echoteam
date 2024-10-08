<!doctype html>
<html lang="fr">
<?php
include 'var_globale.php';

echo $test;
?>

        <div id="wrapper">
            <aside>
                <?php
               

                $laQuestionEnSql = "SELECT * FROM `users` WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();
                //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
//                echo "<pre>" . print_r($user, 1) . "</pre>";
                ?>
                <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les messages des utilisatrices
                        auxquel est abonnée l'utilisatrice :
                        <?php echo $user['alias'] ?>
                    </p>

                </section>
            </aside>
            <main>
                <?php
                /**
                 * Etape 3: récupérer tous les messages des abonnements
                 */
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    users.alias as author_name,  
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM followers 
                    JOIN users ON users.id=followers.followed_user_id
                    JOIN posts ON posts.user_id=users.id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE followers.following_user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                /**
                 * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
                 * A vous de retrouver comment faire la boucle while de parcours...
                 */
                while ($post = $lesInformations->fetch_assoc())
                {
                ?>                
                <article>
                    <h3>
                        <time ><?php echo $post['created'] ?></time>
                    </h3>
                    <address><?php echo $post['author_name']?></address>
                    <div>
                        <p><?php echo $post['content']?></p>
                    </div>                                            
                    <footer>
                        <small>♥ <?php echo $post['like_number']?></small>
                            <a href=""><?php echo $post['taglist']?></a>
                    </footer>
                </article>
                <?php }
                // et de pas oublier de fermer ici vote while
                ?>


            </main>
        </div>
    </body>
</html>
