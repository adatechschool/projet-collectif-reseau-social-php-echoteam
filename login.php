<?php
include 'var_globale.php';


echo $head;
?>

        <div id="wrapper" >


            <main>

                <article>
                    <?php
                    if (!isset($_SESSION['connected_id'])) {
                        echo '<h2>⚠️</h2>
          <p>Connexion obligatoire pour accéder au site!</p>';
                    }
                    ?>
                    <h2>Connexion</h2>
                    <?php
                    // vérifie si on est en train d'afficher ou de traiter le formulaire
                    // si on recoit un champs email rempli il y a une chance que ce soit un traitement
                    $enCoursDeTraitement = isset($_POST['email']);
                    if ($enCoursDeTraitement)
                    {
                        // on ne fait ce qui suit que si un formulaire a été soumis.
                        // récupère ce qu'il y a dans le formulaire
                        $emailAVerifier = $_POST['email'];
                        $passwdAVerifier = $_POST['motpasse'];

                        //Petite sécurité pour éviter les injections sql
                        $emailAVerifier = $mysqli->real_escape_string($emailAVerifier);
                        $passwdAVerifier = $mysqli->real_escape_string($passwdAVerifier);
                        // on crypte le mot de passe pour éviter d'exposer notre utilisatrice en cas d'intrusion dans nos systèmes
                        $passwdAVerifier = md5($passwdAVerifier);
                        //construction de la requete
                        $lInstructionSql = "SELECT * "
                                . "FROM users "
                                . "WHERE "
                                . "email LIKE '" . $emailAVerifier . "'"
                                ;
                        // Vérification de l'utilisateur
                        $res = $mysqli->query($lInstructionSql);
                        $user = $res->fetch_assoc();
                        if ( ! $user OR $user["password"] != $passwdAVerifier)
                        {
                            echo "La connexion a échouée. ";
                            
                        } else
                        {
                            echo "Votre connexion est un succès : " . $user['alias'] . ".";
                            // Se souvenir que l'utilisateur s'est connecté pour la suite
                            // documentation: https://www.php.net/manual/fr/session.examples.basic.php
                            $_SESSION['connected_id']=$user['id'];
                            // Redirection après connexion réussie vers le feed
                            header("Location: feed.php"); // Redirige vers une page protégée
                            exit();
                        }
                    }


        if (isset($_SESSION['connected_id'])) {
            // L'utilisateur est connecté, afficher le bouton de déconnexion
            echo '<form action="logout.php" method="post">';
            echo '<input type="submit" value="Déconnexion">';
            echo '</form>';
        } else {
            // L'utilisateur n'est pas connecté, afficher le formulaire de connexion
            ?>
                    <form action="login.php" method="post">
                        <dl>
                            <dt><label for='email'>E-Mail</label></dt>
                            <dd><input type='email' name='email' aria-describedby='email-error'></dd>
                            <dt><label for='motpasse'>Mot de passe</label></dt>
                            <dd><input type='password' name='motpasse' aria-describedby='motpasse-error'></dd>
                        </dl>
                        <input type='submit' value="Connexion">
                    </form>
                    <p>
                        Pas de compte?
                        <a href='registration.php'>Inscrivez-vous.</a>
                    </p>
                    <?php
                    }
                    ?>
                </article>
            </main>

        </div>
    </body>
</html>
