<?php
require_once 'php_func/configure.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['user_id'];

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);

    // Fetch user's posts
    $stmtPosts = $pdo->prepare("SELECT * FROM blogs WHERE author_id = :user_id ORDER BY created_at DESC");
    $stmtPosts->execute([':user_id' => $userId]);
    $posts = $stmtPosts->fetchAll();

    // Fetch user's comments along with the title of the blog they commented on
    $stmtComments = $pdo->prepare("SELECT c.*, b.title as blog_title FROM comments c JOIN blogs b ON c.blog_id = b.id WHERE c.user_id = :user_id ORDER BY c.created_at DESC");
    $stmtComments->execute([':user_id' => $userId]);
    $comments = $stmtComments->fetchAll();

    // Fetch posts that the user liked (favorites)
    $stmtLikes = $pdo->prepare("SELECT b.* FROM favorites f JOIN blogs b ON f.blog_id = b.id WHERE f.user_id = :user_id ORDER BY f.created_at DESC");
    $stmtLikes->execute([':user_id' => $userId]);
    $likes = $stmtLikes->fetchAll();
} catch (PDOException $e) {
    die("<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CC Blogs</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/profile.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .profile-info img {
            margin-right: 20px;
            max-width: 200px;
            height: auto;
        }
        .post, .comment, .liked-post {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .post .blog-image img, .liked-post .blog-image img {
            max-width: 100%;
            height: auto;
        }
        .tab-content {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div id="header"></div>

    <div class="profile-container">
        <div class="breadcrumb">
            <a href="index.php">Home</a> > Profile
        </div>

        <div class="profile-info">
            <?php
            // Display profile image if set; otherwise a default placeholder.
            if (isset($_SESSION['profileImage']) && !empty($_SESSION['profileImage'])) {
                echo '<img src="' . '../COSC_360_Project' . $_SESSION['profileImage'] . '"  style="width: 200px; height: 150px;" alt="Profile Photo">';
            } else {
                echo '<div class="profile-photo">Profile Photo</div>';
            }
            ?>
            <div class="profile-details">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['displayName']); ?></p>
                <p><strong>Bio:</strong> <?php echo htmlspecialchars($_SESSION['bio']); ?></p>
            </div>
        </div>

        <div class="button-container">
            <!-- Link to an edit profile page where user can update profile picture, bio and name -->
            <a href="settings.php"><button>Edit Profile</button></a>
            <a href="createPost.php"><button>Create Post</button></a>
        </div>

        <!-- Tabs for Posts, Comments, and Likes -->
        <div class="tabs">
            <button class="tab-link active" data-tab="posts">Posts</button>
            <button class="tab-link" data-tab="comments">Comments</button>
            <button class="tab-link" data-tab="likes">Likes</button>
        </div>

        <!-- Posts Tab -->
        <div class="tab-content" id="posts">
            <?php if ($posts): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <article class="blog">
                            <div class="blog-text">
                                <h3><a href="blogpage.php?id=<?php echo htmlspecialchars($post['id']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                                <p>
                                    <?php 
                                        // Display a preview of the post content (first 150 characters)
                                        echo htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) . '...';
                                    ?>
                                </p>
                            </div>
                            <div class="blog-image">
                                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Cover image for <?php echo htmlspecialchars($post['title']); ?>">
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You haven't posted any blogs yet.</p>
            <?php endif; ?>
        </div>

        <!-- Comments Tab -->
        <div class="tab-content" id="comments" style="display:none;">
            <?php if ($comments): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <p><strong>On <a href="blogpage.php?id=<?php echo htmlspecialchars($comment['blog_id']); ?>"><?php echo htmlspecialchars($comment['blog_title']); ?></a>:</strong></p>
                        <p><?php echo htmlspecialchars($comment['content']); ?></p>
                        <p class="comment-date"><?php echo date('F j, Y g:i A', strtotime($comment['created_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You haven't made any comments yet.</p>
            <?php endif; ?>
        </div>

        <!-- Likes Tab -->
        <div class="tab-content" id="likes" style="display:none;">
            <?php if ($likes): ?>
                <?php foreach ($likes as $like): ?>
                    <div class="liked-post">
                        <article class="blog">
                            <div class="blog-text">
                                <h3><a href="blogpage.php?id=<?php echo htmlspecialchars($like['id']); ?>"><?php echo htmlspecialchars($like['title']); ?></a></h3>
                                <p>
                                    <?php 
                                        // Display a preview of the liked blog content
                                        echo htmlspecialchars(substr(strip_tags($like['content']), 0, 150)) . '...';
                                    ?>
                                </p>
                            </div>
                            <div class="blog-image">
                                <img src="<?php echo htmlspecialchars($like['image']); ?>" alt="Cover image for <?php echo htmlspecialchars($like['title']); ?>">
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You haven't liked any posts yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#header").load("header.php");

            $(".tab-link").click(function() {
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