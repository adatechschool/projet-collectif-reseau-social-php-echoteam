<?php
include 'var_globale.php';

// Vérification de la connexion et des permissions
redirect_login();

echo $head;

if ($mysqli->connect_errno) {
    echo "Échec de la connexion : " . $mysqli->connect_error;
    exit();
}

// Vérification si l'utilisateur est un administrateur
$sqlAdminCheck = "SELECT role FROM users WHERE id = ?";
$stmt = $mysqli->prepare($sqlAdminCheck);
$stmt->bind_param("i", $_SESSION['connected_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    // Si l'utilisateur n'est pas admin, redirection vers la page d'accueil ou autre page protégée
    echo "Vous n'avez pas les droits d'accès à cette page.";
    exit();
}

// Si l'action delete_user est activée
if (isset($_GET['action']) && $_GET['action'] === 'delete_user' && isset($_GET['id'])) {
    $userIdToDelete = intval($_GET['id']);

    // Désactivation temporaire des contraintes de clé étrangère
    $disableFKCheck = "SET foreign_key_checks = 0";
    $mysqli->query($disableFKCheck);

    // Suppression de l'utilisateur
    $deleteSql = "DELETE FROM users WHERE id = ?";
    $deleteStmt = $mysqli->prepare($deleteSql);
    $deleteStmt->bind_param("i", $userIdToDelete);

    if ($deleteStmt->execute()) {
        // Réactivation des contraintes de clé étrangère
        $enableFKCheck = "SET foreign_key_checks = 1";
        $mysqli->query($enableFKCheck);

        // Redirection après la suppression
        header('Location: admin.php?deleted=true');
        exit(); // On arrête l'exécution du script après la redirection
    } else {
        // Réactivation des contraintes de clé étrangère même en cas d'erreur
        $mysqli->query("SET foreign_key_checks = 1");
        echo "<p>Erreur lors de la suppression de l'utilisateur.</p>";
        exit();
    }
}

?>

<div id="wrapper" class="admin">
    <aside>
        <h2>Mots-clés</h2>
        <?php
        $laQuestionEnSql = "SELECT * FROM tags LIMIT 50";
        $lesInformations = $mysqli->query($laQuestionEnSql);

        if (!$lesInformations) {
            echo "Échec de la requête : " . $mysqli->error;
            exit();
        }

        while ($tag = $lesInformations->fetch_assoc()) {
            ?>
            <article>
                <h3><?php echo $tag['label']; ?></h3>
                <p><?php echo $tag['id']; ?></p>
                <nav>
                    <a href="tags.php?tag_id=<?php echo $tag['id']; ?>">Messages</a>
                </nav>
            </article>
        <?php } ?>
    </aside>

    <main>
        <h2>Utilisateurs</h2>
        <?php
        // Récupération de la liste des utilisateurs
        $laQuestionEnSql = "SELECT id, alias, role FROM users LIMIT 50";
        $lesInformations = $mysqli->query($laQuestionEnSql);

        if (!$lesInformations) {
            echo "Échec de la requête : " . $mysqli->error;
            exit();
        }

        while ($user = $lesInformations->fetch_assoc()) {
            ?>
            <article>
                <h3><a href="wall.php?user_id=<?php echo $user['id']; ?>"><?php echo $user['alias']; ?></a></h3>
                <p>Rôle: <?php echo $user['role'] == 'admin' ? 'Admin' : 'User'; ?></p>
                <p>ID: <?php echo $user['id']; ?></p>

                <nav>
                    <a href="wall.php?user_id=<?php echo $user['id']; ?>">Mur</a>
                    <a href="feed.php?user_id=<?php echo $user['id']; ?>">Flux</a>
                    <a href="settings.php?user_id=<?php echo $user['id']; ?>">Paramètres</a>
                    <a href="followers.php?user_id=<?php echo $user['id']; ?>">Suiveurs</a>
                    <a href="subscriptions.php?user_id=<?php echo $user['id']; ?>">Abonnements</a>

                    <!-- Si l'utilisateur a le rôle 'user', on affiche un bouton pour supprimer l'utilisateur -->
                    <?php if ($user['role'] == 'user') { ?>
                        <a href="admin.php?action=delete_user&id=<?php echo $user['id']; ?>"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                    <?php } ?>
                </nav>
            </article>
        <?php } ?>

        <!-- Code pour afficher un message de suppression -->
        <?php
        if (isset($_GET['deleted']) && $_GET['deleted'] === 'true') {
            echo "<p>L'utilisateur a été supprimé avec succès.</p>";
        }
        ?>
    </main>
</div>

</body>
</html>
