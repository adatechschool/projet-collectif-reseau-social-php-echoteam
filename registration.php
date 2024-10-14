<?php

include 'var_globale.php';

echo $head;
?>

        <div id="wrapper" >

            <aside>
                <h2>Présentation</h2>
                <p>Bienvenu sur notre réseau social.</p>
            </aside>
            <main>
                <article>
                    <h2>Inscription</h2>
                    <?php
                    // vérifie si on est en train d'afficher ou de traiter le formulaire
                    // si on recoit un champs email rempli il y a une chance que ce soit un traitement
                    $enCoursDeTraitement = isset($_POST['email']);
                    if ($enCoursDeTraitement) {
                        // on ne fait ce qui suit que si un formulaire a été soumis.
                        // récupère ce qu'il y a dans le formulaire (les noms entre crochets sont les div input html"
                        $new_email = $_POST['email'];
                        $new_alias = $_POST['pseudo'];
                        $new_passwd = $_POST['motpasse'];

                        // Verification des champs et création des messages d'erreur éventuels
                        $erreurs = [];
                        if (empty($new_alias)) {
                            $erreurs[] = "Le pseudo est requis.";
                        }
                        if (empty($new_email)) {
                            $erreurs[] = "L'email est requis.";
                        }
                        if (empty($new_passwd)) {
                            $erreurs[] = "Le mot de passe est requis.";
                        }

                        if (count($erreurs) === 0) { //(si aucune erreur n'est inscrite dans le tableau de message d'erreur, le code s'execute)
                            //Petite sécurité pour éviter les injections sql (https://www.w3schools.com/sql/sql_injection.asp):
                            $new_email = $mysqli->real_escape_string($new_email);
                            $new_alias = $mysqli->real_escape_string($new_alias);
                            $new_passwd = $mysqli->real_escape_string($new_passwd);
                            // on crypte le mot de passe pour éviter d'exposer notre utilisatrice en cas d'intrusion dans nos systèmes
                            // (md5 est pédagogique mais n'est pas recommandée pour une vraie sécurité)
                            $new_passwd = md5($new_passwd);
                            //construction de la requete
                            $lInstructionSql = "INSERT INTO users (id, email, password, alias) "
                                . "VALUES (NULL, "
                                . "'" . $new_email . "', "
                                . "'" . $new_passwd . "', "
                                . "'" . $new_alias . "'"
                                . ");";
                            // exécution de la requete
                            $ok = $mysqli->query($lInstructionSql);
                            if (!$ok) {
                                echo "L'inscription a échoué : " . $mysqli->error;
                            } else {
                                echo "Votre inscription est un succès : " . $new_alias;
                                echo " <a href='login.php'>Connectez-vous.</a>";
                            }
                        } else {
                            // sinon, affiche les erreurs (manque un email ou pseudo ou mot de passe)
                            foreach ($erreurs as $erreur) {
                                echo "<p style='color: red;'>$erreur</p>";
                            }
                        }
                    }
                    ?>
                    <form action="registration.php" method="post">
                        <input type='hidden'name='???' value='achanger'>
                        <dl>
                            <dt><label for='pseudo'>Pseudo</label></dt>
                            <dd><input type='text'name='pseudo' aria-describedby='pseudo-error'></dd>
                            <dt><label for='email'>E-Mail</label></dt>
                            <dd><input type='email'name='email'aria-describedby='email-error'></dd>
                            <dt><label for='motpasse'>Mot de passe</label></dt>
                            <dd><input type='password'name='motpasse'aria-describedby='motpasse-error'></dd>
                        </dl>
                        <input type='submit'>
                    </form>
                </article>
            </main>
        </div>
    </body>
</html>
