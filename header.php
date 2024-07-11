<?php
// Inclure session.php pour démarrer la session
include('session.php');
?>

<nav class=" navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">Motus Game</a>
        </div>
        <ul class="nav navbar-nav">
            <li class="active"><a href="index.php">Home</a></li>
            
            <li><a href="scores.php">Top Scores</a></li>
        </ul>
        
        <ul class="nav navbar-nav navbar-right">
            <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true): ?>
                <li><a href="game.php">Play</a></li>
                <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>

                
            <?php else: ?>
                <li><a href="register.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>
                <li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<?php
// Afficher et supprimer le message flash s'il est défini
if(isset($_SESSION['flash'])) {
    echo '<div class="alert alert-success">' . $_SESSION['flash'] . '</div>';
    unset($_SESSION['flash']);
}
?>
