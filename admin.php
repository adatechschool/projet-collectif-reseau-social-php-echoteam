<!doctype html>
<html lang="fr">
<?php
include 'var_globale.php';
echo $head;
?>

        <?php
        if ($mysqli->connect_errno)
        {
            echo("Échec de la connexion : " . $mysqli->connect_error);
            exit();
        }
        ?>
        <div id="wrapper" class='admin'>
            <aside>
                <h2>Mots-clés</h2>
                <?php

                $laQuestionEnSql = "SELECT * FROM `tags` LIMIT 50";
                $lesInformations = $mysqli->query($laQuestionEnSql);

                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                    exit();
                }

                while ($tag = $lesInformations->fetch_assoc())
                {

                    ?>
                    <article>
                        <h3><?php echo $tag['label'] ?></h3>
                        <p><?php echo $tag['id'] ?></p>
                        <nav>
                            <a href="tags.php?tag_id=<?php echo $tag['id']; ?>">Messages</a>
                        </nav>
                    </article>
                <?php } ?>
            </aside>
            <main>
                <h2>Utilisatrices</h2>
                <?php

                $laQuestionEnSql = "SELECT * FROM `users` LIMIT 50";
                $lesInformations = $mysqli->query($laQuestionEnSql);

                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                    exit();
                }

                while ($tag = $lesInformations->fetch_assoc())
                {

                    ?>
                    <article>
                        <h3><?php echo $tag['alias'] ?></h3>
                        <p><?php echo $tag['id'] ?></p>
                        <nav>
                            <a href="wall.php?user_id=123">Mur</a>
                            | <a href="feed.php?user_id=123">Flux</a>
                            | <a href="settings.php?user_id=123">Paramètres</a>
                            | <a href="followers.php?user_id=123">Suiveurs</a>
                            | <a href="subscriptions.php?user_id=123">Abonnements</a>
                        </nav>
                    </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>