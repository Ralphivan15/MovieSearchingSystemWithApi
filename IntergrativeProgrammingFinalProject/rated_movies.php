<?php
$host       = 'localhost';
$dbname     = 'movie_db';
$username   = 'root';
$password   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$filter = $_GET['filter'] ?? 'most_rated';
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit  = 5;
$offset = ($page - 1) * $limit;

$orderBy = ($filter === 'highest_rated') ? 'avg_rating DESC' : 'total_ratings DESC';

$sql = "
    SELECT m.title, m.poster_url, AVG(r.rating_value) AS avg_rating, COUNT(r.rating_value) AS total_ratings
    FROM ratings r
    JOIN movies m ON r.movie_id = m.movie_id
    GROUP BY r.movie_id
    ORDER BY $orderBy
    LIMIT $limit OFFSET $offset
";

$stmt   = $pdo->query($sql);
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalStmt  = $pdo->query("SELECT COUNT(DISTINCT movie_id) AS total FROM ratings");
$totalRows  = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);
?>
<html>
<head>
    <link rel="stylesheet" href="css/rated.css">
</head>    
<body>
    <h1>Rated Movies</h1>

    <?php foreach ($movies as $movie): ?>
        <div class="movie-card">
            <img src="<?= $movie['poster_url'] ?>">
<span><?= htmlspecialchars($movie['title']) ?></span>
<span>Avg Rating: <?= round($movie['avg_rating'], 1) ?> ⭐</span>
<span>Total Ratings: <?= $movie['total_ratings'] ?></span>

        </div>
    <?php endforeach; ?>

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?filter=<?= $filter ?>&page=<?= $i ?>">Page <?= $i ?></a>
        <?php endfor; ?>
    </div>

    <a href="index.html" class="back-button">Back to Search</a>
</body>
</html>