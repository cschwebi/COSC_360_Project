<?php
require_once 'php_func/configure.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$blogId = intval($_GET['id']);

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    $sql = "SELECT b.*, u.fullName AS author, u.id AS author_id 
            FROM blogs b 
            JOIN users u ON b.author_id = u.id 
            WHERE b.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $blogId]);
    $blog = $stmt->fetch();

    if (!$blog) {
        echo "<script>alert('Blog post not found.'); window.location.href='index.php';</script>";
        exit;
    }
    
    $stmtComments = $pdo->prepare("SELECT c.*, u.fullName FROM comments c JOIN users u ON c.user_id = u.id WHERE c.blog_id = :blog_id ORDER BY c.created_at ASC");
    $stmtComments->execute([':blog_id' => $blogId]);
    $comments = $stmtComments->fetchAll();

    // Check if already favourited by current user
    $favorited = false;
    if (isset($_SESSION['user_id'])) {
        $stmtFavorite = $pdo->prepare("SELECT COUNT(*) AS count FROM favorites WHERE user_id = :user_id AND blog_id = :blog_id");
        $stmtFavorite->execute([
            ':user_id' => $_SESSION['user_id'],
            ':blog_id' => $blogId
        ]);
        $resultFavorite = $stmtFavorite->fetch();
        if ($resultFavorite['count'] > 0) {
            $favorited = true;
        }
    }
    
} catch (PDOException $e) {
    die("<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($blog['title']); ?> - CC Blogs</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/blogpage.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .blog-image img { max-width: 100%; height: auto; }
    .comments-container .comment { border-bottom: 1px solid #ccc; padding: 8px 0; }
    #commentForm textarea { width: 100%; height: 80px; box-sizing: border-box; padding: 8px; }
    #commentForm button { background: #2e2f59; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin-top: 5px; transition: background 0.3s ease; }
    #commentForm button:hover { background: #1a1a3c; }
    .button-container button { margin-right: 10px; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
  </style>
</head>
<body>
  <div id="header"></div>
  
  <main class="blog-container">
    <div class="breadcrumb-filter">
      <div class="breadcrumb">
        <a href="index.php">Home</a> > 
        <a href="authors.php">Authors</a> > 
        <a href="authorname.php?id=<?php echo htmlspecialchars($blog['author_id']); ?>">
          <?php echo htmlspecialchars($blog['author']); ?>
        </a> > 
        <?php echo htmlspecialchars($blog['title']); ?>
      </div>
    </div>

    <h1><?php echo htmlspecialchars($blog['title']); ?></h1>
    <div class="blog-meta">
      By <a href="authorname.php?id=<?php echo htmlspecialchars($blog['author_id']); ?>"><?php echo htmlspecialchars($blog['author']); ?></a> | 
      <?php echo date('F j, Y', strtotime($blog['created_at'])); ?> |
      <?php echo htmlspecialchars($blog['topic']); ?>
    </div>

    <div class="blog-image">
      <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="Cover image for <?php echo htmlspecialchars($blog['title']); ?>">
    </div>

    <div class="blog-content">
      <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
    </div>

    <div class="social-section">
      <div class="social-stats">
        <p>Likes: <span id="like-count"><?php echo htmlspecialchars($blog['like_count']); ?></span></p>
      </div>
      <div class="button-container">
        <?php
        // check if post is alrady liked by user
        $hasLiked = false;
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = :user_id AND blog_id = :blog_id");
            $stmt->execute([':user_id' => $_SESSION['user_id'], ':blog_id' => $blogId]);
            $hasLiked = $stmt->fetch() !== false;
        }
        ?>
        <button class="like-btn <?php echo $hasLiked ? 'liked' : ''; ?>">
            <?php echo $hasLiked ? 'Unlike' : 'Like'; ?>
        </button>
      </div>
    </div>

    <!-- Comments Section -->
    <section class="comments">
      <h2>Comments</h2>
      <div class="comments-container">
        <?php if ($comments): ?>
          <?php foreach ($comments as $comment): ?>
            <div class="comment">
              <p><strong><?php echo htmlspecialchars($comment['fullName']); ?>:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>
              <p class="comment-date"><?php echo date('F j, Y g:i A', strtotime($comment['created_at'])); ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No comments yet. Be the first to comment!</p>
        <?php endif; ?>
      </div>

      <form id="commentForm">
        <textarea id="commentContent" placeholder="Write your comment..."></textarea>
        <button type="submit">Submit Comment</button>
      </form>
    </section>
  </main>

  <script>
    $(document).ready(function() {
      $("#header").load("header.php");
    });
    
    var loggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    
    $('.like-btn').click(function(){
        if (!loggedIn) {
            window.location.href = 'login.html';
            return;
        }
        
        var $button = $(this);
        
        // Send AJAX request to toggle like
        $.ajax({
            url: 'php_func/like.php',
            type: 'POST',
            data: { blog_id: <?php echo htmlspecialchars($blog['id']); ?> },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Update like count
                    $('#like-count').text(response.like_count);
                    
                    // Update button text and class
                    if(response.action === 'liked') {
                        $button.text('Unlike').addClass('liked');
                    } else {
                        $button.text('Like').removeClass('liked');
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function() { alert('Error updating like.'); }
        });
    });
    
    $('.favorite-btn').click(function(){
        if (!loggedIn) { window.location.href = 'login.html'; return; }
        var $btn = $(this);
        $.ajax({
            url: 'php_func/favorite.php',
            type: 'POST',
            data: { blog_id: <?php echo htmlspecialchars($blog['id']); ?> },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    if(response.message === 'Added to favourites.') {
                        $btn.text('Favourited');
                    } else if(response.message === 'Removed from favourites.') {
                        $btn.text('Add to Favourites');
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function() { alert('Error updating favourites.'); }
        });
    });
    
    $('#commentForm').submit(function(e) {
        e.preventDefault();
        if (!loggedIn) { window.location.href = 'login.html'; return; }
        var commentContent = $('#commentContent').val().trim();
        if(commentContent === '') { alert('Please enter a comment.'); return; }
        $.ajax({
            url: 'php_func/add_comment.php',
            type: 'POST',
            data: { blog_id: <?php echo htmlspecialchars($blog['id']); ?>, content: commentContent },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('.comments-container').append(
                        '<div class="comment"><p><strong>' + response.user + ':</strong> ' + response.content + '</p><p class="comment-date">' + response.created_at + '</p></div>'
                    );
                    $('#commentContent').val('');
                } else {
                    alert(response.message);
                }
            },
            error: function() { alert('Error submitting comment.'); }
        });
    });
  </script>
</body>
</html>