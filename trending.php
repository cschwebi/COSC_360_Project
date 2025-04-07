<?php
require_once 'php_func/configure.php';
session_start();

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    // Get date from 1 month ago
    $oneMonthAgo = date('Y-m-d H:i:s', strtotime('-1 month'));
    
    // Get date from 10 days ago
    $tenDaysAgo = date('Y-m-d H:i:s', strtotime('-10 days'));
    
    // Fetch posts per day for the last 10 days
    $stmtDailyPosts = $pdo->prepare("
        SELECT DATE(created_at) as post_date, COUNT(*) as post_count
        FROM blogs
        WHERE created_at >= :ten_days_ago
        GROUP BY DATE(created_at)
        ORDER BY post_date ASC
    ");
    $stmtDailyPosts->execute([':ten_days_ago' => $tenDaysAgo]);
    $dailyPosts = $stmtDailyPosts->fetchAll();
    
    // Prepare data for the chart
    $dates = [];
    $counts = [];
    
    // Initialize all days with zero counts
    for ($i = 9; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('M j', strtotime($date));
        $counts[] = 0;
    }
    
    // Fill in the actual counts
    foreach ($dailyPosts as $post) {
        $postDate = date('Y-m-d', strtotime($post['post_date']));
        $daysAgo = floor((time() - strtotime($postDate)) / (60 * 60 * 24));
        
        if ($daysAgo >= 0 && $daysAgo < 10) {
            $counts[9 - $daysAgo] = $post['post_count'];
        }
    }
    
    // Convert PHP arrays to JSON for JavaScript
    $datesJson = json_encode($dates);
    $countsJson = json_encode($counts);
    
    // Fetch top 5 most liked posts from last month
    // Modified query to use the like_count field from blogs table
    $stmtTopPosts = $pdo->prepare("
        SELECT b.*, u.fullName as author
        FROM blogs b
        JOIN users u ON b.author_id = u.id
        WHERE b.created_at >= :one_month_ago
        ORDER BY b.like_count DESC
        LIMIT 5
    ");
    $stmtTopPosts->execute([':one_month_ago' => $oneMonthAgo]);
    $topPosts = $stmtTopPosts->fetchAll();
    
    // Fetch top 5 most active topics from last month
    $stmtTopTopics = $pdo->prepare("
        SELECT topic, COUNT(*) as post_count
        FROM blogs
        WHERE created_at >= :one_month_ago
        GROUP BY topic
        ORDER BY post_count DESC
        LIMIT 10
    ");
    $stmtTopTopics->execute([':one_month_ago' => $oneMonthAgo]);
    $topTopics = $stmtTopTopics->fetchAll();
    
} catch (PDOException $e) {
    die("<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trending - CC Blogs</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/register.css">
    <link rel="stylesheet" href="css/trending.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div id="header"></div>

    <nav class="breadcrumb">
        <a href="index.php">Home</a> &gt; 
        <span>Trending</span>
    </nav>

    <div class="trending-container">
        <div class="chart-container">
            <div class="chart-title">Posts Per Day (Last 10 Days)</div>
            <canvas id="postsChart"></canvas>
        </div>

        <div class="trending-columns">
            <div class="trending-column">
                <div class="trending-section">
                    <h2>Top Posts This Month</h2>
                    <?php if ($topPosts): ?>
                        <?php foreach ($topPosts as $index => $post): ?>
                            <div class="post-card">
                                <div class="post-rank">#<?php echo $index + 1; ?></div>
                                <div class="post-content">
                                    <h3><a href="blogpage.php?id=<?php echo htmlspecialchars($post['id']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                    <div class="post-meta">
                                        By <a href="authorname.php?id=<?php echo htmlspecialchars($post['author_id']); ?>"><?php echo htmlspecialchars($post['author']); ?></a> | 
                                        <?php echo date('F j, Y', strtotime($post['created_at'])); ?> |
                                        <span class="like-count"><?php echo $post['like_count']; ?> likes</span>
                                    </div>
                                </div>
                                <div class="blog-image">
                                    <a href="blogpage.php?id=<?php echo htmlspecialchars($post['id']); ?>">
                                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Cover image for <?php echo htmlspecialchars($post['title']); ?>">
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No posts in the last month.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="trending-column">
                <div class="trending-section">
                    <h2>Popular Topics This Month</h2>
                    <?php if ($topTopics): ?>
                        <?php foreach ($topTopics as $index => $topic): ?>
                            <div class="topic-card">
                                <div class="topic-rank">#<?php echo $index + 1; ?></div>
                                <div class="topic-content">
                                    <div class="topic-name">
                                        <a href="search.php?category=<?php echo urlencode($topic['topic']); ?>">
                                            <?php echo htmlspecialchars($topic['topic']); ?>
                                        </a>
                                    </div>
                                    <div class="post-count"><?php echo $topic['post_count']; ?> posts</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No topics in the last month.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#header").load("header.php");
            
            // Create the chart
            const ctx = document.getElementById('postsChart').getContext('2d');
            const postsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo $datesJson; ?>,
                    datasets: [{
                        label: 'Number of Posts',
                        data: <?php echo $countsJson; ?>,
                        borderWidth: 2,
                        tension: 0.3,
                        pointBackgroundColor: 'rgba(46, 47, 89, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                stepSize: 1
                            },
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
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
        });
    </script>
</body>
</html> 