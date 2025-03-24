<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'php_func/admin_functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminLogin.php");
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'deleteUser':
                if (isset($_POST['userId'])) {
                    echo json_encode(deleteUser($_POST['userId']));
                }
                break;
            case 'deletePost':
                if (isset($_POST['postId'])) {
                    echo json_encode(deletePost($_POST['postId']));
                }
                break;
            case 'search':
                if (isset($_POST['searchType']) && isset($_POST['searchTerm'])) {
                    $result = searchUsers($_POST['searchType'], $_POST['searchTerm']);
                    echo json_encode($result);
                }
                break;
            case 'searchPosts':
                if (isset($_POST['searchType']) && isset($_POST['searchTerm'])) {
                    $result = searchPosts($_POST['searchType'], $_POST['searchTerm']);
                    echo json_encode($result);
                }
                break;
        }
        exit;
    }
}

// Get initial data
$users = getUsers();
$posts = getPosts();

// Debug output
if (isset($posts['error'])) {
    echo "<!-- Posts Error: " . htmlspecialchars($posts['error']) . " -->";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - CC Blogs</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</head>
<body>
    <div id="header"></div>
    
    <nav class="breadcrumb">
        <a href="index.php">Home</a> &gt; 
        <span>Admin Panel</span>
    </nav>
    
    <div class="admin-container">
        <h2>Admin Panel</h2>

        <div class="search-container">
            <h3>Search for User</h3>
            <form id="searchForm">
                <div class="form-group">
                    <label>Search by:</label>
                    <div class="search-options">
                        <label><input type="radio" name="searchType" value="name" checked> Name</label>
                        <label><input type="radio" name="searchType" value="email"> Email</label>
                        <label><input type="radio" name="searchType" value="post"> Post</label>
                    </div>
                    <input type="text" id="search" name="search" placeholder="Enter search term...">
                    <div class="form-buttons">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                        <button type="button" id="resetUsers" class="reset-button"><i class="fas fa-undo"></i> Reset</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="user-management">
            <h3>User Management</h3>
            <?php if (isset($users['error'])): ?>
                <div class="error-message">Error loading users: <?php echo htmlspecialchars($users['error']); ?></div>
            <?php else: ?>
            <table id="userTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="5">No users found</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr data-id="<?php echo htmlspecialchars($user['id']); ?>">
                            <td><?php echo htmlspecialchars($user['fullName']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td>
                                <button class="delete-user" <?php echo $user['role'] === 'admin' ? 'disabled' : ''; ?>>
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="post-management">
            <h3>Post Management</h3>
            <div class="search-container">
                <form id="postSearchForm">
                    <div class="form-group">
                        <label>Search posts by:</label>
                        <div class="search-options">
                            <label><input type="radio" name="postSearchType" value="title" checked> Title</label>
                            <label><input type="radio" name="postSearchType" value="author"> Author</label>
                        </div>
                        <input type="text" id="postSearch" name="postSearch" placeholder="Enter search term...">
                        <div class="form-buttons">
                            <button type="submit"><i class="fas fa-search"></i> Search</button>
                            <button type="button" id="resetPosts" class="reset-button"><i class="fas fa-undo"></i> Reset</button>
                        </div>
                    </div>
                </form>
            </div>
            <?php if (isset($posts['error'])): ?>
                <div class="error-message">Error loading posts: <?php echo htmlspecialchars($posts['error']); ?></div>
            <?php else: ?>
            <table id="postTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr><td colspan="4">No posts found</td></tr>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                        <tr data-id="<?php echo htmlspecialchars($post['id']); ?>">
                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                            <td><?php echo htmlspecialchars($post['author']); ?></td>
                            <td><?php echo htmlspecialchars($post['created_at']); ?></td>
                            <td>
                                <button class="delete-post">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#header").load("header.php");

            // Store initial table states
            var initialUsers = $("#userTable tbody").html();
            var initialPosts = $("#postTable tbody").html();

            // Reset users table
            $("#resetUsers").click(function() {
                $("#search").val('');
                $("input[name='searchType'][value='name']").prop('checked', true);
                $("#userTable tbody").html(initialUsers);
            });

            // Reset posts table
            $("#resetPosts").click(function() {
                $("#postSearch").val('');
                $("input[name='postSearchType'][value='title']").prop('checked', true);
                $("#postTable tbody").html(initialPosts);
            });

            // Handle user deletion
            $(document).on('click', '.delete-user', function() {
                if (!confirm("Are you sure you want to delete this user? This will also delete all their posts.")) {
                    return;
                }
                
                var row = $(this).closest('tr');
                var userId = row.data('id');
                
                $.post('admin.php', {
                    action: 'deleteUser',
                    userId: userId
                }, function(response) {
                    if (response.success) {
                        row.remove();
                    } else {
                        alert('Error deleting user: ' + response.error);
                    }
                });
            });

            // Handle post deletion
            $(document).on('click', '.delete-post', function() {
                if (!confirm("Are you sure you want to delete this post?")) {
                    return;
                }
                
                var row = $(this).closest('tr');
                var postId = row.data('id');
                
                $.post('admin.php', {
                    action: 'deletePost',
                    postId: postId
                }, function(response) {
                    if (response.success) {
                        row.remove();
                    } else {
                        alert('Error deleting post: ' + response.error);
                    }
                });
            });

            // Handle user search
            $("#searchForm").submit(function(event) {
                event.preventDefault();
                var searchType = $("input[name='searchType']:checked").val();
                var searchTerm = $("#search").val().trim();
                
                if (searchTerm === "") {
                    return;
                }
                
                $.post('admin.php', {
                    action: 'search',
                    searchType: searchType,
                    searchTerm: searchTerm
                }, function(response) {
                    console.log('User search response:', response);
                    
                    var tbody = $("#userTable tbody");
                    tbody.empty();
                    
                    if (response.error) {
                        alert('Error performing search: ' + response.error);
                        return;
                    }
                    
                    if (Array.isArray(response) && response.length > 0) {
                        response.forEach(function(user) {
                            var row = $(`
                                <tr data-id="${user.id}">
                                    <td>${user.fullName}</td>
                                    <td>${user.email}</td>
                                    <td>${user.role}</td>
                                    <td>${user.created_at}</td>
                                    <td>
                                        <button class="delete-user" ${user.role === 'admin' ? 'disabled' : ''}>
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>`);
                            tbody.append(row);
                        });
                    } else {
                        tbody.append('<tr><td colspan="5" class="no-results">No users found</td></tr>');
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Search error:', textStatus, errorThrown);
                    alert('Error performing search: ' + textStatus);
                });
            });

            // Handle post search
            $("#postSearchForm").submit(function(event) {
                event.preventDefault();
                var searchType = $("input[name='postSearchType']:checked").val();
                var searchTerm = $("#postSearch").val().trim();
                
                if (searchTerm === "") {
                    return;
                }
                
                $.post('admin.php', {
                    action: 'searchPosts',
                    searchType: searchType,
                    searchTerm: searchTerm
                }, function(response) {
                    console.log('Post search response:', response);
                    
                    var tbody = $("#postTable tbody");
                    tbody.empty();
                    
                    if (response.error) {
                        alert('Error performing search: ' + response.error);
                        return;
                    }
                    
                    if (Array.isArray(response) && response.length > 0) {
                        response.forEach(function(post) {
                            var row = $(`
                                <tr data-id="${post.id}">
                                    <td>${post.title}</td>
                                    <td>${post.author}</td>
                                    <td>${post.created_at}</td>
                                    <td>
                                        <button class="delete-post">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>`);
                            tbody.append(row);
                        });
                    } else {
                        tbody.append('<tr><td colspan="4" class="no-results">No posts found</td></tr>');
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error('Search error:', textStatus, errorThrown);
                    alert('Error performing search: ' + textStatus);
                });
            });
        });
    </script>
</body>
</html> 