<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hachage du mot de passe

    // Vérifier si le nom d'utilisateur existe déjà
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $_SESSION['flash'] = 'Username already exists. Please choose a different username.';
        header('Location: signup.php');
        exit();
    }

    // Insérer l'utilisateur s'il n'existe pas déjà
    $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    $stmt->execute([$username, $password]);

    $_SESSION['flash'] = 'User registered successfully';
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<?php
        include_once 'head.php';
    ?>

<body>
    <?php
        include_once 'header.php';
    ?>
    <?php
    // Afficher le message flash s'il est défini
    if(isset($_SESSION['flash'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['flash'] . '</div>';
        unset($_SESSION['flash']);
    }
    ?>

    

    <div class="home-parent1">
        <div class="form-container1">
            <!-- <h2>Inscription</h2> -->
            <form class="form1" method="post" action="register.php" >
                <div class="form-group1">
                    <label for="name">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group1">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button class="button1" type="submit">Register</button>
            </form>
        </div>
    </div>
    <?php
        
        include_once 'footer.php';
    ?>
</body>
</html>
