<?php
// history.php

$host       = 'localhost';
$dbname     = 'movie_db';
$username   = 'root';
$password   = '';
$charset    = 'utf8';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch top 10 searched movies
$stmt   = $pdo->query("SELECT title, search_count, poster_url FROM movies ORDER BY search_count DESC LIMIT 5");
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search History</title>
    <link rel="stylesheet" href="css/history.css">
</head>
<body>
    <h1>Top 5 Most Searched Movies </h1>

    <?php if (count($movies) === 0): ?>
        <p style="text-align:center;">No search history found.</p>
    <?php else: ?>
        <div class="movie-container">
            <?php foreach ($movies as $index => $movie): ?>
                <?php
                    $rank = $index + 1;
                    $rankClass = match($rank) {
                        1 => 'rank-1',
                        2 => 'rank-2',
                        3 => 'rank-3',
                        default => 'rank-default'
                    };
                ?>
                <div class="movie-item">
                    <div class="rank-badge <?= $rankClass ?>">Top <?= $rank ?></div>
                    <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="Poster">
                    <div class="movie-info">
                        <h3><?= htmlspecialchars($movie['title']) ?></h3>
                        <p><?= htmlspecialchars($movie['search_count']) ?> searches</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="index.html" class="back-button">Back to Search</a>
</body>
</html>
