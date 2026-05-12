<?php
require_once __DIR__ . '/../services/app.service.php';

if (!checkAuth()) {
    jsonResponse(['ok' => false, 'error' => 'Non autenticato'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'error' => 'Metodo non consentito'], 405);
}

if (!function_exists('mysqli_connect')) {
    jsonResponse(['ok' => false, 'error' => 'mysqli non disponibile'], 500);
}

$movieId = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;
$text = trim($_POST['text'] ?? '');
$username = $_SESSION['username'] ?? '';

if ($movieId <= 0 || $text === '' || $username === '') {
    jsonResponse(['ok' => false, 'error' => 'Dati mancanti o non validi'], 400);
}

$conn = dbConnect();
if (!$conn) {
    jsonResponse(['ok' => false, 'error' => 'Connessione database fallita'], 500);
}

$movieIdEscaped = mysqli_real_escape_string($conn, (string)$movieId);
$textEscaped = mysqli_real_escape_string($conn, $text);
$usernameEscaped = mysqli_real_escape_string($conn, $username);

$query = "INSERT INTO comments (movie_id, username, text) VALUES ('" . $movieIdEscaped . "', '" . $usernameEscaped . "', '" . $textEscaped . "')";
$ok = mysqli_query($conn, $query);

mysqli_close($conn);

if (!$ok) {
    jsonResponse(['ok' => false, 'error' => 'Inserimento commento fallito'], 500);
}

jsonResponse(['ok' => true]);
?>
