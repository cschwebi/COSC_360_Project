<?php
require_once 'configure.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['body']);
    $topic = trim($_POST['topic']);
    $author_id = $_SESSION['user_id'];
    $image_path = './img/blog.png'; // Default image path

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
        $pdo->beginTransaction();

        // Insert the blog post first to get the ID
        $stmt = $pdo->prepare("
            INSERT INTO blogs (title, content, topic, author_id, image) 
            VALUES (:title, :content, :topic, :author_id, :image)
        ");

        $stmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':topic' => $topic,
            ':author_id' => $author_id,
            ':image' => $image_path
        ]);

        $blog_id = $pdo->lastInsertId();

        // Handle image upload if present
        if (isset($_FILES['headerImage']) && $_FILES['headerImage']['error'] === UPLOAD_ERR_OK) {
            $file_info = pathinfo($_FILES['headerImage']['name']);
            $file_extension = strtolower($file_info['extension']);
            $file_name = $file_info['filename'];
            
            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception('Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.');
            }

            // Create img/blogimages directory if it doesn't exist
            $upload_dir = '../img/blogimages/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Set the new file path //found the problem lol
            $new_file_name = $blog_id . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;
            $db_image_path = './img/blogimages/' . $new_file_name;

            // Move uploaded file
            if (move_uploaded_file($_FILES['headerImage']['tmp_name'], $upload_path)) {
                // Update the image path in the database
                $stmt = $pdo->prepare("UPDATE blogs SET image = :image WHERE id = :id");
                $stmt->execute([
                    ':image' => $db_image_path,
                    ':id' => $blog_id
                ]);
            } else {
                throw new Exception('Failed to move uploaded file.');
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Post created successfully']);
        

    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?> 