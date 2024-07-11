<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Si le formulaire est soumis pour redémarrer le jeu
if (isset($_POST['restart'])) {
    session_unset(); // Réinitialise toutes les variables de session
    session_destroy(); // Détruit la session
    session_start(); // Démarre une nouvelle session
    header('Location: game.php'); // Recharge la page pour démarrer un nouveau jeu
    exit;
}

// Si le formulaire est soumis pour choisir une nouvelle difficulté
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['difficulty'])) {
    $difficulty = $_POST['difficulty'];
    $_SESSION['difficulty'] = $difficulty;
    $_SESSION['word'] = get_random_french_word($difficulty);
}

// Choix du mot aléatoire en fonction de la difficulté enregistrée dans la session
if (!isset($_SESSION['word']) || isset($_POST['restart'])) {
    $difficulty = $_SESSION['difficulty'] ?? 'medium'; // Utilise la difficulté enregistrée ou par défaut 'medium'
    $_SESSION['word'] = get_random_french_word($difficulty);
}

$word = str_pad($_SESSION['word'], 8); // Remplir le mot à 8 caractères

// Fonction pour obtenir un mot aléatoire en français depuis l'API RandomWord
function get_random_french_word($difficulty) {
    switch ($difficulty) {
        case 'easy':
            $length = 4; // Mots de 4 caractères pour la difficulté facile
            break;
        case 'medium':
            $length = 6; // Mots de 6 caractères pour la difficulté moyenne
            break;
        case 'hard':
            $length = 8; // Mots de 8 caractères pour la difficulté difficile
            break;
        default:
            $length = 6; // Par défaut, mots de 6 caractères pour la difficulté moyenne
            break;
    }

    // URL de l'API RandomWord pour obtenir un mot aléatoire en français
    $api_url = "https://random-word-api.vercel.app/api?words=1&lang=fr&length=$length";

    // Initialisez un contexte pour la requête HTTP
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true, // Ignorer les erreurs HTTP pour pouvoir récupérer le contenu même en cas de 404
        ],
    ]);

    // Effectuez la requête HTTP
    $response = file_get_contents($api_url, false, $context);

    // Vérifiez si la réponse est vide ou a échoué
    if ($response === false) {
        return 'default'; // Retourner une valeur par défaut en cas d'échec de la requête
    }

    // Décodage de la réponse JSON
    $data = json_decode($response);

    // Vérifiez si la réponse contient un mot valide
    if ($data && is_array($data) && count($data) > 0) {
        return $data[0]; // Retourner le premier mot du tableau (le mot aléatoire)
    }

    return 'default';
}

// Fonction pour vérifier la proposition de l'utilisateur par rapport au mot choisi
function check_guess($word, $guess) {
    $result = [];
    $guess = str_pad($guess, 8); // Remplir la proposition à 8 caractères

    for ($i = 0; $i < 8; $i++) {
        if (isset($guess[$i]) && isset($word[$i])) {
            if (($guess[$i] == $word[$i]) && !empty(trim($guess[$i])) && $guess[$i] !== ' ') {
                $result[] = 'correct';
            } elseif ((strpos($word, $guess[$i]) !== false) && !empty(trim($guess[$i])) && $guess[$i] !== ' ') {
                $result[] = 'present';
            } else {
                $result[] = 'absent';
            }
        } else {
            $result[] = 'absent'; // Si l'indice n'est pas défini, on le considère absent
        }
    }
    return $result;
}

// Initialisation des variables de session pour les devinettes
if (!isset($_SESSION['guesses'])) {
    $_SESSION['guesses'] = [];
}

// Traitement du formulaire de devinette soumis par l'utilisateur
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guess'])) {
    $guess = $_POST['guess'];
    if (count($_SESSION['guesses']) < 8) {
        $result = check_guess($word, $guess);

        // Calcul du score
        $score = count(array_filter($result, function($v) { return $v == 'correct'; }));
        $stmt = $pdo->prepare('INSERT INTO scores (user_id, score) VALUES (?, ?)');
        $stmt->execute([$_SESSION['user_id'], $score]);

        $_SESSION['guesses'][] = [
            'guess' => $guess,
            'result' => $result
        ];
    }

    // Vérification si le mot a été correctement deviné
    if ($guess === $_SESSION['word']) {
        $game_over = true; // Variable pour afficher le message de félicitations
    }
}

$guesses = $_SESSION['guesses'];
?>

<!DOCTYPE html>
<html>
<?php include_once 'head.php'; ?>
<body>

<?php include_once 'header.php'; ?>

<div class="container">
    

    <?php if (isset($game_over) && $game_over): ?>
        <div class="button-container">
            <p class="p-color">Congratulations! You've guessed the word: <?= strtoupper($word) ?></p>
        </div>
        
    <?php else: ?>
        <?php if (count($guesses) >= 6 && $guesses[count($guesses) - 1]['guess'] !== $word): ?>
            <div class="button-container">
                <p class="p-color">Game Over. The word was: <?= strtoupper($word) ?></p>
            </div>
        <?php endif; ?>

    
        <div class="form-container">
            <form class="form1" method="post" action="game.php">
                <div class="form-group">
                    <label for="difficulty">Select Difficulty:</label>
                    <select id="difficulty" name="difficulty" required>
                    <option value="easy" <?php if (isset($_SESSION['difficulty']) && $_SESSION['difficulty'] === 'easy') echo 'selected'; ?>>Easy</option>
        <option value="medium" <?php if (!isset($_SESSION['difficulty']) || $_SESSION['difficulty'] === 'medium') echo 'selected'; ?>>Medium</option>
        <option value="hard" <?php if (isset($_SESSION['difficulty']) && $_SESSION['difficulty'] === 'hard') echo 'selected'; ?>>Hard</option>
                    </select>
                </div>
                <button class="button1" type="submit">Start Game</button>
            </form>
        </div>

        <div class="guess-container">
            <form method="post" action="game.php">
                <div class="input-container">
                    <label for="guess">Guess:</label>
                    <input type="text" id="guess" name="guess" maxlength="8" required>
                    <input type="submit" value="Submit">
                </div>
            </form>
        </div>

        <div class="grid">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <?php for ($j = 0; $j < 8; $j++): ?>
                    <div class="cell <?php
                    if (isset($guesses[$i]['result'][$j])) {
                        echo $guesses[$i]['result'][$j];
                    } else {
                        echo 'absent';
                    }
                    ?>">
                        <?php
                        if ($i == 0 && $j == 0) {
                            // Afficher la première lettre du mot dans la première cellule
                            echo strtoupper($word[0]);
                        } elseif (isset($guesses[$i]['guess'][$j])) {
                            echo strtoupper($guesses[$i]['guess'][$j]);
                        }
                        ?>
                    </div>
                <?php endfor; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($game_over) && $game_over || (count($guesses) >= 6 && $guesses[count($guesses) - 1]['guess'] !== $word)): ?>
        <form method="post" action="game.php">
            <input type="submit" name="restart" value="Restart">
        </form>
    <?php endif; ?>
</div>

<?php include_once 'footer.php'; ?>

</body>
</html>
