<?php
require_once __DIR__ . '/../services/app.service.php';

if (!checkAuth()) {
    $requestedMovieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $next = 'movie.php?id=' . $requestedMovieId;
    header('Location: login.php?next=' . rawurlencode($next));
    exit;
}

$movie = null;
$error = null;
$movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!function_exists('mysqli_connect')) {
    $error = "Estensione mysqli non attiva nel PHP in uso.";
} else if ($movieId <= 0) {
    $error = "Film non valido.";
} else {
    $conn = mysqli_connect($dbconfig['host'], $dbconfig['user'], $dbconfig['password'], $dbconfig['name']);

    if (!$conn) {
        $error = "Connessione al database non riuscita.";
    } else {
        $movieStmt = mysqli_prepare($conn, 'SELECT id, title, plot, poster, likes FROM movies WHERE id = ? LIMIT 1');
        if ($movieStmt) {
            mysqli_stmt_bind_param($movieStmt, 'i', $movieId);
            mysqli_stmt_execute($movieStmt);
            $movieResult = mysqli_stmt_get_result($movieStmt);
            $movie = $movieResult ? mysqli_fetch_assoc($movieResult) : null;
            mysqli_stmt_close($movieStmt);
        }

        if (!$movie) {
            $error = "Film non trovato.";
        }

        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettaglio film</title>
    <link rel="stylesheet" href="../styles/style.css">
    <script src="../scripts/movie.js" defer></script>
</head>
<body class="movies-page">
    <main class="movies-shell">
        <section class="top-nav" aria-label="Barra di navigazione dettaglio film">
            <div>
                <p class="eyebrow">MovieHub</p>
                <h1>Dettaglio film</h1>
            </div>
            <div class="header-actions">
                <a class="pill" href="../index.php">Torna al catalogo</a>
                <button type="button" id="theme-toggle" class="pill alt-action">Dark mode</button>
            </div>
        </section>

        <?php if (!empty($error) || empty($movie)): ?>
            <section class="login-card">
                <div class="alert" role="alert"><?php echo htmlspecialchars($error ?? 'Film non trovato.'); ?></div>
            </section>
        <?php else: ?>
            <?php $posterPath = resolvePosterPath($movie['poster'] ?? '', true); ?>
            <section class="login-card movie-detail">
                <div class="movie-detail-layout">
                    <img class="movie-detail-poster" src="<?php echo htmlspecialchars($posterPath); ?>" alt="Locandina di <?php echo htmlspecialchars($movie['title']); ?>">
                    <div class="movie-detail-side">
                        <p class="eyebrow">Dettaglio film</p>
                        <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                        <p class="subtitle"><?php echo htmlspecialchars($movie['plot'] ?? 'Nessuna trama disponibile.'); ?></p>
                        <div class="header-actions">
                            <p class="likes" data-likes-label><?php echo (int)$movie['likes']; ?> likes</p>
                            <button type="button" class="movie-link like-btn" data-movie-id="<?php echo (int)$movie['id']; ?>">+ Like</button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="login-card">
                <h2>Lascia un commento</h2>
                <div id="comment-status" class="subtitle" aria-live="polite"></div>
                <form id="comment-form" class="login-form">
                    <input type="hidden" name="movie_id" value="<?php echo (int)$movie['id']; ?>">
                    <label for="text">Commento</label>
                    <textarea id="text" name="text" rows="4" placeholder="Scrivi qui il tuo commento..." required></textarea>
                    <button type="submit">Pubblica commento</button>
                </form>
            </section>

            <section class="login-card">
                <h2>Commenti</h2>
                <label for="comments-sort">Ordina commenti</label>
                <select id="comments-sort" class="search-input sort-input">
                    <option value="newest">Più recenti</option>
                    <option value="oldest">Più vecchi</option>
                </select>
                <div id="comments-list" class="comments-list" data-movie-id="<?php echo (int)$movie['id']; ?>"></div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
