<?php
require_once __DIR__ . '/../services/app.service.php';

if (!checkAuth()) {
    jsonResponse(['ok' => false, 'error' => 'Non autenticato'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'error' => 'Metodo non consentito'], 405);
}

$commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
if ($commentId <= 0) {
    jsonResponse(['ok' => false, 'error' => 'comment_id non valido'], 400);
}

$conn = dbConnect();
if (!$conn) {
    jsonResponse(['ok' => false, 'error' => 'Connessione database fallita'], 500);
}

$commentIdEscaped = mysqli_real_escape_string($conn, (string)$commentId);
$usernameEscaped = mysqli_real_escape_string($conn, currentUsername());
$query = "DELETE FROM comments WHERE id = '" . $commentIdEscaped . "' AND username = '" . $usernameEscaped . "'";
$ok = mysqli_query($conn, $query);

if (!$ok) {
    mysqli_close($conn);
    jsonResponse(['ok' => false, 'error' => 'Eliminazione fallita'], 500);
}

$affected = mysqli_affected_rows($conn);
mysqli_close($conn);

if ($affected === 0) {
    jsonResponse(['ok' => false, 'error' => 'Commento non trovato o non autorizzato'], 403);
}

jsonResponse(['ok' => true]);
?>
