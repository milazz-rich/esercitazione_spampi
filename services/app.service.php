<?php
require_once __DIR__ . '/../config/db.config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function checkAuth() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
}

function currentUsername() {
    return $_SESSION['username'] ?? '';
}

function requireAuth($redirectPath = 'login.php') {
    if (!checkAuth()) {
        header('Location: ' . $redirectPath);
        exit;
    }
}

function dbConnect() {
    global $dbconfig;
    return mysqli_connect($dbconfig['host'], $dbconfig['user'], $dbconfig['password'], $dbconfig['name']);
}

function attemptLogin($username, $password) {
    $conn = dbConnect();
    if (!$conn) {
        return ['ok' => false, 'error' => 'Connessione al database non riuscita.'];
    }

    $usernameEscaped = mysqli_real_escape_string($conn, $username);
    $query = "SELECT * FROM users WHERE username = '" . $usernameEscaped . "'";
    $res = mysqli_query($conn, $query);

    if (!$res || mysqli_num_rows($res) === 0) {
        if ($res) {
            mysqli_free_result($res);
        }
        mysqli_close($conn);
        return ['ok' => false, 'error' => 'Username e/o password errati.'];
    }

    $entry = mysqli_fetch_assoc($res);
    $storedPassword = $entry['password'];
    $passwordOk = password_verify($password, $storedPassword) || hash_equals($storedPassword, $password);

    mysqli_free_result($res);
    mysqli_close($conn);

    if (!$passwordOk) {
        return ['ok' => false, 'error' => 'Username e/o password errati.'];
    }

    $_SESSION['username'] = $entry['username'];
    $_SESSION['user_id'] = $entry['id'];

    return ['ok' => true];
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function logoutAndRedirect($redirectPath = '../index.php') {
    logoutUser();
    header('Location: ' . $redirectPath);
    exit;
}

function jsonResponse($payload, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function resolvePosterPath($poster, $fromPages = false) {
    $poster = (string)$poster;
    if ($poster === '') {
        return '';
    }

    if (strpos($poster, 'img/') === 0) {
        $poster = 'assets/' . substr($poster, 4);
    }

    if ($fromPages) {
        return '../' . ltrim($poster, '/');
    }

    return $poster;
}
?>
