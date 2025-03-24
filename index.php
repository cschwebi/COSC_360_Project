<!DOCTYPE html>
<?php
require_once 'php_func/configure.php';

//Define categories array (matching search.php)
$categories = [
    'Finance', 'Health', 'Technology', 'Travel', 
    'Productivity', 'Food', 'Business', 'Parenting', 
    'Education', 'Entertainment', 'Other'
];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    //Fetch 5 most recent blogs with title, content preview, and image
    $sql = "SELECT b.id, b.title, b.content, b.image, b.created_at, b.topic, u.fullName as author 
            FROM blogs b 
            JOIN users u ON b.author_id = u.id 
            ORDER BY b.created_at DESC 
            LIMIT 5";
    $stmt = $pdo->query($sql);
    $blogs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database connection failed: " . $e->getMessage();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Blogs</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <!-- Include Header -->
    <div id="header-placeholder">
    </div>

    <main>
        <section class="content">
            <div class="blogs">
                <h2>Latest Blogs</h2>

                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php else: ?>
                    <?php foreach ($blogs as $blog): ?>
                        <article class="blog">
                            <div class="blog-text">
                                <h3>
                                    <a href="blogpage.php?id=<?php echo htmlspecialchars($blog['id']); ?>" class="blog-title">
                                        <?php echo htmlspecialchars($blog['title']); ?>
                                    </a>
                                </h3>
                                <div class="blog-meta">
                                    By <?php echo htmlspecialchars($blog['author']); ?> | 
                                    <?php echo date('F j, Y', strtotime($blog['created_at'])); ?> |
                                    <?php echo htmlspecialchars($blog['topic']); ?>
                                </div>
                                <p><?php 
                                    // Get first 100 words
                                    $words = str_word_count(strip_tags($blog['content']), 2);
                                    $first100 = implode(' ', array_slice(array_values($words), 0, 50));
                                    echo htmlspecialchars($first100) . '...' ;
                                ?></p>
                            </div>
                            <div class="blog-image">
                                <img src="<?php echo htmlspecialchars($blog['image']); ?>" 
                                     alt="Cover image for <?php echo htmlspecialchars($blog['title']); ?>">
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <aside class="trending-topics">
                <h2>Trending Topics</h2>
                <ol>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="search.php?category=<?php echo urlencode($cat); ?>">
                                <?php echo htmlspecialchars($cat); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </aside>
        </section>
    </main>
    <script>
        // Load header
        $(document).ready(function() {
            $("#header-placeholder").load("header.php");
        });
    </script>
</body>
</html>
