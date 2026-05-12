<?php
require_once __DIR__ . '/../services/app.service.php';

if (!checkAuth()) {
    jsonResponse(['ok' => false, 'error' => 'Non autenticato'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'error' => 'Metodo non consentito'], 405);
}

$movieId = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;
if ($movieId <= 0) {
    jsonResponse(['ok' => false, 'error' => 'movie_id non valido'], 400);
}

$conn = dbConnect();
if (!$conn) {
    jsonResponse(['ok' => false, 'error' => 'Connessione database fallita'], 500);
}

$movieIdEscaped = mysqli_real_escape_string($conn, (string)$movieId);
$updateQuery = "UPDATE movies SET likes = likes + 1 WHERE id = " . $movieIdEscaped;
$ok = mysqli_query($conn, $updateQuery);

if (!$ok) {
    mysqli_close($conn);
    jsonResponse(['ok' => false, 'error' => 'Like non aggiornato'], 500);
}

$selectQuery = "SELECT likes FROM movies WHERE id = " . $movieIdEscaped . " LIMIT 1";
$res = mysqli_query($conn, $selectQuery);
$likes = 0;
if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    $likes = (int)$row['likes'];
}
if ($res) {
    mysqli_free_result($res);
}

mysqli_close($conn);

jsonResponse(['ok' => true, 'likes' => $likes]);
?>
