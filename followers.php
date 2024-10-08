<!doctype html>
<html lang="fr">
    <?php
    include 'test.php';

    echo $test;
?>
            <aside>
                <img src = "user.jpg" alt = "Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez la liste des personnes qui
                        suivent les messages de l'utilisatrice
                        n° <?php echo intval($_GET['user_id']) ?></p>

                </section>
            </aside>
            <main class='contacts'>
                <?php
                include 'connexion.php';

                $userId = intval($_GET['user_id']);
                // Etape 2: se connecter à la base de donnée
                // Etape 3: récupérer le nom de l'utilisateur
                $laQuestionEnSql = "
                    SELECT users.*
                    FROM followers
                    LEFT JOIN users ON users.id=followers.following_user_id
                    WHERE followers.followed_user_id='$userId'
                    GROUP BY users.id
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                // Etape 4: à vous de jouer
                //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci dessous 
                while ($followers = $lesInformations->fetch_assoc()) {
                    ?>
                
     
                <article>
                    <img src="user.jpg" alt="blason"/>
                    <h3><?php echo $followers['alias']?></h3>
                    <p><?php echo $followers['id']?></p>                    
                </article> <?php
            }
            ?>
            </main>
        </div>
    </body>
</html>
