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
                        
                        echo '<div class="thread-card">';
                        echo '<h3><a href="thread.php?id=' . $thread['thread_id'] . '">' . htmlspecialchars($thread['title']) . '</a></h3>';
                        echo '<div class="thread-meta">';
                        echo '<span>Posted by ' . $username . '</span>';
                        echo '<span>' . date('F j, Y', strtotime($thread['created_at'])) . '</span>';
                        echo '</div>';
                        echo '<p>' . htmlspecialchars($excerpt) . '...</p>';
                        echo '<div class="thread-stats">';
                        echo '<div class="stat-item">';
                        echo '<span>' . $commentCount . ' comments</span>';
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
                            
                            echo '<div class="thread-card">';
                            echo '<h3><a href="thread.php?id=' . $thread['thread_id'] . '">' . htmlspecialchars($thread['title']) . '</a></h3>';
                            echo '<div class="thread-meta">';
                            echo '<span>Posted by ' . $username . '</span>';
                            echo '<span>' . date('F j, Y', strtotime($thread['created_at'])) . '</span>';
                            echo '</div>';
                            echo '<p>' . htmlspecialchars($excerpt) . '...</p>';
                            echo '<div class="thread-stats">';
                            echo '<div class="stat-item">';
                            echo '<span>' . $commentCount . ' comments</span>';
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
                
                if (count($chartData) > 0) {
                    foreach ($chartData as $day) {
                        $labels[] = date('M d', strtotime($day['date']));
                        $values[] = intval($day['count']);
                    }
                } else {
                    // Fallback chart data if no data found
                    $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    $values = [0, 0, 0, 0, 0, 0, 0];
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

<style>
.hot-threads-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
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
</style>

<?php include 'includes/footer.php'; ?> 