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

    // Retrieve distinct authors from the favourites
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
        <title>Profile - CC Blogs</title>
        <link rel="stylesheet" href="css/styles.css">
        <link rel="stylesheet" href="css/favourites.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body>
        <div id="header"></div>

        <div class="breadcrumb-filter">
            <div class="breadcrumb"><a href="index.php">Home</a> > Favourites</div>
            <input type="button" id="filter-btn" value="Filter">
            <input type="button" id="fav-btn" value="Edit Favourites">
        </div>

        <div class="filter-panel">
          <h3>Filter Options</h3>
          <label>
            <input type="checkbox" name="filter" value="option1" /> Option 1
          </label>
          <br />
          <label>
            <input type="checkbox" name="filter" value="option2" /> Option 2
          </label>
          <br />
          <label>
            <input type="checkbox" name="filter" value="option3" /> Option 3
          </label>
          <br />
          <input type="button" value="Apply Filters" />
        </div>
    
        <div class="fav-panel">
          <h3>Edit Favourites</h3>
          <p>Favourite editing options go here.</p>
          <input type="button" value="Save Changes" />
        </div>

        <div class="tabs">
            <button class="tab-link active" data-tab="authors-tab">Authors</button>
            <button class="tab-link" data-tab="blogs-tab">Blogs</button>
            <div class="slider"></div>
        </div>
        
        <div class="tab-content" id="authors-tab">
            <main>
              <?php if($favAuthors): ?>
                <?php foreach($favAuthors as $author): ?>
                  <div class="authorBox">
                    <img src="<?php echo htmlspecialchars($author['profileImage'] ? $author['profileImage'] : 'img/default.png'); ?>" alt="<?php echo htmlspecialchars($author['fullName']); ?>">
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
            </main>
        </div>
        
        <div class="tab-content" id="blogs-tab" style="display: none;">
            <main>
              <?php if($favBlogs): ?>
                <?php foreach($favBlogs as $blog): ?>
                  <div class="authorBox">
                    <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="Cover image for <?php echo htmlspecialchars($blog['title']); ?>" style="max-width:150px; height:auto;">
                    <h2>
                      <a href="blogpage.php?id=<?php echo htmlspecialchars($blog['id']); ?>">
                        <?php echo htmlspecialchars($blog['title']); ?>
                      </a>
                    </h2>
                    <p><?php echo htmlspecialchars($blog['author']); ?></p>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>No favourite blogs found.</p>
              <?php endif; ?>
            </main>
        </div>
        
        <script>
            $(document).ready(function () {
                $("#header").load("header.php");
                const $slider = $(".slider");
                const $tabLinks = $(".tab-link");

                function moveSlider($btn) {
                    const leftPos = $btn.position().left;
                    const width = $btn.outerWidth();
                    $slider.css({ left: leftPos, width: width });
                }
                moveSlider($(".tab-link.active"));

                $tabLinks.click(function () {
                    $tabLinks.removeClass("active");
                    $(this).addClass("active");
                    const tab = $(this).data("tab");
                    $(".tab-content:visible").slideUp(function () {
                        $("#" + tab).slideDown();
                    });
                    moveSlider($(this));
                });

                $("#filter-btn").click(function () {
                    $(".filter-panel").slideToggle();
                });
                $("#fav-btn").click(function () {
                    $(".fav-panel").slideToggle();
                });
            });
        </script>
    </body>
</html>