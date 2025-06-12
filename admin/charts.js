// User Growth Chart
function updateUserGrowthChart() {
    const ctx = document.getElementById('userGrowthChart').getContext('2d');
    
    fetch('get_user_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (userGrowthChartInstance) {
                    userGrowthChartInstance.destroy(); // Destroy existing chart if any
                }
                userGrowthChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Total Pengguna',
                            data: data.values,
                            borderColor: '#3498db', // Blue color
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Pertumbuhan Pengguna (6 Bulan Terakhir)'
                            },
                            legend: {
                                display: true,
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: Math.max(1, Math.ceil(Math.max(...data.values) / 10)) // Dynamic step size
                                }
                            }
                        }
                    }
                });
            } else {
                console.error('Error fetching user stats:', data.message);
                // Optionally display an error message on the chart canvas
            }
        })
        .catch(error => {
            console.error('Fetch error for user stats:', error);
        });
}

// Genre Distribution Chart
function updateGenreDistributionChart() {
    const ctx = document.getElementById('genreDistributionChart').getContext('2d');
    
    fetch('get_genre_stats.php') // Fetch from your new PHP script
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.labels.length > 0) {
                 if (genreDistributionChartInstance) {
                    genreDistributionChartInstance.destroy();
                }
                genreDistributionChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.values,
                            backgroundColor: [ // Add more colors if you have many genres
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                                '#FF9F40', '#8AC24A', '#FF5722', '#00BCD4', '#E91E63',
                                '#607D8B', '#795548' 
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Distribusi Genre Film (Lokal)'
                            },
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            } else if (data.labels.length === 0) {
                console.log('No genre data to display.');
                 if (genreDistributionChartInstance) {
                    genreDistributionChartInstance.destroy();
                }
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.textAlign = 'center';
                ctx.fillText('No genre data available', ctx.canvas.width / 2, ctx.canvas.height / 2);
            } else {
                console.error('Error fetching genre stats:', data.message);
            }
        })
        .catch(error => {
             console.error('Fetch error for genre stats:', error);
        });
}

// Keep global instances for charts to destroy them before re-initializing
let userGrowthChartInstance = null;
let genreDistributionChartInstance = null;

// Initialize charts when page loads (or when dashboard is shown)
// This will be called from admin.js when the dashboard section is active.
// document.addEventListener('DOMContentLoaded', function() {
//     updateUserGrowthChart();
//     updateGenreDistributionChart();
// });