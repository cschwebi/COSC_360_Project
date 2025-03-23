<!DOCTYPE html>
<?php
require_once 'php_func/configure.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    // Fetch 5 most recent blogs with title, content preview, and image
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
    <style>
        h2 a {
            color: #2e2f59;
            text-decoration: none;
        }
        .blog-image img {
            max-width: 200px;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
        }
        .blog {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .blog-text {
            flex: 1;
        }
        .blog-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .blog-title {
            color: #2e2f59;
            text-decoration: none;
            margin: 0 0 10px 0;
        }
        .blog-title:hover {
            color:#11122f;
            text-decoration: underline;
        }
    </style>
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
                <h2><a href="trending.html">Trending Topics</a></h2>
                <ol>
                    <li><a href="trending.html?category=finance">Finance</a></li>
                    <li><a href="trending.html?category=health">Health</a></li>
                    <li><a href="trending.html?category=technology">Technology</a></li>
                    <li><a href="trending.html?category=travel">Travel</a></li>
                    <li><a href="trending.html?category=productivity">Productivity</a></li>
                    <li><a href="trending.html?category=food">Food</a></li>
                    <li><a href="trending.html?category=business">Business</a></li>
                    <li><a href="trending.html?category=parenting">Parenting</a></li>
                    <li><a href="trending.html?category=education">Education</a></li>
                    <li><a href="trending.html?category=entertainment">Entertainment</a></li>
                    <li><a href="trending.html?category=other">Other</a></li>
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
