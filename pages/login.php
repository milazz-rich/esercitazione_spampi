<?php
    require_once __DIR__ . '/../services/auth.service.php';
    $mysqliAvailable = function_exists('mysqli_connect');

    if (checkAuth()) {
        header('Location: movie.php');
        exit;
    }

    if (!$mysqliAvailable) {
        $error = "Estensione mysqli non attiva nel PHP in uso.";
    }
    else if (!empty($_POST["username"]) && !empty($_POST["password"]) )
    {

        $conn = mysqli_connect($dbconfig['host'], $dbconfig['user'], $dbconfig['password'], $dbconfig['name']) or die(mysqli_error($conn));

        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $query = "SELECT * FROM users WHERE username = '".$username."'";

        $res = mysqli_query($conn, $query) or die(mysqli_error($conn));;
        
        if (mysqli_num_rows($res) > 0) {
            $entry = mysqli_fetch_assoc($res);
            $inputPassword = $_POST['password'];
            $storedPassword = $entry['password'];
            $passwordOk = password_verify($inputPassword, $storedPassword) || hash_equals($storedPassword, $inputPassword);

            if ($passwordOk) {

                // Imposto una sessione dell'utente
                $_SESSION["username"] = $entry['username'];
                $_SESSION["user_id"] = $entry['id'];
                header("Location: home.php");
                mysqli_free_result($res);
                mysqli_close($conn);
                exit;
            }
        }
        $error = "Username e/o password errati.";
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
