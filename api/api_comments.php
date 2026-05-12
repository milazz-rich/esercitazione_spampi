<?php
require_once __DIR__ . '/../services/app.service.php';

if (!checkAuth()) {
    jsonResponse(['ok' => false, 'error' => 'Non autenticato'], 401);
}

if (!function_exists('mysqli_connect')) {
    jsonResponse(['ok' => false, 'error' => 'mysqli non disponibile'], 500);
}

$movieId = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;
if ($movieId <= 0) {
    jsonResponse(['ok' => false, 'error' => 'movie_id non valido'], 400);
}

$conn = dbConnect();
if (!$conn) {
    jsonResponse(['ok' => false, 'error' => 'Connessione database fallita'], 500);
}

$movieIdEscaped = mysqli_real_escape_string($conn, (string)$movieId);
$sort = $_GET['sort'] ?? 'newest';
$sortSql = $sort === 'oldest' ? 'created_at ASC, id ASC' : 'created_at DESC, id DESC';
$query = "SELECT id, username, text, created_at FROM comments WHERE movie_id = " . $movieIdEscaped . " ORDER BY " . $sortSql;
$res = mysqli_query($conn, $query);

if (!$res) {
    mysqli_close($conn);
    jsonResponse(['ok' => false, 'error' => 'Errore query commenti'], 500);
}

$comments = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['can_delete'] = currentUsername() !== '' && $row['username'] === currentUsername();
    $comments[] = $row;
}

mysqli_free_result($res);
mysqli_close($conn);

jsonResponse($comments);
?>
