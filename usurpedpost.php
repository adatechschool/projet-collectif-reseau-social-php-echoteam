<?php

include 'var_globale.php';
redirect_login();

echo $head;
?>

        <div id="wrapper" >

            <aside>
                <h2>Présentation</h2>
                <p>Sur cette page on peut poster un message en se faisant 
                    passer pour quelqu'un d'autre</p>
            </aside>
            <main>
                <article>
                    <h2>Poster un message</h2>
                    <?php
                     //Récupération de la liste des auteurs
                    $listAuteurs = [];
                    $laQuestionEnSql = "SELECT * FROM users";
                    $lesInformations = $mysqli->query($laQuestionEnSql);
                    while ($user = $lesInformations->fetch_assoc())
                    {
                        $listAuteurs[$user['id']] = $user['alias'];
                    }


                    /**
                     * TRAITEMENT DU FORMULAIRE
                     */
                    // Vérifie si on est en train d'afficher ou de traiter le formulaire
                    // si on recoit un champs email rempli il y a une chance que ce soit un traitement
                    $enCoursDeTraitement = isset($_POST['auteur']);
                    if ($enCoursDeTraitement) {
                        // on ne fait ce qui suit que si un formulaire a été soumis.
                        // On récupére ce qu'il y a dans le formulaire
                        $authorId = $_POST['auteur'];
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
                                echo "Message posté en tant que :" . $listAuteurs[$authorId];
                            }
                        } else {
                            // sinon, affiche les erreurs (manque un email ou pseudo ou mot de passe)
                            foreach ($erreurs as $erreur) {
                                echo "<p style='color: red;'>$erreur</p>";
                            }
                        }
                    }

                    ?>                     
                    <form action="usurpedpost.php" method="post">
                        <input type='hidden' name='???' value='achanger'>
                        <dl>
                            <dt><label for='auteur'>Auteur</label></dt>
                            <dd><select name='auteur'>
                                    <?php
                                    foreach ($listAuteurs as $id => $alias)
                                        echo "<option value='$id'>$alias</option>";
                                    ?>
                                </select></dd>
                            <dt><label for='message'>Message</label></dt>
                            <dd><textarea name='message'aria-describedby='message-error'></textarea></dd>
                        </dl>
                        <input type='submit'>
                    </form>               
                </article>
            </main>
        </div>
    </body>
</html>
