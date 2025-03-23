<?php
require_once 'php_func/configure.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    // Retrieve authors who have at least one blog and count how many blogs each has.
    // Authors with zero blogs will not be returned due to the INNER JOIN.
    $sql = "SELECT u.id, u.fullName, u.profileImage, COUNT(b.id) AS blogCount
            FROM users u
            JOIN blogs b ON u.id = b.author_id
            GROUP BY u.id
            ORDER BY blogCount DESC";
    $stmt = $pdo->query($sql);
    $authors = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Authors - CC Blogs</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/authors.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <div id="header"></div>

  <div class="breadcrumb-filter">
      <div class="breadcrumb"><a href="index.php">Home</a> > Authors</div>
      <input type="button" id="filter-btn" value="Filter">
  </div>
  
  <!-- Filter Panel (optional) -->
  <div class="filter-panel">
      <h3>Filter Options</h3>
      <label><input type="checkbox" name="category" value="option1"> Option 1</label><br>
      <label><input type="checkbox" name="category" value="option2"> Option 2</label><br>
      <label><input type="checkbox" name="category" value="option3"> Option 3</label><br>
      <input type="button" value="Apply Filters">
  </div>

  <main>
    <?php foreach ($authors as $author): ?>
      <div class="authorBox">
          <!-- Use the author's profile image if set; otherwise default -->
          <img src="<?php echo htmlspecialchars($author['profileImage'] ? $author['profileImage'] : 'img/default.png'); ?>" alt="<?php echo htmlspecialchars($author['fullName']); ?>">
          <ol type="1">
              <h2>
                <a href="authorname.php?id=<?php echo htmlspecialchars($author['id']); ?>">
                  <?php echo htmlspecialchars($author['fullName']); ?>
                </a>
              </h2>
              <?php
              // Retrieve the top 3 most recent posts for this author
              try {
                  $stmtPosts = $pdo->prepare("SELECT id, title FROM blogs WHERE author_id = :author_id ORDER BY created_at DESC LIMIT 3");
                  $stmtPosts->execute([':author_id' => $author['id']]);
                  $posts = $stmtPosts->fetchAll();
                  foreach ($posts as $post) {
                      echo '<li><a href="blogpage.php?id=' . htmlspecialchars($post['id']) . '">' . htmlspecialchars($post['title']) . '</a></li>';
                  }
              } catch (PDOException $e) {
                  echo '<li>Error loading posts</li>';
              }
              ?>
          </ol>
      </div>
    <?php endforeach; ?>
  </main>

  <script>
      $(document).ready(function() {
          $("#header").load("header.php");
          $('#filter-btn').click(function() {
              $('.filter-panel').slideToggle();
          });
      });
  </script>
</body>
</html>