<!DOCTYPE html>
<?php
require_once 'php_func/configure.php';

// Get the search query, category, and search field and sanitize them
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search_field = isset($_GET['search_field']) ? trim($_GET['search_field']) : 'title';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
    
    if (!empty($search_query) || !empty($category)) {
        // Base query
        $sql = "SELECT b.id, b.title, b.content, b.image, b.created_at, b.topic, u.fullName as author 
                FROM blogs b 
                JOIN users u ON b.author_id = u.id 
                WHERE 1=1";
        $params = array();
        
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
        
        // Add order by
        $sql .= " ORDER BY b.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $blogs = $stmt->fetchAll();
    } else {
        $blogs = [];
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .search-header {
            margin-bottom: 2rem;
        }
        .search-header h2 {
            margin: 0;
            color: #2e2f59;
        }
        .search-header p {
            color: #666;
            margin: 0.5rem 0 0 0;
        }
        .no-results {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-panel {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .filter-panel form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }
        .filter-panel .search-group {
            flex: 2;
            min-width: 250px;
        }
        .filter-panel .category-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-panel .search-field-group {
            flex: 2;
            min-width: 250px;
        }
        .filter-panel label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2e2f59;
            font-weight: bold;
        }
        .filter-panel input[type="text"],
        .filter-panel select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            font-size: 1rem;
        }
        .filter-panel button {
            padding: 0.5rem 1.5rem;
            background: #2e2f59;
            color: white;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s;
            height: 2.5rem;
        }
        .filter-panel button:hover {
            background: #11122f;
        }
        .active-filters {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .active-filters .filter-tag {
            background: #e0e7fa;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.9rem;
            color: #2e2f59;
        }
        .radio-group {
            display: flex;
            gap: 1.5rem;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .radio-option input[type="radio"] {
            margin: 0;
        }
        .radio-option label {
            margin: 0;
            font-weight: normal;
        }
    </style>
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
                <?php elseif (empty($search_query) && empty($category)): ?>
                    <div class="no-results">
                        <p>Please enter a search term or select a category to find blogs.</p>
                    </div>
                <?php elseif (empty($blogs)): ?>
                    <div class="no-results">
                        <p>No blogs found matching your criteria.</p>
                    </div>
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
                <?php endif; ?>
            </div>

            <aside class="trending-topics">
                <h2><a href="trending.html">Trending Topics</a></h2>
                <ol>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="search.php?category=<?php echo urlencode($cat); ?>">
                                <?php echo htmlspecialchars($cat); ?>
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