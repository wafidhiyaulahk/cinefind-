<?php
// admin_dashboard.php

// Memulai session dan memeriksa otentikasi admin
session_start();
require_once '../config/database.php'; // Menggunakan file konfigurasi database

// Cek jika pengguna sudah login dan merupakan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { //
    header('Location: ../login.php');
    exit();
}

// Koneksi ke database untuk mengambil data statistik awal
$conn = mysqli_connect('localhost', 'root', '', 'coda3424_cinefind'); //
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineFind Admin - Movie Recommendation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* CSS Gabungan dari admin.css dan style inline dari admin.php */
        /* --- Dari admin.css --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #fff;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center; /* Ditambahkan agar judul center */
        }
        
        .admin-panel-title {
            color: #ecf0f1;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-nav li {
            margin-bottom: 5px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0 25px 25px 0;
            margin-right: 10px;
        }

        .sidebar-nav a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav li.active a,
        .sidebar-nav a:hover {
            background-color: #34495e;
            color: #fff;
            transform: translateX(5px);
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e1e4e8;
        }

        .content-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            cursor: pointer;
        }
        
        .admin-dropdown .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            min-width: 180px;
            padding: 10px 0;
            margin-top: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .admin-profile:hover .dropdown-menu,
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #2c3e50;
            text-decoration: none;
        }
        
        .dropdown-menu a:hover {
            background-color: #f8f9fa;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }
        
        .stat-card:nth-child(1) .stat-icon { background-color: rgba(52, 152, 219, 0.1); color: #3498db; }
        .stat-card:nth-child(2) .stat-icon { background-color: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .stat-card:nth-child(3) .stat-icon { background-color: rgba(155, 89, 182, 0.1); color: #9b59b6; }
        .stat-card:nth-child(4) .stat-icon { background-color: rgba(230, 126, 34, 0.1); color: #e67e22; }

        .stat-info h3 { font-size: 0.9rem; color: #7f8c8d; margin-bottom: 5px; }
        .stat-number { font-size: 1.5rem; font-weight: 600; color: #2c3e50; }

        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .chart-header h2 { font-size: 1.2rem; font-weight: 600; color: #2c3e50; }
        .chart-body { height: 300px; }

        .table-section {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 { font-size: 1.3rem; font-weight: 600; color: #2c3e50; }
        
        .header-actions { display: flex; gap: 10px; }
        .add-btn, .refresh-btn { padding: 8px 15px; /* ... */ }
        .search-box { display: flex; }
        .search-box input { padding: 8px 15px; border: 1px solid #e1e4e8; border-radius: 5px; width: 250px; }
        .filter-options select { padding: 8px 15px; border: 1px solid #e1e4e8; border-radius: 5px; }
        
        .table-container { overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e1e4e8; }
        .data-table th { background-color: #f8f9fa; font-weight: 600; }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
            padding: 10px 0;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #e1e4e8;
            background-color: #fff;
            color: #2c3e50;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination-btn:hover:not(:disabled) {
            background-color: #f8f9fa;
            border-color: #2c3e50;
        }

        .pagination-btn.active {
            background-color: #2c3e50;
            color: #fff;
            border-color: #2c3e50;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-ellipsis {
            padding: 8px 12px;
            color: #7f8c8d;
        }

        .filter-options {
            padding: 8px 12px;
            border: 1px solid #e1e4e8;
            border-radius: 4px;
            background-color: #fff;
            color: #2c3e50;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-options:hover {
            border-color: #2c3e50;
        }

        .filter-options:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
        }

        .loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .status-badge.approved { background-color: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .status-badge.pending { background-color: rgba(241, 196, 15, 0.1); color: #f1c40f; }
        .status-badge.rejected { background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; }
        .status-badge.admin { background-color: rgba(52, 152, 219, 0.1); color: #3498db; }
        .status-badge.pengguna { background-color: rgba(149, 165, 166, 0.1); color: #95a5a6; }
        
        .action-btn { background: none; border: none; cursor: pointer; padding: 5px; margin: 0 2px; }
        .action-btn.view { color: #3498db; }
        .action-btn.edit { color: #f1c40f; }
        .action-btn.delete { color: #e74c3c; }
        
        .content-section { display: none; animation: fadeIn 0.3s ease; }
        .content-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .logout-container { display: flex; justify-content: center; align-items: center; min-height: 70vh; }
        .logout-card { background-color: #fff; padding: 40px; text-align: center; max-width: 500px; }
        .logout-icon { font-size: 4rem; color: #e74c3c; margin-bottom: 20px; }
        .logout-actions { display: flex; justify-content: center; gap: 15px; }
        .cancel-btn { background-color: #f8f9fa; color: #7f8c8d; }
        .confirm-btn { background-color: #e74c3c; color: white; text-decoration: none; }
        
        .text-center { text-align: center; }

        /* --- Dari style inline di admin.php --- */
        .data-table img { width: 50px; height: 75px; object-fit: cover; border-radius: 4px; }
        .rating-badge { background: #e50914; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.9rem; }
        .status-badge.released { background: #2ecc71; color: white; }
        .status-badge.upcoming { background: #f1c40f; color: white; }
        .status-badge.post-production { background: #3498db; color: white; }

        .chart-subtitle {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 class="admin-panel-title">Admin Cinefind</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#dashboard"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="#movies"><i class="fas fa-film"></i> <span>Daftar Film</span></a></li>
                    <li><a href="#reviews"><i class="fas fa-comments"></i> <span>Watchlist Pengguna</span></a></li>
                    <li><a href="#users"><i class="fas fa-users"></i> <span>Kelola Pengguna</span></a></li>
                    <li><a href="#logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="content-header">
                <h1 id="page-title">Dashboard</h1>
                <div class="admin-profile">
                    <div class="admin-info">
                        <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <span class="admin-role">Administrator</span>
                    </div>
                   
                </div>
            </header>

            <section id="dashboard-section" class="content-section active">
                <section class="stats-section">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <h3>Total Pengguna</h3>
                            <p class="stat-number" id="total-users-stat">
                                <?php $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengguna"); echo mysqli_fetch_assoc($result)['total']; ?>
                            </p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                        <div class="stat-info">
                            <h3>Member</h3>
                            <p class="stat-number" id="total-members-stat">
                                <?php $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengguna WHERE role_id != 1"); echo mysqli_fetch_assoc($result)['total']; ?>
                            </p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-film"></i></div>
                        <div class="stat-info">
                            <h3>Total Film</h3>
                            <p class="stat-number" id="total-films-stat">
                                <span class="loading-count">Loading...</span>
                            </p>
                        </div>
                    </div>
                    
                </section>
                <section class="charts-section">
                    <div class="chart-container">
                        <div class="chart-header"><h2>Pertumbuhan Pengguna</h2></div>
                        <div class="chart-body"><canvas id="userGrowthChart"></canvas></div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2>Distribusi Genre Film</h2>
                        
                        <div class="chart-body">
                            <canvas id="genreDistributionChart"></canvas>
                            <div class="chart-legend" id="genre-legend"></div>
                        </div>
                    </div>
                </section>
            </section>

            <section id="movies-section" class="content-section">
                <div class="table-section">
                    <div class="section-header">
                        <h2>Daftar Film</h2>
                        <div class="header-actions">
                            <select id="movieFilter" class="filter-options">
                                <option value="popular">Film Populer</option>
                                <option value="top_rated">Film Terbaik</option>
                                <option value="upcoming">Film Mendatang</option>
                                <option value="now_playing">Film yang Sedang Tayang</option>
                            </select>
                            <input type="text" id="movieSearch" class="search-box" placeholder="Cari film...">
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Poster</th>
                                    <th>Judul</th>
                                    <th>Genre</th>
                                    <th>Rating</th>
                                    <th>Tahun</th>
                                </tr>
                            </thead>
                            <tbody id="movies-management-table-body">
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination" id="movies-pagination">
                        <!-- Pagination will be added dynamically -->
                    </div>
                </div>
            </section>

            <section id="reviews-section" class="content-section">
                <div class="table-section">
                    <div class="section-header">
                        <h2>Watchlist Pengguna</h2>
                        <div class="header-actions">
                            <input type="text" id="watchlistSearch" class="search-box" placeholder="Cari film...">
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Judul Film</th>
                                    <th>Poster</th>
                                    <th>Tanggal Ditambahkan</th>
                                </tr>
                            </thead>
                            <tbody id="watchlist-table-body">
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="users-section" class="content-section">
                <div class="table-section">
                    <div class="section-header">
                        <h2>Kelola Pengguna</h2>
                        <div class="header-actions">
                            <input type="text" id="userSearch" class="search-box" placeholder="Cari pengguna...">
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Tanggal Bergabung</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body">
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="logout-section" class="content-section">
                <div class="logout-container">
                    <div class="logout-card">
                        <i class="fas fa-sign-out-alt logout-icon"></i>
                        <h2>Logout</h2>
                        <p>Apakah Anda yakin ingin keluar dari sistem?</p>
                        <div class="logout-actions">
                            <button class="cancel-btn">Batal</button>
                            <a href="../logout.php" class="confirm-btn">Konfirmasi Logout</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

<script>
// --- Script Gabungan dari admin.js & charts.js ---

// --- Bagian dari charts.js ---

// Variabel global untuk instance chart agar bisa dihancurkan sebelum digambar ulang
let userGrowthChartInstance = null;
let genreDistributionChartInstance = null;

function updateUserGrowthChart() {
    const ctx = document.getElementById('userGrowthChart')?.getContext('2d');
    if (!ctx) return;
    
    fetch('get_user_stats.php') //
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (userGrowthChartInstance) {
                    userGrowthChartInstance.destroy();
                }
                userGrowthChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Total Pengguna',
                            data: data.values,
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        return Math.round(value);
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return Math.round(context.raw);
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                console.error('Error fetching user stats:', data.message);
            }
        }).catch(error => console.error('Fetch error for user stats:', error));
}

async function updateGenreDistributionChart(type = 'popular') {
    const ctx = document.getElementById('genreDistributionChart')?.getContext('2d');
    if (!ctx) return;
    
    try {
        const response = await fetch(`get_genre_distribution.php?type=${type}`);
        const result = await response.json();
        
        if (result.status === 'success' && result.data.length > 0) {
            if (genreDistributionChartInstance) {
                genreDistributionChartInstance.destroy();
            }
            
            const labels = result.data.map(item => item.genre);
            const data = result.data.map(item => item.count);
            const total = data.reduce((a, b) => a + b, 0);
            
            // Update total movies count
            const totalElement = document.getElementById('genre-distribution-total');
            if (totalElement) {
                totalElement.textContent = `Total Film: ${result.total_movies.toLocaleString()}`;
            }
            
            genreDistributionChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                            '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                            '#9966FF', '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${value} film (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.error('Error fetching genre distribution:', result.message);
        }
    } catch (error) {
        console.error('Error updating genre distribution chart:', error);
    }
}


// --- Bagian dari admin.js ---

document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    const contentSections = document.querySelectorAll('.content-section');
    const pageTitle = document.getElementById('page-title');
    const adminProfile = document.querySelector('.admin-profile');
    const dropdownMenu = document.querySelector('.admin-dropdown .dropdown-menu');
    const logoutCancelBtn = document.querySelector('#logout-section .cancel-btn');
    const movieSearchInput = document.querySelector('#movieSearch');
    const userSearchInput = document.querySelector('#userSearch');
    const watchlistSearchInput = document.querySelector('#watchlistSearch');
    
    function showSection(sectionId) {
        contentSections.forEach(section => section.classList.remove('active'));
        const targetSection = document.getElementById(`${sectionId}-section`);
        if (targetSection) {
            targetSection.classList.add('active');
            const link = document.querySelector(`.sidebar-nav a[href="#${sectionId}"]`);
            pageTitle.textContent = link ? link.textContent.trim() : 'Dashboard';

            // Load data for the active section
            switch (sectionId) {
                case 'dashboard':
                    updateUserGrowthChart();
                    updateGenreDistributionChart();
                    break;
                case 'movies':
                    loadMoviesData();
                    break;
                case 'reviews':
                    loadWatchlistData();
                    break;
                case 'users':
                    loadUsersData();
                    break;
            }
        }
    }

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            sidebarLinks.forEach(item => item.parentElement.classList.remove('active'));
            this.parentElement.classList.add('active');
            showSection(targetId);
        });
    });

    if(adminProfile) {
        adminProfile.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
    }
    
    if(logoutCancelBtn) {
        logoutCancelBtn.addEventListener('click', () => showSection('dashboard'));
    }

    document.addEventListener('click', function() {
        if(dropdownMenu?.classList.contains('show')) {
            dropdownMenu.classList.remove('show');
        }
    });
    
    if(movieSearchInput) movieSearchInput.addEventListener('input', (e) => filterTable('movies-management-table-body', e.target.value));
    if(userSearchInput) userSearchInput.addEventListener('input', (e) => filterTable('users-table-body', e.target.value));
    if(watchlistSearchInput) watchlistSearchInput.addEventListener('input', (e => filterTable('watchlist-table-body', e.target.value)));

    // Initial load
    showSection('dashboard');
    document.querySelector('.sidebar-nav li a[href="#dashboard"]').parentElement.classList.add('active');
});

// Generic table filter function
function filterTable(tbodyId, searchTerm) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    const term = searchTerm.toLowerCase();
    const rows = tbody.querySelectorAll('tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

let currentPage = 1;
let totalPages = 1;
let currentFilter = 'popular';
let searchTimeout;

async function loadMoviesData(page = 1, filter = 'popular', searchQuery = '') {
    const tbody = document.getElementById('movies-management-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
    
    try {
        const params = new URLSearchParams({
            page: page,
            action: searchQuery ? 'search' : 'list',
            type: filter
        });
        
        if (searchQuery) {
            params.append('query', searchQuery);
        }
        
        const response = await fetch(`get_tmdb_movies.php?${params}`);
        const result = await response.json();
        
        tbody.innerHTML = '';
        
        if (result.status === 'success' && result.data.length > 0) {
            result.data.forEach(movie => {
                const row = `
                    <tr>
                        <td><img src="${movie.poster_path}" alt="${movie.title}" style="width: 50px; height: 75px; object-fit: cover; border-radius: 4px;"></td>
                        <td>${movie.title}</td>
                        <td>${movie.genre}</td>
                        <td><span class="rating-badge">${movie.rating}</span></td>
                        <td>${movie.release_date}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            
            // Update pagination
            currentPage = result.current_page;
            totalPages = result.total_pages;
            updatePagination();
            
            // Update total count if available
            if (result.total_results) {
                const countElement = document.getElementById('total-films-stat');
                if (countElement) {
                    countElement.innerHTML = `<b>${result.total_results.toLocaleString()}</b>`;
                }
            }
        } else {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center">${result.message || 'Tidak ada film ditemukan.'}</td></tr>`;
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Gagal memuat data film.</td></tr>';
        console.error("Error loading movies:", error);
    }
}

function updatePagination() {
    const pagination = document.getElementById('movies-pagination');
    if (!pagination) return;
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} 
                onclick="changePage(${currentPage - 1})">
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            paginationHTML += `
                <button class="pagination-btn ${i === currentPage ? 'active' : ''}" 
                        onclick="changePage(${i})">
                    ${i}
                </button>
            `;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            paginationHTML += '<span class="pagination-ellipsis">...</span>';
        }
    }
    
    // Next button
    paginationHTML += `
        <button class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} 
                onclick="changePage(${currentPage + 1})">
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    pagination.innerHTML = paginationHTML;
}

function changePage(page) {
    if (page < 1 || page > totalPages) return;
    loadMoviesData(page, currentFilter);
}

// Add event listeners for search and filter
document.getElementById('movieSearch').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadMoviesData(1, currentFilter, e.target.value);
    }, 500);
});

document.getElementById('movieFilter').addEventListener('change', (e) => {
    currentFilter = e.target.value;
    loadMoviesData(1, currentFilter);
    updateGenreDistributionChart(currentFilter);
});

// Add event listener for genre distribution filter
document.getElementById('genreDistributionFilter').addEventListener('change', (e) => {
    updateGenreDistributionChart(e.target.value);
});

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    loadMoviesData();
    updateMovieCount();
    updateGenreDistributionChart();
});

async function deleteMovie(movieId) {
    if (!confirm('Apakah Anda yakin ingin menghapus film ini?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('movie_id', movieId);

        const response = await fetch('delete_movie.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert('Film berhasil dihapus');
            loadMoviesData(); // Reload the table
        } else {
            alert('Gagal menghapus film: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting movie:', error);
        alert('Terjadi kesalahan saat menghapus film');
    }
}



async function loadWatchlistData() {
    const tbody = document.getElementById('watchlist-table-body');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
    try {
        const response = await fetch('get_watchlist.php');
        const result = await response.json();
        
        tbody.innerHTML = '';
        if (result.status === 'success') {
            if (result.data && result.data.length > 0) {
                result.data.forEach(watchlist => {
                    const row = `
                        <tr>
                            <td>${watchlist.id}</td>
                            <td>${watchlist.username || 'Unknown'}</td>
                            <td>${watchlist.movie_title}</td>
                            <td>
                                <img src="${watchlist.poster_path ? 'https://image.tmdb.org/t/p/w500' + watchlist.poster_path : '../assets/images/default-poster.png'}" 
                                     alt="${watchlist.movie_title}" 
                                     style="width: 50px; height: 75px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td>${watchlist.added_date}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center">Tidak ada data watchlist. (Total: ${result.count || 0})</td></tr>`;
            }
        } else {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center">Error: ${result.message || 'Tidak ada data watchlist.'}</td></tr>`;
        }
    } catch (error) {
        console.error("Error loading watchlist:", error);
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Gagal memuat data watchlist. Silakan coba lagi.</td></tr>';
    }
}

async function loadUsersData() {
    const tbody = document.getElementById('users-table-body');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
    try {
        const response = await fetch('get_users.php');
        const result = await response.json();
        tbody.innerHTML = '';
        if (result.status === 'success' && result.data.length > 0) {
            result.data.forEach(user => {
                const row = `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td><span class="status-badge ${user.role_text.toLowerCase()}">${user.role_text}</span></td>
                        <td>${user.join_date}</td>
                        <td>
                            <button class="action-btn delete" title="Hapus" onclick="deleteUser(${user.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center">${result.message || 'Tidak ada pengguna ditemukan.'}</td></tr>`;
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Gagal memuat data pengguna.</td></tr>';
        console.error("Error loading users:", error);
    }
}

async function deleteUser(userId) {
    if (!confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('user_id', userId);

        const response = await fetch('delete_user.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert('Pengguna berhasil dihapus');
            loadUsersData(); // Reload the table
            // Update user count in dashboard
            const totalUsersElement = document.getElementById('total-users-stat');
            const totalMembersElement = document.getElementById('total-members-stat');
            if (totalUsersElement) {
                const currentCount = parseInt(totalUsersElement.textContent);
                totalUsersElement.textContent = (currentCount - 1).toString();
            }
            if (totalMembersElement) {
                const currentCount = parseInt(totalMembersElement.textContent);
                totalMembersElement.textContent = (currentCount - 1).toString();
            }
        } else {
            alert('Gagal menghapus pengguna: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        alert('Terjadi kesalahan saat menghapus pengguna');
    }
}

// Function to update movie count
async function updateMovieCount() {
    const countElement = document.getElementById('total-films-stat');
    if (!countElement) return;
    
    try {
        const response = await fetch('get_movie_count.php');
        const result = await response.json();
        
        if (result.status === 'success') {
            countElement.innerHTML = `<b>${result.total_results.toLocaleString()}</b>`;
        } else {
            countElement.innerHTML = '<b>Error</b>';
        }
    } catch (error) {
        console.error('Error fetching movie count:', error);
        countElement.innerHTML = '<b>Error</b>';
    }
}
</script>
</body>
</html>