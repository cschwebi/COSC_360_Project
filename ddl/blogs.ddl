CREATE DATABASE blogDB;

USE blogDB;

CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO blogs (title, content, image) VALUES
('Example Blog 1', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...', 'image1.jpg'),
('Example Blog 2', 'Another blog post example with some interesting content...', 'image2.jpg'),
('Example Blog 3', 'More content that should be loaded dynamically...', 'image3.jpg');
