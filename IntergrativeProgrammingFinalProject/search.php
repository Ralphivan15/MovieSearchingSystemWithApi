<?php
$host       = 'localhost';
$dbname     = 'movie_db';
$username   = 'root';
$password   = '';
$charset    = 'utf8';
$apiKey     = 'a2568983d4e0251c47d26e239708cb1a';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $query = urlencode(trim($_POST['query']));
    $searchUrl = "https://api.themoviedb.org/3/search/movie?api_key=$apiKey&query=$query";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $searchUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $searchResponse = curl_exec($ch);
    curl_close($ch);

    $searchData = json_decode($searchResponse, true);

    if (!empty($searchData['results'])) {
        $results = array_map(function($m) {
            return [
                'id'            => $m['id'],
                'title'         => $m['title'],
                'release_date'  => $m['release_date'] ?? '',
                'poster_url'    => "https://image.tmdb.org/t/p/w500" . ($m['poster_path'] ?? '')
            ];
        }, $searchData['results']);

        echo json_encode(['results' => $results]);
    } else {
        echo json_encode(['error' => 'No movies found.']);
    }
}
?>
