<?php

include 'var_globale.php';
redirect_login();

echo $head;
?>
    <div id="wrapper" class='profile'>
        <aside>
            <img src="user.png" alt="Portrait de l'utilisatrice"/>
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez les informations des utilisateurs.</p>
            </section>
        </aside>
        <main>

            <?php
            $laQuestionEnSql = "SELECT users.*,
                count(DISTINCT posts.id) as totalpost,
                count(DISTINCT given.post_id) as totalgiven,
                count(DISTINCT recieved.user_id) as totalrecieved
                FROM users
                LEFT JOIN posts ON posts.user_id=users.id
                LEFT JOIN likes as given ON given.user_id=users.id
                LEFT JOIN likes as recieved ON recieved.post_id=posts.id
                WHERE users.id='$userId'
                GROUP BY users.id
            ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            if (!$lesInformations) {
                echo("Échec de la requête : " . $mysqli->error);
            }

            while ($user = $lesInformations->fetch_assoc()) {
            ?>
                <article class='parameters'>
                    <h3>Mes paramètres pour l'utilisateur <?php echo $userId; ?></h3>
                    <dl>
                        <dt>Pseudo</dt>
                        <dd><?php echo $user['alias']; ?></dd>
                        <dt>Email</dt>
                        <dd><?php echo $user['email']; ?></dd>
                        <dt>Nombre de message</dt>
                        <dd><?php echo $user['totalpost']; ?></dd>
                        <dt>Nombre de "J'aime" donnés &nbsp</dt>
                        <dd><?php echo $user['totalgiven']; ?></dd>
                        <dt>Nombre de "J'aime" reçus &nbsp</dt>
                        <dd><?php echo $user['totalrecieved']; ?></dd>
                    </dl>
                </article>
            <?php
            }
            ?>
        </main>
    </div>
</body>
</html>
