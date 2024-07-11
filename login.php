
<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['loggedin'] = true;
        header('Location: game.php');
    } else {
        echo 'Login failed';
    }
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
    

    <div class="home-parent1">
        <div class="form-container1">
           
            <form class="form1" method="post" action="login.php">
                <div class="form-group1">
                    <label for="name">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group1">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button class="button1" type="submit">Connexion</button>
            </form>
        </div>
    </div>



    
    <?php
        include_once 'footer.php';
    ?>
</body>
</html>
