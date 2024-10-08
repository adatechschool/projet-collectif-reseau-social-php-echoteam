
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Paramètres</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <header>
        <img src="resoc.jpg" alt="Logo de notre réseau social"/>
        <nav id="menu">
            <a href="news.php">Actualités</a>
            <a href="wall.php?user_id=5">Mur</a>
            <a href="feed.php?user_id=5">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php?user_id=5">Paramètres</a></li>
                <li><a href="followers.php?user_id=5">Mes suiveurs</a></li>
                <li><a href="subscriptions.php?user_id=5">Mes abonnements</a></li>
            </ul>
        </nav>
    </header>
    <div id="wrapper" class='profile'>
        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez les informations des utilisateurs.</p>
            </section>
        </aside>
        <main>
            <?php
            /**
             * Etape 2: se connecter à la base de donnée
             */
            include 'connexion.php';

            /**
             * Etape 3: récupérer les utilisateurs
             */
            $laQuestionEnSql = "
                SELECT users.*,
                count(DISTINCT posts.id) as totalpost,
                count(DISTINCT given.post_id) as totalgiven,
                count(DISTINCT recieved.user_id) as totalrecieved
                FROM users
                LEFT JOIN posts ON posts.user_id=users.id
                LEFT JOIN likes as given ON given.user_id=users.id
                LEFT JOIN likes as recieved ON recieved.post_id=posts.id
                WHERE users.id BETWEEN 1 AND 7
                GROUP BY users.id
            ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            if (!$lesInformations) {
                echo("Échec de la requête : " . $mysqli->error);
            }
            /**
             * Etape 4: afficher les informations de chaque utilisateur
             */
            while ($user = $lesInformations->fetch_assoc()) {
            ?>
                <article class='parameters'>
                    <h3>Mes paramètres pour l'utilisateur <?php echo $user['alias']; ?></h3>
                    <dl>
                        <dt>Pseudo</dt>
                        <dd><?php echo $user['alias']; ?></dd>
                        <dt>Email</dt>
                        <dd><?php echo $user['email']; ?></dd>
                        <dt>Nombre de message</dt>
                        <dd><?php echo $user['totalpost']; ?></dd>
                        <dt>Nombre de "J'aime" donnés </dt>
                        <dd><?php echo $user['totalgiven']; ?></dd>
                        <dt>Nombre de "J'aime" reçus</dt>
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
