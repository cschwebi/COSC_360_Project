<?php
require_once 'php_func/configure.php';
session_start();

// Ensure an author ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $authorId = intval($_GET['id']);
} else {
    header("Location: authors.php");
    exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);

    // Fetch the author's details from the users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $authorId]);
    $author = $stmt->fetch();

    if (!$author) {
        echo "Author not found.";
        exit;
    }

    // Get author statistics: total posts and total likes
    $stmtStats = $pdo->prepare("
        SELECT COUNT(*) AS postCount, IFNULL(SUM(like_count), 0) AS totalLikes
        FROM blogs WHERE author_id = :authorId
    ");
    $stmtStats->execute([':authorId' => $authorId]);
    $stats = $stmtStats->fetch();

    // Retrieve the posts by this author (order by newest first)
    $stmtPosts = $pdo->prepare("SELECT * FROM blogs WHERE author_id = :authorId ORDER BY created_at DESC");
    $stmtPosts->execute([':authorId' => $authorId]);
    $posts = $stmtPosts->fetchAll();

} catch (PDOException $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Author Profile - <?php echo htmlspecialchars($author['fullName']); ?></title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/profile.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .profile-info img {
      margin-right: 20px;
      max-width: 150px;
    }
    .blog-post {
      margin-bottom: 20px;
    }
    .blog-post .image img {
      max-width: 100%;
      height: auto;
    }
  </style>
</head>
<body>
  <div id="header"></div>
  <div class="profile-container">
    <div class="breadcrumb">
      <a href="index.php">Home</a> > <a href="authors.php">Authors</a> > <?php echo htmlspecialchars($author['fullName']); ?>
    </div>

    <div class="profile-info">
      <img src="<?php echo htmlspecialchars($author['profileImage'] ? $author['profileImage'] : 'img/default.png'); ?>" alt="<?php echo htmlspecialchars($author['fullName']); ?>">
      <div class="profile-details">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($author['fullName']); ?></p>
        <p><strong>Bio:</strong> <?php echo htmlspecialchars($author['bio']); ?></p>
        <p><strong>Total Likes:</strong> <?php echo htmlspecialchars($stats['totalLikes']); ?></p>
        <p><strong>Total Posts:</strong> <?php echo htmlspecialchars($stats['postCount']); ?></p>
      </div>
    </div>

    <div class="tabs">
      <button class="tab-link active" data-tab="posts">Posts</button>
      <!-- You can add other tabs (comments, likes) if needed -->
    </div>

    <div class="tab-content" id="posts">
      <?php if ($posts): ?>
        <?php foreach ($posts as $post): ?>
          <div class="blog-post">
            <article class="blog">
              <div class="text">
                <h2>
                  <a href="blogpage.php?id=<?php echo htmlspecialchars($post['id']); ?>">
                    <?php echo htmlspecialchars($post['title']); ?>
                  </a>
                </h2>
                <p>
                  <?php 
                    // Show a preview of the post content (first 150 characters)
                    echo htmlspecialchars(substr(strip_tags($post['content']), 0, 150)) . '...';
                  ?>
                </p>
              </div>
              <div class="image">
                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Cover image for <?php echo htmlspecialchars($post['title']); ?>"  style="max-width: 150px; height: auto;">
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No posts available for this author.</p>
      <?php endif; ?>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $("#header").load("header.php");
      // Optionally, add tab functionality if you add more tabs in the future
    });
  </script>
</body>
</html>