<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo - Pacha Tour</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .metric-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .metric-card h3 {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .metric-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }
        
        .metric-card .change {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .positive { color: #10b981; }
        .negative { color: #ef4444; }
        
        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .chart-container {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .chart-container h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .recent-activity {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-item h4 {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .activity-item p {
            font-size: 0.8rem;
            color: #666;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .nav-links a {
            padding: 0.5rem 1rem;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 4px;
            border: 1px solid #667eea;
            transition: all 0.3s;
        }
        
        .nav-links a:hover {
            background: #667eea;
            color: white;
        }
        
        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏔️ Dashboard Administrativo - Pacha Tour</h1>
    </div>
    
    <div class="container">
        <div class="nav-links">
            <a href="/admin/reports/bookings">📊 Reportes de Reservas</a>
            <a href="/admin/reports/revenue">💰 Reportes de Ingresos</a>
            <a href="/admin/reports/users">👥 Reportes de Usuarios</a>
            <a href="/admin/reports/attractions">🏛️ Reportes de Atractivos</a>
            <a href="/admin/overview">⚙️ Estado del Sistema</a>
        </div>
        
        <div class="metrics-grid">
            <div class="metric-card">
                <h3>Total Usuarios</h3>
                <div class="value">{{ number_format($metrics['total_users']) }}</div>
                <div class="change positive">+{{ $metrics['new_users_this_month'] }} este mes</div>
            </div>
            
            <div class="metric-card">
                <h3>Atractivos Activos</h3>
                <div class="value">{{ number_format($metrics['total_attractions']) }}</div>
            </div>
            
            <div class="metric-card">
                <h3>Reservas Totales</h3>
                <div class="value">{{ number_format($metrics['total_bookings']) }}</div>
                <div class="change positive">+{{ $metrics['bookings_this_month'] }} este mes</div>
            </div>
            
            <div class="metric-card">
                <h3>Ingresos Totales</h3>
                <div class="value">Bs. {{ number_format($metrics['total_revenue'], 2) }}</div>
                <div class="change positive">Bs. {{ number_format($metrics['revenue_this_month'], 2) }} este mes</div>
            </div>
            
            <div class="metric-card">
                <h3>Reservas Confirmadas</h3>
                <div class="value">{{ number_format($metrics['confirmed_bookings']) }}</div>
            </div>
            
            <div class="metric-card">
                <h3>Reservas Pendientes</h3>
                <div class="value">{{ number_format($metrics['pending_bookings']) }}</div>
            </div>
            
            <div class="metric-card">
                <h3>Comisiones Totales</h3>
                <div class="value">Bs. {{ number_format($metrics['total_commissions'], 2) }}</div>
                <div class="change positive">Bs. {{ number_format($metrics['commissions_this_month'], 2) }} este mes</div>
            </div>
            
            <div class="metric-card">
                <h3>Valoración Promedio</h3>
                <div class="value">{{ number_format($metrics['average_rating'], 1) }}/5</div>
                <div class="change">{{ $metrics['pending_reviews'] }} reseñas pendientes</div>
            </div>
        </div>
        
        <div class="charts-section">
            <div class="chart-container">
                <h3>Reservas por Estado</h3>
                <canvas id="bookingsStatusChart" width="400" height="200"></canvas>
            </div>
            
            <div class="chart-container">
                <h3>Ingresos Mensuales (Últimos 6 meses)</h3>
                <canvas id="monthlyRevenueChart" width="400" height="200"></canvas>
            </div>
        </div>
        
        <div class="recent-activity">
            <h3>Actividad Reciente</h3>
            
            <h4 style="margin-top: 1.5rem; margin-bottom: 1rem; color: #667eea;">Reservas Recientes</h4>
            @foreach($recentActivity['recent_bookings'] as $booking)
            <div class="activity-item">
                <h4>Reserva #{{ $booking->id }} - {{ $booking->user->name }}</h4>
                <p>{{ $booking->tourSchedule->tour->name ?? 'Tour eliminado' }} - {{ $booking->status }} - Bs. {{ number_format($booking->total_amount, 2) }}</p>
                <p>{{ $booking->created_at->diffForHumans() }}</p>
            </div>
            @endforeach
            
            <h4 style="margin-top: 1.5rem; margin-bottom: 1rem; color: #667eea;">Usuarios Nuevos</h4>
            @foreach($recentActivity['recent_users'] as $user)
            <div class="activity-item">
                <h4>{{ $user->name }} ({{ $user->role }})</h4>
                <p>{{ $user->email }} - Registrado {{ $user->created_at->diffForHumans() }}</p>
            </div>
            @endforeach
            
            <h4 style="margin-top: 1.5rem; margin-bottom: 1rem; color: #667eea;">Reseñas Pendientes</h4>
            @foreach($recentActivity['recent_reviews'] as $review)
            <div class="activity-item">
                <h4>{{ $review->user->name }} - {{ $review->rating }}/5 estrellas</h4>
                <p>{{ $review->reviewable->name ?? 'Item eliminado' }} - {{ substr($review->comment ?? '', 0, 100) }}{{ strlen($review->comment ?? '') > 100 ? '...' : '' }}</p>
                <p>{{ $review->created_at->diffForHumans() }}</p>
            </div>
            @endforeach
        </div>
    </div>
    
    <script>
        // Bookings Status Chart
        const statusCtx = document.getElementById('bookingsStatusChart').getContext('2d');
        const statusData = @json($chartData['bookings_by_status']);
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#6366f1'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Monthly Revenue Chart
        const revenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
        const revenueData = @json($chartData['monthly_revenue']);
        
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`),
                datasets: [{
                    label: 'Ingresos (Bs.)',
                    data: revenueData.map(item => item.revenue),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
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
                            callback: function(value) {
                                return 'Bs. ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>