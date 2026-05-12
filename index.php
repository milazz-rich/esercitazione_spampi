<?php
require_once __DIR__ . '/services/app.service.php';

if (!checkAuth()) {
    header('Location: pages/login.php?next=' . rawurlencode('../index.php'));
    exit;
}

$movies = [];
$error = null;
$isAuthenticated = true;

if (!function_exists('mysqli_connect')) {
    $error = "Estensione mysqli non attiva nel PHP in uso.";
} else {
    $conn = mysqli_connect($dbconfig['host'], $dbconfig['user'], $dbconfig['password'], $dbconfig['name']);

    if (!$conn) {
        $error = "Connessione al database non riuscita.";
    } else {
        $query = "SELECT id, title, plot, poster, likes FROM movies ORDER BY id ASC";
        $res = mysqli_query($conn, $query);

        if (!$res) {
            $error = "Errore nel recupero dei film dal database.";
        } else {
            while ($row = mysqli_fetch_assoc($res)) {
                $movies[] = $row;
            }
            mysqli_free_result($res);
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
    <title>MovieHub - Catalogo Film</title>
    <link rel="stylesheet" href="styles/style.css">
    <script src="scripts/index.js" defer></script>
</head>
<body class="movies-page">
    <main class="movies-shell">
        <section class="top-nav" aria-labelledby="movies-title">
            <div>
                <p class="eyebrow">MovieHub</p>
                <h1 id="movies-title">Film disponibili</h1>
            </div>
            <div class="header-actions">
                <span class="pill">Ciao, <?php echo htmlspecialchars(currentUsername()); ?></span>
                <button type="button" id="theme-toggle" class="pill alt-action">Dark mode</button>
                <a class="pill" href="pages/logout.php">Logout</a>
            </div>
        </section>

        <section class="login-card search-card">
            <label class="search-label" for="movie-search">Cerca film</label>
            <input id="movie-search" class="search-input" type="search" placeholder="Titolo o trama..." autocomplete="off">
        </section>

        <?php if (!empty($error)): ?>
            <section class="login-card">
                <div class="alert" role="alert"><?php echo htmlspecialchars($error); ?></div>
            </section>
        <?php elseif (empty($movies)): ?>
            <section class="login-card">
                <p class="subtitle">Nessun film trovato nella tabella <strong>movies</strong>.</p>
            </section>
        <?php else: ?>
            <section class="movies-grid" aria-label="Lista dei film">
                <?php foreach ($movies as $movie): ?>
                    <?php $posterPath = resolvePosterPath($movie['poster'] ?? '', false); ?>
                    <article class="movie-card login-card" data-title="<?php echo htmlspecialchars(strtolower($movie['title'])); ?>" data-plot="<?php echo htmlspecialchars(strtolower($movie['plot'] ?? '')); ?>">
                        <img class="movie-thumb" src="<?php echo htmlspecialchars($posterPath); ?>" alt="Locandina di <?php echo htmlspecialchars($movie['title']); ?>">
                        <div class="movie-card-top">
                            <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                            <span class="likes" data-likes-label><?php echo (int)$movie['likes']; ?> likes</span>
                        </div>
                        <p class="movie-plot"><?php echo htmlspecialchars($movie['plot'] ?? 'Nessuna trama disponibile.'); ?></p>
                        <a class="movie-link detail-link" href="pages/movie.php?id=<?php echo (int)$movie['id']; ?>">Apri dettaglio</a>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
