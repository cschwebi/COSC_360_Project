CREATE DATABASE IF NOT EXISTS blogDB;
USE blogDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullName VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- store hashed passwords
    profileImage VARCHAR(255),       -- path or URL to the user's profile image
    bio TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',  -- distinguishes regular users from admins
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),              -- cover image path/URL
    topic VARCHAR(100),              -- e.g., Finance, Health, Technology, etc.
    author_id INT NOT NULL,          -- foreign key linking to the users table
    like_count INT DEFAULT 0,        -- total like count for the post
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,            -- foreign key referencing the blog post
    user_id INT NOT NULL,            -- foreign key referencing the user who commented
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    blog_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, blog_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE
);