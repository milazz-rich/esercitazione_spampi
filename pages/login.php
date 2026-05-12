<?php
    require_once __DIR__ . '/../services/app.service.php';
    $mysqliAvailable = function_exists('mysqli_connect');
    $defaultRedirect = '../index.php';

    $next = $_GET['next'] ?? $_POST['next'] ?? '';
    if (!is_string($next)) {
        $next = '';
    }

    $nextPath = parse_url($next, PHP_URL_PATH);
    $nextHost = parse_url($next, PHP_URL_HOST);
    $nextScheme = parse_url($next, PHP_URL_SCHEME);
    $isSafeNext = $next !== '' && empty($nextHost) && empty($nextScheme) && ($nextPath === 'movie.php' || $nextPath === '../index.php' || $nextPath === 'index.php');
    $redirectTarget = $isSafeNext ? $next : $defaultRedirect;

    if (checkAuth()) {
        header('Location: ' . $redirectTarget);
        exit;
    }

    if (!$mysqliAvailable) {
        $error = "Estensione mysqli non attiva nel PHP in uso.";
    }
    else if (!empty($_POST["username"]) && !empty($_POST["password"]) )
    {

        $loginResult = attemptLogin($_POST['username'], $_POST['password']);
        if ($loginResult['ok']) {
            header("Location: " . $redirectTarget);
            exit;
        }
        $error = $loginResult['error'];
    }
    else if (isset($_POST["username"]) || isset($_POST["password"])) {
        $error = "Inserisci username e password.";
    }

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-card" aria-labelledby="login-title">
            <p class="eyebrow">Bentornato</p>
            <h1 id="login-title">Accedi al tuo account</h1>
            <p class="subtitle">Inserisci le tue credenziali per continuare.</p>

            <?php if (!empty($error)): ?>
                <div class="alert" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" class="login-form" autocomplete="on">
                <input type="hidden" name="next" value="<?php echo htmlspecialchars($redirectTarget); ?>">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="es. mario.rossi"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    required
                >

                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="La tua password"
                    required
                >

                <button type="submit">Accedi</button>
            </form>
        </section>
    </main>
</body>
</html>
