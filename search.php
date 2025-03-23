<!DOCTYPE html>
<?php
require_once 'php_func/configure.php';

// Get the search query, category, and search field and sanitize them
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search_field = isset($_GET['search_field']) ? trim($_GET['search_field']) : 'title';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    // Base query
    $sql = "SELECT b.id, b.title, b.content, b.image, b.created_at, b.topic, u.fullName as author 
            FROM blogs b 
            JOIN users u ON b.author_id = u.id";
    $params = array();
    
    // Add WHERE clause only if search or category filters are applied
    if (!empty($search_query) || !empty($category)) {
        $sql .= " WHERE 1=1";
        
        // Add search condition if search term exists
        if (!empty($search_query)) {
            switch($search_field) {
                case 'content':
                    $sql .= " AND LOWER(b.content) LIKE LOWER(:search)";
                    break;
                case 'author':
                    $sql .= " AND LOWER(u.fullName) LIKE LOWER(:search)";
                    break;
                case 'title':
                default:
                    $sql .= " AND LOWER(b.title) LIKE LOWER(:search)";
                    break;
            }
            $search_pattern = "%{$search_query}%";
            $params[':search'] = $search_pattern;
        }
        
        // Add category condition if category is selected
        if (!empty($category)) {
            $sql .= " AND b.topic = :category";
            $params[':category'] = $category;
        }
    }
    
    // Add order by
    $sql .= " ORDER BY b.created_at DESC";
    
    // First get total count
    $count_sql = "SELECT COUNT(*) FROM (" . $sql . ") as count_query";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_results = $count_stmt->fetchColumn();
    
    // Add limit and offset
    if (empty($search_query) && empty($category)) {
        // Show first 100 blogs when no filters are applied
        $sql .= " LIMIT 100";
        $blogs = $pdo->query($sql)->fetchAll();
    } else {
        // Use pagination for filtered results
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $per_page;
        $params[':offset'] = ($page - 1) * $per_page;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $blogs = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error = "Database connection failed: " . $e->getMessage();
}

// Define categories array
$categories = [
    'Finance', 'Health', 'Technology', 'Travel', 
    'Productivity', 'Food', 'Business', 'Parenting', 
    'Education', 'Entertainment', 'Other'
];
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - CC Blogs</title>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/search.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Include Header -->
    <div id="header-placeholder"></div>

    <main>
        <section class="content">
            <div class="blogs">
                <div class="search-header">
                    <h2>Search Results</h2>
                </div>

                <div class="filter-panel">
                    <form method="get" action="search.php">
                        <div class="search-group">
                            <label for="search">Search Term</label>
                            <input type="text" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search_query); ?>" 
                                   placeholder="Enter keywords...">
                            <div class="search-field-group">
                                <label>Search In</label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" id="search_title" name="search_field" value="title" 
                                               <?php echo ($search_field === 'title' || empty($search_field)) ? 'checked' : ''; ?>>
                                        <label for="search_title">Blog Title</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" id="search_content" name="search_field" value="content"
                                               <?php echo $search_field === 'content' ? 'checked' : ''; ?>>
                                        <label for="search_content">Blog Content</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" id="search_author" name="search_field" value="author"
                                               <?php echo $search_field === 'author' ? 'checked' : ''; ?>>
                                        <label for="search_author">Author Name</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="category-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                                            <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit">Search</button>
                    </form>
                    <?php if (!empty($search_query) || !empty($category)): ?>
                        <div class="active-filters">
                            <?php if (!empty($search_query)): ?>
                                <span class="filter-tag">
                                    <?php 
                                    $field_label = [
                                        'title' => 'Title',
                                        'content' => 'Content',
                                        'author' => 'Author'
                                    ][$search_field] ?? 'Title';
                                    echo htmlspecialchars($field_label . ': ' . $search_query); 
                                    ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($category)): ?>
                                <span class="filter-tag">Category: <?php echo htmlspecialchars($category); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php elseif (empty($blogs)): ?>
                    <div class="no-results">
                        <p>No blogs found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <?php if (empty($search_query) && empty($category)): ?>
                        <div class="search-info">
                            <p>Showing 100 most recent blogs</p>
                        </div>
                    <?php endif; ?>
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
                                    $words = str_word_count(strip_tags($blog['content']), 2);
                                    $first50 = implode(' ', array_slice(array_values($words), 0, 50));
                                    echo htmlspecialchars($first50) . '...' ;
                                ?></p>
                            </div>
                            <div class="blog-image">
                                <img src="<?php echo htmlspecialchars($blog['image']); ?>" 
                                     alt="Cover image for <?php echo htmlspecialchars($blog['title']); ?>">
                            </div>
                        </article>
                    <?php endforeach; ?>
                    
                    <?php if ($total_results > $page * $per_page): ?>
                        <form method="get" action="search.php" class="load-more-form">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                            <input type="hidden" name="search_field" value="<?php echo htmlspecialchars($search_field); ?>">
                            <input type="hidden" name="page" value="<?php echo $page + 1; ?>">
                            <button type="submit" class="load-more">
                                Load More Results <br> (<?php echo $total_results - ($page * $per_page); ?> remaining)
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <aside class="trending-topics">
                <h2>Trending Topics</h2>
                <ol>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="search.php?category=<?php echo urlencode($cat); ?>" 
                               class="<?php echo $category === $cat ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat); ?>
                                <?php if ($category === $cat): ?>
                                    <span class="active-indicator">•</span>
                                <?php endif; ?>
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