<?php

declare(strict_types=1);

session_start();

const STARTING_BALANCE = 100;
const MAX_HISTORY = 10;

if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = STARTING_BALANCE;
}

if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

$errors = [];
$flash  = null;

$action = $_POST['action'] ?? null;

if ($action === 'reset') {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['balance'] = STARTING_BALANCE;
    $_SESSION['history'] = [];
    $flash               = 'La partida se reinició y tienes saldo completo para jugar de nuevo.';
} elseif ($action === 'play') {
    $bet = filter_input(INPUT_POST, 'bet', FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 1,
        ],
    ]);

    if ($bet === false) {
        $errors[] = 'Indica una apuesta válida (un número entero mayor a 0).';
    } elseif ($bet > $_SESSION['balance']) {
        $errors[] = 'No puedes apostar más de tu saldo disponible.';
    }

    if (!$errors) {
        $rolls      = [random_int(1, 6), random_int(1, 6), random_int(1, 6)];
        $multiplier = calculateMultiplier($rolls);
        $payout     = (int) round($bet * $multiplier);

        $_SESSION['balance'] += $payout - $bet;

        $resultText = $multiplier > 1
            ? "¡Ganaste! Multiplicador x$multiplier. Premio: $payout créditos."
            : 'No hubo suerte esta vez, inténtalo de nuevo.';

        array_unshift($_SESSION['history'], [
            'rolls'      => $rolls,
            'bet'        => $bet,
            'multiplier' => $multiplier,
            'balance'    => $_SESSION['balance'],
            'result'     => $resultText,
        ]);

        $_SESSION['history'] = array_slice($_SESSION['history'], 0, MAX_HISTORY);
    }
}

function calculateMultiplier(array $rolls): float
{
    $counts = array_count_values($rolls);
    rsort($counts);

    if ($counts[0] === 3) {
        return 4.0;
    }

    if ($counts[0] === 2) {
        return 2.0;
    }

    return 0.0;
}

function formatRolls(array $rolls): string
{
    return implode(' · ', array_map('intval', $rolls));
}

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casino - Juego de dados</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main class="layout">
        <header>
            <h1>Casino</h1>
            <p>Prueba suerte con el juego de dados. Obtén pares o tríos para multiplicar tu apuesta.</p>
        </header>

        <?php if ($flash): ?>
            <div class="alert success"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="card">
            <div class="balance">
                <span>Saldo disponible</span>
                <strong><?= number_format((float) $_SESSION['balance']); ?> créditos</strong>
            </div>

            <form method="post" class="form">
                <label for="bet">Apuesta</label>
                <input
                    type="number"
                    id="bet"
                    name="bet"
                    min="1"
                    step="1"
                    required
                    value="<?= htmlspecialchars((string) ($_POST['bet'] ?? 10), ENT_QUOTES, 'UTF-8'); ?>">

                <p class="rules">
                    <span>Trío = x4</span>
                    <span>Par = x2</span>
                    <span>Sin coincidencias = pierdes la apuesta</span>
                </p>

                <button class="btn primary" type="submit" name="action" value="play">Jugar</button>
                <button class="btn" type="submit" name="action" value="reset">Reiniciar saldo</button>
            </form>
        </section>

        <section class="card">
            <h2>Historial reciente</h2>
            <?php if (empty($_SESSION['history'])): ?>
                <p class="muted">Juega una ronda para ver el historial.</p>
            <?php else: ?>
                <div class="table">
                    <div class="row head">
                        <span>Dados</span>
                        <span>Apuesta</span>
                        <span>Resultado</span>
                        <span>Saldo después</span>
                    </div>
                    <?php foreach ($_SESSION['history'] as $entry): ?>
                        <div class="row">
                            <span><?= htmlspecialchars(formatRolls($entry['rolls']), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span><?= number_format((float) $entry['bet']); ?> créditos</span>
                            <span><?= htmlspecialchars($entry['result'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span><?= number_format((float) $entry['balance']); ?> créditos</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
