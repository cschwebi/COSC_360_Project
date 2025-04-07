<?php
require_once 'php_func/configure.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
$userId = $_SESSION['user_id'];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);

    // Retrieve favourite blogs for the logged-in user
    $stmtBlogs = $pdo->prepare("
        SELECT b.*, u.fullName AS author, u.id AS author_id 
        FROM favorites f
        JOIN blogs b ON f.blog_id = b.id
        JOIN users u ON b.author_id = u.id
        WHERE f.user_id = :user_id
        ORDER BY f.created_at DESC
    ");
    $stmtBlogs->execute([':user_id' => $userId]);
    $favBlogs = $stmtBlogs->fetchAll();

    //retrieve distinct authors from the favourites
    $stmtAuthors = $pdo->prepare("
        SELECT DISTINCT u.id, u.fullName, u.profileImage
        FROM favorites f
        JOIN blogs b ON f.blog_id = b.id
        JOIN users u ON b.author_id = u.id
        WHERE f.user_id = :user_id
        ORDER BY u.fullName ASC
    ");
    $stmtAuthors->execute([':user_id' => $userId]);
    $favAuthors = $stmtAuthors->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favourites - CC Blogs</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/profile.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /*Extra styling for tab content if needed */
        .tab-content {
            margin-top: 20px;
        }
        .authorBox, .liked-post {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            overflow: auto;
        }
        .authorBox img, .liked-post .blog-image img {
            max-width: 200px;
            height: auto;
            margin-right: 20px;
            float: left;
        }
        .authorBox h2, .liked-post h3 {
            margin: 0;
        }
    </style>
</head>
<body>
    <div id="header"></div>

    <div class="profile-container">
        <div class="breadcrumb">
            <a href="index.php">Home</a> > Favourites
        </div>

        <!-- Favourites Tabs-->
        <div class="tabs">
            <button class="tab-link active" data-tab="fav-authors">Authors</button>
            <button class="tab-link" data-tab="fav-blogs">Blogs</button>
        </div>

        <!-- Authors Tab Content -->
        <div class="tab-content" id="fav-authors">
            <?php if ($favAuthors): ?>
                <?php foreach ($favAuthors as $author): ?>
                    <div class="authorBox">
                        <img src="<?php echo htmlspecialchars($author['profileImage'] ? $author['profileImage'] : 'img/default.png'); ?>" alt="<?php echo htmlspecialchars($author['fullName']); ?>">
                        <h2>
                            <a href="authorname.php?id=<?php echo htmlspecialchars($author['id']); ?>">
                                <?php echo htmlspecialchars($author['fullName']); ?>
                            </a>
                        </h2>
                        <?php
                        // retrieve the top 3 most recent posts for this autho
                        try {
                            $stmtPosts = $pdo->prepare("SELECT id, title FROM blogs WHERE author_id = :author_id ORDER BY created_at DESC LIMIT 3");
                            $stmtPosts->execute([':author_id' => $author['id']]);
                            $posts = $stmtPosts->fetchAll();
                            if ($posts) {
                                echo '<ol>';
                                foreach ($posts as $post) {
                                    echo '<li><a href="blogpage.php?id=' . htmlspecialchars($post['id']) . '">' . htmlspecialchars($post['title']) . '</a></li>';
                                }
                                echo '</ol>';
                            }
                        } catch (PDOException $e) {
                            echo '<p>Error loading posts</p>';
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No favourite authors found.</p>
            <?php endif; ?>
        </div>

        <!-- blogs Tab Content -->
        <div class="tab-content" id="fav-blogs" style="display:none;">
            <?php if ($favBlogs): ?>
                <?php foreach ($favBlogs as $blog): ?>
                    <div class="liked-post">
                        <div class="blog-image">
                            <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="Cover image for <?php echo htmlspecialchars($blog['title']); ?>">
                        </div>
                        <div class="blog-text">
                            <h3><a href="blogpage.php?id=<?php echo htmlspecialchars($blog['id']); ?>"><?php echo htmlspecialchars($blog['title']); ?></a></h3>
                            <p><?php echo htmlspecialchars(substr(strip_tags($blog['content']), 0, 150)) . '...'; ?></p>
                            <p>By <a href="authorname.php?id=<?php echo htmlspecialchars($blog['author_id']); ?>"><?php echo htmlspecialchars($blog['author']); ?></a></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No favourite blogs found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $("#header").load("header.php");
            $(".tab-link").click(function () {
                var tab = $(this).data("tab");
                $(".tab-link").removeClass("active");
                $(this).addClass("active");
                $(".tab-content").hide();
                $("#" + tab).show();
            });
        });
    </script>
</body>
</html>