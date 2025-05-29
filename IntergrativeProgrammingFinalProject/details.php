<?php
$host       = 'localhost';
$dbname     = 'movie_db';
$username   = 'root';
$password   = '';
$apiKey     = 'a2568983d4e0251c47d26e239708cb1a';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
    $movieId = $_POST['movie_id'];

    // Get full details from TMDb
    $detailsUrl = "https://api.themoviedb.org/3/movie/$movieId?api_key=$apiKey";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $detailsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $detailsResponse = curl_exec($ch);
    curl_close($ch);
    $movieDetails = json_decode($detailsResponse, true);

    // Update or insert search count
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ?");
    $stmt->execute([$movieId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $pdo->prepare("UPDATE movies SET search_count = search_count + 1 WHERE movie_id = ?")->execute([$movieId]);
    } else {
        $insert = $pdo->prepare("INSERT INTO movies (movie_id, title, search_count, poster_url) VALUES (?, ?, 1, ?)");
        $insert->execute([
            $movieId,
            $movieDetails['title'],
            "https://image.tmdb.org/t/p/w500" . $movieDetails['poster_path']
        ]);
    }

    // Get average rating
    $ratingStmt = $pdo->prepare("SELECT AVG(rating_value) as avg_rating FROM ratings WHERE movie_id = ?");
    $ratingStmt->execute([$movieId]);
    $avgRatingRow = $ratingStmt->fetch();
    $avgRating  = $avgRatingRow['avg_rating'] ? round($avgRatingRow['avg_rating'], 1) : "No ratings yet";

    echo json_encode([
        'title'             => $movieDetails['title'],
        'release_date'      => $movieDetails['release_date'],
        'poster_url'        => "https://image.tmdb.org/t/p/w500" . $movieDetails['poster_path'],
        'overview'          => $movieDetails['overview'],
        'rating'            => $movieDetails['vote_average'],
        'avg_user_rating'   => $avgRating,
        'genres'            => array_map(fn($g) => $g['name'], $movieDetails['genres']),
        'movie_id'          => $movieId
    ]);
}
?>
