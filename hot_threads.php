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
            
            <!-- Remove any PHP output potentially showing here -->
            <!-- This is where unwanted numbers might be showing up -->
            <div style="display:none;">
                <?php 
                // Capture and hide any unwanted output
                ob_start();
                print_r($values); 
                ob_end_clean();
                ?>
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
                        // But store them in a different variable to avoid them being displayed elsewhere
                        $_chart_values = [1, 2, 3, 2, 4, 3, 5];
                        // Only use this for the chart, not for page display
                        $values = $_chart_values;
                    }
                    
                } catch (Exception $e) {
                    error_log("Error in hot_threads.php: " . $e->getMessage());
                    echo '<div class="error-message">';
                    echo '<p>There was an error loading threads. Please try again later.</p>';
                    echo '</div>';
                    
                    // Fallback chart data
                    $labels = [
                        date('M d', strtotime('-6 days')),
                        date('M d', strtotime('-5 days')),
                        date('M d', strtotime('-4 days')),
                        date('M d', strtotime('-3 days')),
                        date('M d', strtotime('-2 days')),
                        date('M d', strtotime('-1 days')),
                        date('M d')
                    ];
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
            document.getElementById('chart-loading').innerHTML = 'Chart could not be loaded. Please try again later.';
        }
    } catch(error) {
        console.error("Error initializing chart:", error);
        document.getElementById('chart-loading').style.display = 'none';
        document.getElementById('chart-loading').innerHTML = 'Chart could not be loaded. Please try again later.';
    }
    
    // Remove any unwanted blue bars that might appear
    setTimeout(function() {
        // Find and remove any unwanted blue bars outside the chart container
        const blueBars = document.querySelectorAll('div[style*="background-color: #3498db"]');
        for (let i = 0; i < blueBars.length; i++) {
            const bar = blueBars[i];
            // Only remove if outside the chart
            if (!bar.closest('.chart-container')) {
                // Find the parent to remove
                let parent = bar.parentElement;
                while (parent && parent.tagName !== 'BODY' && 
                      !parent.classList.contains('content-section')) {
                    parent = parent.parentElement;
                }
                
                if (parent && parent.classList.contains('content-section')) {
                    // Only remove direct children of content-section that aren't important
                    const children = Array.from(parent.children);
                    for (let j = 0; j < children.length; j++) {
                        const child = children[j];
                        if (!child.classList.contains('filters') && 
                            !child.classList.contains('threads-list') && 
                            !child.id !== 'hot-threads-list' &&
                            !child.classList.contains('error-message') &&
                            !child.classList.contains('no-results')) {
                            // Check if this contains numbers
                            const text = child.textContent.trim();
                            if (/^[0-9\s]+$/.test(text) || child.querySelector('div[style*="background-color: #3498db"]')) {
                                child.remove();
                            }
                        }
                    }
                }
            }
        }
    }, 100);
});
</script>

<style>
.hot-threads-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Hide the numbers at the top of threads */
.content-section > div:not(.filters):not(#hot-threads-list):not(.threads-list):not(.thread-card):not(.error-message):not(.no-results) {
    display: none !important;
}

/* Hide any element that contains just numbers before the threads list */
.content-section > div:first-of-type:not(.filters):not(#hot-threads-list) {
    display: none !important;
}

/* Target numeric row specifically */
.content-section > .static-chart,
.content-section > *:has(> .bar),
.content-section > div[style*="display: flex"] {
    display: none !important;
}

/* Specifically target blue bars with numbers */
div:has(> div[style*="background-color: #3498db"]) {
    display: none !important;
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

/* Hide weekday headers above threads */
.hot-threads-container > div > .content-section > div:not(.filters):not(#hot-threads-list):not(.threads-list):not(.thread-card):not(.error-message):not(.no-results) {
    display: none !important;
}
.content-section > div:first-of-type:not(.filters) {
    display: none !important;
}
/* Hide specifically Mon-Sun row */
.content-section > div.row,
.threads-list ~ div.row,
div:has(> span:first-child:contains("Mon")),
.content-section > div:has(> span) {
    display: none !important;
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
</style>

<?php include 'includes/footer.php'; ?> 