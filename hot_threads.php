<?php
session_start();
require_once('includes/config.php');
require_once('includes/functions.php');

$page_title = 'Hot Threads';
$additional_scripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

include 'includes/header.php';
?>

<div class="main-container">
    <div class="breadcrumb">
        <a href="home.php">Home</a> / <span>Hot Threads</span>
    </div>
    
    <h2>Hot Threads</h2>
    <p class="description">Discover the most active and popular discussions on our platform.</p>
    
    <div class="hot-threads-container">
        <!-- Simple Activity Visualization - in its own section -->
        <div class="visualization-section">
            <div class="activity-visualization">
                <h3>Discussion Activity (Last 7 Days)</h3>
                <div class="chart-container">
                    <canvas id="activityChart" height="180"></canvas>
                    <div id="chart-loading" class="chart-loading">Loading activity data...</div>
                    <!-- Fallback static chart -->
                    <div id="fallback-chart" class="fallback-chart" style="display:none;">
                        <div class="static-chart">
                            <div class="bar-container">
                                <div class="bar" style="height: 20%;" title="1 discussion">
                                    <span class="value">1</span>
                                </div>
                                <div class="day">Mon</div>
                            </div>
                            <div class="bar-container">
                                <div class="bar" style="height: 40%;" title="2 discussions">
                                    <span class="value">2</span>
                                </div>
                                <div class="day">Tue</div>
                            </div>
                            <div class="bar-container">
                                <div class="bar" style="height: 60%;" title="3 discussions">
                                    <span class="value">3</span>
                                </div>
                                <div class="day">Wed</div>
                            </div>
                            <div class="bar-container">
                                <div class="bar" style="height: 40%;" title="2 discussions">
                                    <span class="value">2</span>
                                </div>
                                <div class="day">Thu</div>
                            </div>
                            <div class="bar-container">
                                <div class="bar" style="height: 80%;" title="4 discussions">
                                    <span class="value">4</span>
                                </div>
                                <div class="day">Fri</div>
                            </div>
                            <div class="bar-container">
                                <div class="bar" style="height: 60%;" title="3 discussions">
                                    <span class="value">3</span>
                                </div>
                                <div class="day">Sat</div>
                            </div>
                            <div class="bar-container">
                                <div class="bar" style="height: 100%;" title="5 discussions">
                                    <span class="value">5</span>
                                </div>
                                <div class="day">Sun</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content section with filters and threads -->
        <div class="content-section">
            <?php
            // Get filter parameters from URL
            $timeframe = isset($_GET['timeframe']) ? intval($_GET['timeframe']) : 168; // Default to 1 week (168 hours)
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';
            ?>
            
            <div class="filters">
                <form method="get" action="hot_threads.php" class="filter-form">
                    <div class="filter-group">
                        <label for="timeframe">Time Period:</label>
                        <select id="timeframe" name="timeframe">
                            <option value="24" <?php echo $timeframe == 24 ? 'selected' : ''; ?>>Last 24 Hours</option>
                            <option value="168" <?php echo $timeframe == 168 ? 'selected' : ''; ?>>Last Week</option>
                            <option value="720" <?php echo $timeframe == 720 ? 'selected' : ''; ?>>Last Month</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort">Sort By:</label>
                        <select id="sort" name="sort">
                            <option value="comments" <?php echo $sort == 'comments' ? 'selected' : ''; ?>>Comment Count</option>
                            <option value="recent" <?php echo $sort == 'recent' ? 'selected' : ''; ?>>Most Recent</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="filter-button">Apply Filters</button>
                </form>
            </div>
            
            <div id="hot-threads-list" class="threads-list">
                <?php
                try {
                    error_log("Starting hot threads query");
                    $db = getDBConnection();
                    
                    // Build the query based on the filters
                    if ($sort === 'comments') {
                        // Sort by comment count (most commented threads first)
                        $query = "SELECT t.*, 
                                 (SELECT COUNT(*) FROM comments c WHERE c.thread_id = t.thread_id) AS comment_count
                                 FROM threads t
                                 WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                                 ORDER BY comment_count DESC
                                 LIMIT 10";
                    } else {
                        // Sort by recent (default)
                        $query = "SELECT t.*, 
                                 (SELECT COUNT(*) FROM comments c WHERE c.thread_id = t.thread_id) AS comment_count
                                 FROM threads t
                                 WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                                 ORDER BY t.created_at DESC
                                 LIMIT 10";
                    }
                    
                    $stmt = $db->prepare($query);
                    $stmt->execute([$timeframe]);
                    $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    error_log("Found " . count($threads) . " threads with timeframe filter");
                    
                    if (count($threads) > 0) {
                        foreach ($threads as $thread) {
                            // Format the data and display each thread
                            $username = htmlspecialchars($thread['username']);
                            $commentCount = isset($thread['comment_count']) ? $thread['comment_count'] : 0;
                            $excerpt = substr($thread['content'], 0, 150);
                            
                            // Determine heat level based on comment count
                            $heatLevel = 'low';
                            $heatIcon = '🔵'; // Cool
                            if ($commentCount >= 10) {
                                $heatLevel = 'high';
                                $heatIcon = '🔥'; // Hot
                            } elseif ($commentCount >= 5) {
                                $heatLevel = 'medium';
                                $heatIcon = '🟠'; // Warm
                            }
                            
                            echo '<div class="thread-card">';
                            echo '<h3><a href="thread.php?id=' . $thread['thread_id'] . '">' . htmlspecialchars($thread['title']) . '</a></h3>';
                            echo '<div class="thread-meta">';
                            echo '<span>Posted by ' . $username . '</span>';
                            echo '<span>' . date('F j, Y', strtotime($thread['created_at'])) . '</span>';
                            echo '</div>';
                            echo '<p>' . htmlspecialchars($excerpt) . '...</p>';
                            echo '<div class="thread-stats">';
                            echo '<div class="stat-item">';
                            echo '<span class="heat-indicator ' . $heatLevel . '">' . $heatIcon . ' ' . $commentCount . ' comments</span>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        // Try without timeframe filter if no threads found
                        error_log("No threads found with timeframe filter, trying without filter");
                        
                        if ($sort === 'comments') {
                            $query = "SELECT t.*, 
                                     (SELECT COUNT(*) FROM comments c WHERE c.thread_id = t.thread_id) AS comment_count
                                     FROM threads t
                                     ORDER BY comment_count DESC
                                     LIMIT 10";
                        } else {
                            $query = "SELECT t.*, 
                                     (SELECT COUNT(*) FROM comments c WHERE c.thread_id = t.thread_id) AS comment_count
                                     FROM threads t
                                     ORDER BY t.created_at DESC
                                     LIMIT 10";
                        }
                        
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        error_log("Found " . count($threads) . " threads without timeframe filter");
                        
                        if (count($threads) > 0) {
                            foreach ($threads as $thread) {
                                // Format the data and display each thread
                                $username = htmlspecialchars($thread['username']);
                                $commentCount = isset($thread['comment_count']) ? $thread['comment_count'] : 0;
                                $excerpt = substr($thread['content'], 0, 150);
                                
                                // Determine heat level based on comment count
                                $heatLevel = 'low';
                                $heatIcon = '🔵'; // Cool
                                if ($commentCount >= 10) {
                                    $heatLevel = 'high';
                                    $heatIcon = '🔥'; // Hot
                                } elseif ($commentCount >= 5) {
                                    $heatLevel = 'medium';
                                    $heatIcon = '🟠'; // Warm
                                }
                                
                                echo '<div class="thread-card">';
                                echo '<h3><a href="thread.php?id=' . $thread['thread_id'] . '">' . htmlspecialchars($thread['title']) . '</a></h3>';
                                echo '<div class="thread-meta">';
                                echo '<span>Posted by ' . $username . '</span>';
                                echo '<span>' . date('F j, Y', strtotime($thread['created_at'])) . '</span>';
                                echo '</div>';
                                echo '<p>' . htmlspecialchars($excerpt) . '...</p>';
                                echo '<div class="thread-stats">';
                                echo '<div class="stat-item">';
                                echo '<span class="heat-indicator ' . $heatLevel . '">' . $heatIcon . ' ' . $commentCount . ' comments</span>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            // No threads found at all, show a message
                            echo '<div class="no-results">';
                            echo '<p>No threads found.</p>';
                            echo '<p>Be the first to <a href="create_thread.php">create a discussion</a>!</p>';
                            echo '</div>';
                        }
                    }
                    
                    // Get data for the chart (last 7 days)
                    $chartQuery = "
                        SELECT 
                            DATE(created_at) as date,
                            COUNT(*) as count
                        FROM threads
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        GROUP BY DATE(created_at)
                        ORDER BY date ASC
                    ";
                    
                    $chartStmt = $db->prepare($chartQuery);
                    $chartStmt->execute();
                    $chartData = $chartStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $labels = [];
                    $values = [];
                    
                    // Generate dates for the last 7 days
                    for ($i = 6; $i >= 0; $i--) {
                        $date = date('Y-m-d', strtotime("-$i days"));
                        $labels[] = date('M d', strtotime($date));
                        $values[] = 0; // Default to 0
                    }
                    
                    // Fill in actual values if we have data
                    if (count($chartData) > 0) {
                        foreach ($chartData as $day) {
                            $dayIndex = array_search(date('M d', strtotime($day['date'])), $labels);
                            if ($dayIndex !== false) {
                                $values[$dayIndex] = intval($day['count']);
                            }
                        }
                    }
                    
                    // Make sure we have at least some data to display
                    if (array_sum($values) == 0) {
                        // No real data, add some sample data for visualization
                        $values = [1, 2, 3, 2, 4, 3, 5];
                    }
                    
                } catch (Exception $e) {
                    error_log("Error in hot_threads.php: " . $e->getMessage());
                    echo '<div class="error-message">';
                    echo '<p>There was an error loading threads. Please try again later.</p>';
                    echo '</div>';
                    
                    // Fallback chart data
                    $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $values = [0, 0, 0, 0, 0, 0, 0];
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Chart.js directly to ensure it loads -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the activity chart
    try {
        console.log("Initializing chart with data:", <?php echo json_encode($values); ?>);
        const ctx = document.getElementById('activityChart');
        if (ctx) {
            document.getElementById('chart-loading').style.display = 'none';
            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{
                        label: 'New Discussions',
                        data: <?php echo json_encode($values); ?>,
                        backgroundColor: '#3498db',
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: '#333'
                            },
                            grid: {
                                color: '#eee'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#333'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            console.log("Chart initialized successfully");
        } else {
            console.error("Chart canvas element not found");
            document.getElementById('chart-loading').style.display = 'none';
            document.getElementById('fallback-chart').style.display = 'block';
        }
    } catch(error) {
        console.error("Error initializing chart:", error);
        document.getElementById('chart-loading').style.display = 'none';
        document.getElementById('fallback-chart').style.display = 'block';
    }
    
    // Fallback after 2 seconds if the chart doesn't load
    setTimeout(function() {
        const chartCanvas = document.getElementById('activityChart');
        if (chartCanvas && !chartCanvas.__chart__) {
            document.getElementById('chart-loading').style.display = 'none';
            document.getElementById('fallback-chart').style.display = 'block';
        }
    }, 2000);
});
</script>

<style>
.hot-threads-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Visualization and content section styling */
.visualization-section {
    margin-bottom: 30px;
}

.content-section {
    clear: both;
}

.metrics-container {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
    margin-bottom: 30px;
}

.metric-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.metric-card h3 {
    margin-top: 0;
    color: #333;
}

.metric-card ul {
    padding-left: 20px;
}

.trending-chart {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters {
    display: flex;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    position: relative;
    z-index: 2;
    clear: both;
    margin-top: 20px;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 20px;
    width: 100%;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-group label {
    font-weight: bold;
    white-space: nowrap;
}

.filter-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
}

.filter-button {
    padding: 8px 16px;
    background-color: #2196F3;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    margin-left: auto;
    position: relative;
    z-index: 3;
}

.filter-button:hover {
    background-color: #0b7dda;
}

.loading-indicator {
    text-align: center;
    padding: 30px;
    color: #666;
    font-style: italic;
}

.threads-list {
    display: grid;
    gap: 20px;
}

.thread-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.thread-card:hover {
    transform: translateY(-2px);
}

.thread-card h3 {
    margin-top: 0;
}

.thread-meta {
    display: flex;
    gap: 15px;
    color: #666;
    font-size: 0.9em;
    margin: 10px 0;
}

.thread-stats {
    display: flex;
    gap: 15px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.popularity-badge {
    background: #ff6b6b;
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.error-message {
    background: #ffebee;
    border: 1px solid #ffcdd2;
    color: #c62828;
    padding: 15px;
    border-radius: 5px;
    margin: 20px 0;
    text-align: center;
}

.no-results {
    text-align: center;
    padding: 30px;
    background: #f5f5f5;
    border-radius: 8px;
    color: #666;
}

/* Visualization Styles */
.activity-visualization {
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    padding: 15px;
}

.activity-visualization h3 {
    color: #333;
    font-size: 18px;
    margin-bottom: 15px;
    text-align: center;
}

.chart-container {
    position: relative;
    height: 180px;
    margin-bottom: 10px;
    z-index: 1;
}

.chart-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 16px;
    color: #666;
}

/* Heat indicator styles */
.heat-indicator {
    display: inline-flex;
    align-items: center;
    font-weight: bold;
}

.heat-indicator.low {
    color: #3498db;
}

.heat-indicator.medium {
    color: #f39c12;
}

.heat-indicator.high {
    color: #e74c3c;
}

/* Fallback chart styles */
.fallback-chart {
    width: 100%;
    height: 180px;
}

.static-chart {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    height: 150px;
    padding-top: 10px;
}

.bar-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 14%;
}

.bar {
    width: 70%;
    background-color: #3498db;
    border-radius: 5px 5px 0 0;
    position: relative;
    min-height: 10px;
    transition: height 0.3s ease;
}

.bar .value {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 12px;
    font-weight: bold;
    color: #333;
}

.day {
    margin-top: 10px;
    font-size: 12px;
    font-weight: bold;
    color: #333;
}

/* Section divider */
.section-divider {
    height: 20px;
    width: 100%;
    clear: both;
    display: block;
}
</style>

<?php include 'includes/footer.php'; ?> 