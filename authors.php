<?php
require_once 'php_func/configure.php';
session_start();

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);

    // Get the optional name filter from GET parameters.
    $nameFilter = "";
    if (isset($_GET['name']) && !empty(trim($_GET['name']))) {
        $nameFilter = trim($_GET['name']);
    }

    // Build the base query.
    $sql = "SELECT u.id, u.fullName, u.profileImage, COUNT(b.id) AS blogCount
            FROM users u
            JOIN blogs b ON u.id = b.author_id ";
    if ($nameFilter !== "") {
        $sql .= "WHERE u.fullName LIKE :name ";
    }
    $sql .= "GROUP BY u.id
             ORDER BY blogCount DESC";

    $stmt = $pdo->prepare($sql);
    if ($nameFilter !== "") {
        $stmt->bindValue(':name', '%' . $nameFilter . '%', PDO::PARAM_STR);
    }
    $stmt->execute();
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
  <!-- Use profile.css if that is what your normally formatted page uses -->
  <link rel="stylesheet" href="css/profile.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    /* this is done so that we can no have the header pushed out of the page */
    .main-wrapper {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    .breadcrumb {
      font-size: 1em;
      margin-bottom: 10px;
    }
    .button-container {
      margin-bottom: 20px;
    }
    /* Filter panel styling */
    .filter-panel {
      display: none;
      padding: 10px;
      background: #f4f4f4;
      margin: 10px 0;
    }
    /* Author box styling (similar to your reference page) */
    .authorBox {
      margin-bottom: 20px;
      overflow: auto;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
    }
    .authorBox img {
      max-width: 150px;
      float: left;
      margin-right: 20px;
    }
    .authorBox ol {
      margin-top: 0;
    }
  </style>
</head>
<body>
  <div id="header"></div>

  <div class="main-wrapper">
    <div class="breadcrumb">
      <a href="index.php">Home</a> > Authors
    </div>

    <div class="button-container">
      <input type="button" id="filter-btn" value="Filter">
    </div>
      
    <div class="filter-panel">
      <h3>Filter Options</h3>
      <form method="GET" action="">
          <label for="name">Author Name:</label>
          <input type="text" id="name" name="name" placeholder="Enter author name" value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
          <br><br>
          <input type="submit" value="Apply Filters">
      </form>
    </div>

    <!-- Main content: Listing authors -->
    <main>
      <?php foreach ($authors as $author): ?>
        <div class="authorBox">
            <img src="<?php echo PROJ_PATH . htmlspecialchars($author['profileImage'] ? $author['profileImage'] : 'img/default.png'); ?>" alt="<?php echo htmlspecialchars($author['fullName']); ?>">
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
  </div>

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