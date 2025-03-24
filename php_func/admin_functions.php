<?php
require_once 'configure.php';

function getUsers() {
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
        $stmt = $pdo->query("SELECT id, fullName, email, role, created_at FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => "Database error: " . $e->getMessage()];
    }
}

function getPosts() {
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
        $stmt = $pdo->query("
            SELECT b.id, b.title, b.created_at, u.fullName as author 
            FROM blogs b 
            JOIN users u ON b.author_id = u.id
            ORDER BY b.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => "Database error: " . $e->getMessage()];
    }
}

function searchUsers($searchType, $searchTerm) {
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
        
        $query = "";
        $params = [':searchTerm' => "%$searchTerm%"];
        
        switch($searchType) {
            case 'name':
                $query = "SELECT id, fullName, email, role, created_at FROM users WHERE fullName LIKE :searchTerm";
                break;
            case 'email':
                $query = "SELECT id, fullName, email, role, created_at FROM users WHERE email LIKE :searchTerm";
                break;
            case 'post':
                $query = "SELECT DISTINCT u.id, u.fullName, u.email, u.role, u.created_at 
                         FROM users u 
                         JOIN blogs b ON u.id = b.author_id 
                         WHERE b.title LIKE :searchTerm";
                break;
            default:
                return ['error' => "Invalid search type"];
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no results found, return empty array instead of false
        return $results ? $results : [];
    } catch (PDOException $e) {
        return ['error' => "Database error: " . $e->getMessage()];
    }
}

function deleteUser($userId) {
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete user's posts first (due to foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE author_id = :userId");
        $stmt->execute([':userId' => $userId]);
        
        // Then delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :userId");
        $stmt->execute([':userId' => $userId]);
        
        // Commit transaction
        $pdo->commit();
        return ['success' => true];
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        return ['error' => "Database error: " . $e->getMessage()];
    }
}

function deletePost($postId) {
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = :postId");
        $stmt->execute([':postId' => $postId]);
        return ['success' => true];
    } catch (PDOException $e) {
        return ['error' => "Database error: " . $e->getMessage()];
    }
}

function searchPosts($searchType, $searchTerm) {
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
        
        $query = "SELECT b.id, b.title, b.created_at, u.fullName as author 
                 FROM blogs b 
                 JOIN users u ON b.author_id = u.id 
                 WHERE ";
        
        if ($searchType === 'title') {
            $query .= "b.title LIKE :searchTerm";
        } else if ($searchType === 'author') {
            $query .= "u.fullName LIKE :searchTerm";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([':searchTerm' => '%' . $searchTerm . '%']);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
        
    } catch (PDOException $e) {
        return ['error' => 'Database error: ' . $e->getMessage()];
    }
}
?> 