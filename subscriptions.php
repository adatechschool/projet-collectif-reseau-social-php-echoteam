<?php

include 'var_globale.php';
maFonction();

echo $head;
?>
        <div id="wrapper">
            <aside>
                <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez la liste des personnes dont
                        l'utilisatrice :
                        <?php echo $_SESSION['connected_id']; ?>
                        suit les messages
                    </p>
                </section>
            </aside>
            <main class='contacts'>

                <?php
                $laQuestionEnSql = " SELECT users.* 
                    FROM followers 
                    JOIN users ON users.id=followers.followed_user_id
                    WHERE followers.following_user_id='$userId'
                    GROUP BY users.id
                    ";
                $lesInformations = $mysqli->query($laQuestionEnSql);

                while ($following = $lesInformations->fetch_assoc()) {
                    ?>
                <article>
                    <img src="user.jpg" alt="blason"/>
                    <h3><a href="wall.php?user_id=<?php echo $following['id']?>"><?php echo $following['alias']?></a></h3>
                    <p><?php echo $following['id']?></p>                    
                </article> <?php
            }
            ?>
            </main>
        </div>
    </body>
</html>
