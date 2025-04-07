<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CC Blogs Header</title>
  <link rel="stylesheet" href="css/header.css">
  <script src="https://kit.fontawesome.com/1df3bfa6c9.js" crossorigin="anonymous"></script>
  <script>
    (function(){
      if (localStorage.getItem('theme') === 'dark') {
        var darkModeLink = document.createElement('link');
        darkModeLink.rel = 'stylesheet';
        darkModeLink.href = 'css/darkmode.css';
        darkModeLink.id = 'dark-mode-css'; //still a WIP but is extra
        document.head.appendChild(darkModeLink);
      }
    })();
  </script>
</head>
<body>
<header>
  <div class="top-header">
    <h1><a href="index.php">CC Blogs</a></h1>
    <div class="login-nav">
        <p>
        <?php //Dynamically update the login/logout button based on session status in top right
        session_start();
        if (isset($_SESSION['user_id'])) {
            echo "Hello, " . htmlspecialchars($_SESSION['displayName']);
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                echo " | <a href='adminLogin.php'>Admin</a>";
            }
            echo " | <a href='php_func/logout.php'>Logout</a>";
        } else {
            echo "<a href='register.html'>Register</a> | <a href='login.html'>Login</a> | <a href='adminLogin.php'>Admin</a>";
        }
        ?></p>
    </div>
    <div class="search-bar">
      <form id="searchForm" method="get" action="search.php">
        <input type="text" name="search" placeholder="Search blogs" required>
        <button type="submit">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>
    </div>
  </div>
  <nav>
    <ul>
      <?php
          //session_start();
          if (isset($_SESSION['user_id'])){
            echo "<li><a href='settings.php'>Settings</a></li>";
            echo "<li><a href='authors.php'>Authors</a></li>";
            echo "<li><a href='favourites.php'>Favourites</a></li>";
            echo "<li><a href='trending.php'>Trending</a></li>";
            echo "<li><a href='profile.php'>Profile</a></li>";
          }else{
            echo "<li><a href='login.html'>Settings</a></li>";
            echo "<li><a href='authors.php'>Authors</a></li>";
            echo "<li><a href='login.html'>Favourites</a></li>";
            echo "<li><a href='trending.php'>Trending</a></li>";
            echo "<li><a href='login.html'>Profile</a></li>";
          }
      ?>
    </ul>
  </nav>
</header>
</body>
</html>