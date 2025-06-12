<?php
require_once 'database.php';

// Pesan status
$status = [
    'success' => '<div style="color: green; padding: 10px; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 4px; margin: 10px 0;">%s</div>',
    'error' => '<div style="color: red; padding: 10px; background: #ffebee; border: 1px solid #ffcdd2; border-radius: 4px; margin: 10px 0;">%s</div>',
    'info' => '<div style="color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0;">%s</div>'
];

// Fungsi untuk menampilkan pesan
function tampilkanPesan($pesan, $tipe = 'info') {
    global $status;
    printf($status[$tipe], $pesan);
}

// Header halaman
echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database CineFind</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        h1 {
            color: #e50914;
            text-align: center;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .step h2 {
            margin-top: 0;
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #e50914;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #f40612;
        }
    </style>
</head>
<body>
    <h1>Setup Database CineFind</h1>';

try {
    // Baca file SQL
    $sql = file_get_contents(__DIR__ . '/cinefind.sql');
    if (!$sql) {
        throw new Exception("File SQL tidak ditemukan!");
    }

    // Tampilkan langkah-langkah setup
    echo '<div class="step">
        <h2>Langkah-langkah Setup Database:</h2>
        <ol>
            <li>Membuat tabel roles</li>
            <li>Menambahkan peran default (admin dan user)</li>
            <li>Membuat tabel users</li>
            <li>Membuat tabel pengguna</li>
            <li>Membuat tabel watchlist</li>
            <li>Membuat tabel reviews</li>
            <li>Membuat tabel ratings</li>
            <li>Membuat indeks untuk performa</li>
        </ol>
    </div>';

    // Pisahkan query SQL
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    // Eksekusi setiap query
    $step = 1;
    foreach ($queries as $query) {
        if (!empty($query)) {
            if (!$conn->query($query)) {
                throw new Exception("Error pada langkah $step: " . $conn->error);
            }
            tampilkanPesan("Langkah $step berhasil dijalankan", 'success');
            $step++;
        }
    }
    
    tampilkanPesan("Setup database berhasil diselesaikan! Semua tabel telah dibuat dengan benar.", 'success');
    
    // Tampilkan tombol untuk kembali ke halaman utama
    echo '<div style="text-align: center; margin-top: 20px;">
        <a href="../pengguna/index.php" class="btn">Kembali ke Halaman Utama</a>
    </div>';

} catch (Exception $e) {
    tampilkanPesan("Error saat setup database: " . $e->getMessage(), 'error');
    
    // Tampilkan tombol untuk mencoba lagi
    echo '<div style="text-align: center; margin-top: 20px;">
        <a href="setup_database.php" class="btn">Coba Lagi</a>
    </div>';
}

$conn->close();

echo '</body></html>';
?> 