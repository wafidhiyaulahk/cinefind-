<?php
// TMDB API Configuration
define('TMDB_API_KEY', 'ba6f7d3b063751fb2bea48683e263f63');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE_URL', 'https://image.tmdb.org/t/p');

// Function to get total movie count
function getTotalMovieCount() {
    $url = TMDB_BASE_URL . "/movie/popular?api_key=" . TMDB_API_KEY . "&language=en-US&page=1";
    $response = file_get_contents($url);
    
    if ($response === false) {
        return [
            'status' => 'error',
            'message' => 'Failed to fetch movie count'
        ];
    }
    
    $data = json_decode($response, true);
    return [
        'status' => 'success',
        'total_results' => $data['total_results'] ?? 0,
        'total_pages' => $data['total_pages'] ?? 0
    ];
}

// Function to fetch movies from TMDB
function fetchTMDBMovies($endpoint = '/movie/popular', $params = []) {
    $defaultParams = [
        'api_key' => TMDB_API_KEY,
        'language' => 'en-US',
        'page' => 1
    ];
    
    $params = array_merge($defaultParams, $params);
    $queryString = http_build_query($params);
    $url = TMDB_BASE_URL . $endpoint . '?' . $queryString;
    
    $response = file_get_contents($url);
    if ($response === false) {
        return [
            'status' => 'error',
            'message' => 'Failed to fetch data from TMDB'
        ];
    }
    
    return json_decode($response, true);
}

// Function to get movie list based on type
function getMovieList($type = 'popular', $page = 1) {
    $endpoints = [
        'popular' => '/movie/popular',
        'top_rated' => '/movie/top_rated',
        'upcoming' => '/movie/upcoming',
        'now_playing' => '/movie/now_playing'
    ];
    
    $endpoint = $endpoints[$type] ?? $endpoints['popular'];
    return fetchTMDBMovies($endpoint, ['page' => $page]);
}

// Function to get movie details
function getMovieDetails($movieId) {
    $url = TMDB_BASE_URL . "/movie/{$movieId}?api_key=" . TMDB_API_KEY . "&language=en-US";
    $response = file_get_contents($url);
    
    if ($response === false) {
        return [
            'status' => 'error',
            'message' => 'Failed to fetch movie details'
        ];
    }
    
    return json_decode($response, true);
}

// Function to search movies
function searchMovies($query) {
    $params = [
        'query' => $query,
        'include_adult' => false
    ];
    
    return fetchTMDBMovies('/search/movie', $params);
}

// Function to get movie genres
function getMovieGenres() {
    $url = TMDB_BASE_URL . "/genre/movie/list?api_key=" . TMDB_API_KEY . "&language=en-US";
    $response = file_get_contents($url);
    
    if ($response === false) {
        return [
            'status' => 'error',
            'message' => 'Failed to fetch genres'
        ];
    }
    
    return json_decode($response, true);
}

// Function to format movie data for display
function formatMovieData($movie) {
    return [
        'id' => $movie['id'],
        'title' => $movie['title'],
        'poster_path' => $movie['poster_path'] ? TMDB_IMAGE_BASE_URL . '/w500' . $movie['poster_path'] : '../assets/images/default-poster.png',
        'genre' => isset($movie['genre_ids']) ? getGenreNames($movie['genre_ids']) : 'N/A',
        'rating' => number_format($movie['vote_average'], 1),
        'release_date' => $movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A',
        'runtime' => isset($movie['runtime']) ? $movie['runtime'] : 'N/A'
    ];
}

// Helper function to get genre names from IDs
function getGenreNames($genreIds) {
    $genres = getMovieGenres();
    if (!isset($genres['genres'])) {
        return 'N/A';
    }
    
    $genreNames = [];
    foreach ($genres['genres'] as $genre) {
        if (in_array($genre['id'], $genreIds)) {
            $genreNames[] = $genre['name'];
        }
    }
    
    return implode(', ', $genreNames);
}

// Function to get genre distribution
function getGenreDistribution($type = 'popular') {
    // Get all genres first
    $genres = getMovieGenres();
    if (!isset($genres['genres'])) {
        return [
            'status' => 'error',
            'message' => 'Failed to fetch genres'
        ];
    }

    // Initialize genre counts
    $genreCounts = array_fill_keys(array_column($genres['genres'], 'id'), 0);
    $genreNames = array_column($genres['genres'], 'name', 'id');

    // Get total pages (TMDB has 500 pages max)
    $totalPages = 500;
    $totalMovies = 1016252;
    $moviesPerPage = ceil($totalMovies / $totalPages);
    
    // Calculate how many pages we need to sample to get a representative distribution
    $samplePages = min(50, $totalPages); // Take up to 50 pages for better performance
    $sampleSize = $samplePages * $moviesPerPage;
    
    // Get random starting page
    $startPage = rand(1, $totalPages - $samplePages);
    
    // Initialize array to store all movies
    $allMovies = [];
    
    // Fetch movies from sample pages
    for ($page = $startPage; $page < $startPage + $samplePages; $page++) {
        $result = getMovieList($type, $page);
        if (isset($result['results'])) {
            $allMovies = array_merge($allMovies, $result['results']);
        }
    }

    // Count movies per genre
    foreach ($allMovies as $movie) {
        if (isset($movie['genre_ids'])) {
            foreach ($movie['genre_ids'] as $genreId) {
                if (isset($genreCounts[$genreId])) {
                    $genreCounts[$genreId]++;
                }
            }
        }
    }

    // Scale the counts to match total movies
    $scaleFactor = $totalMovies / count($allMovies);
    foreach ($genreCounts as $genreId => $count) {
        $genreCounts[$genreId] = round($count * $scaleFactor);
    }

    // Format the data for chart
    $chartData = [];
    foreach ($genreCounts as $genreId => $count) {
        if ($count > 0) {
            $chartData[] = [
                'genre' => $genreNames[$genreId],
                'count' => $count
            ];
        }
    }

    // Sort by count in descending order
    usort($chartData, function($a, $b) {
        return $b['count'] - $a['count'];
    });

    return [
        'status' => 'success',
        'data' => $chartData,
        'total_movies' => $totalMovies,
        'sample_size' => count($allMovies),
        'scaled' => true
    ];
}
?> 