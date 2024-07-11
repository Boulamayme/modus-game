<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT u.username, s.score FROM scores s JOIN users u ON s.user_id = u.id ORDER BY s.score DESC LIMIT 10');
$stmt->execute();
$scores = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
    <?php include_once 'head.php'; ?>
<body>
<?php include_once 'header.php'; ?>
    <div class="score-container">
        <div><h2 class="score-title" >Scores</h2></div> 
        <div>
            <table class="espace">
                <tr>
                    <th class="score-title1">Username</th>
                    <th class="score-title1">Score</th>
                </tr>
                <?php foreach ($scores as $score): ?>
                <tr>
                    <td><?= htmlspecialchars($score['username']) ?></td>
                    <td><?= htmlspecialchars($score['score']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div ><a class="a-score" href="login.php">Back to game</a></div>
    </div>
    
    <?php include_once 'footer.php'; ?>
</body>
</html>
