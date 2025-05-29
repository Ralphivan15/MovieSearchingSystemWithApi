<?php
$host       = 'localhost';
$dbname     = 'movie_db';
$username   = 'root';
$password   = '';
$apiKey     = 'a2568983d4e0251c47d26e239708cb1a';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'], $_POST['rating'])) {
    $movieId = $_POST['movie_id'];
    $rating  = $_POST['rating'];

    if (!in_array($rating, ['1','2','3','4','5'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid rating value.']);
        exit;
    }

    // Insert movie only if not exists
    $stmt = $pdo->prepare("SELECT 1 FROM movies WHERE movie_id = ?");
    $stmt->execute([$movieId]);
    if (!$stmt->fetch()) {
        $response   = file_get_contents("https://api.themoviedb.org/3/movie/$movieId?api_key=$apiKey");
        $movie      = json_decode($response, true);
        $title      = $movie['title'];
        $poster     = "https://image.tmdb.org/t/p/w500" . $movie['poster_path'];
        $insert     = $pdo->prepare("INSERT INTO movies (movie_id, title, search_count, poster_url) VALUES (?, ?, 1, ?)");
        $insert->execute([$movieId, $title, $poster]);
    }

    $stmt = $pdo->prepare("INSERT INTO ratings (movie_id, rating_value) VALUES (?, ?)");
    $stmt->execute([$movieId, $rating]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
?>