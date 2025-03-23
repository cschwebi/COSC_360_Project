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
    topic VARCHAR(100) NOT NULL,              -- e.g., Finance, Health, Technology, Travel, Productivity, Food, Business, Parenting, Education, Entertainment, Others.
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

-- First, let's insert some test users
INSERT INTO users (fullName, email, password, profileImage, bio, role) VALUES
(
    'John Smith',
    'john.smith@email.com',
    'password123',
    './img/default.png',
    'Tech enthusiast and professional software developer with 10 years of experience.',
    'admin'
),
(
    'Sarah Johnson',
    'sarah.j@email.com',
    'password123',
    './img/default.png',
    'Digital marketing specialist and tech blogger. Love sharing knowledge about web development.',
    'user'
),
(
    'Michael Chen',
    'mike.chen@email.com',
    'password123',
    './img/default.png',
    'UI/UX designer with a passion for creating beautiful and functional web experiences.',
    'user'
);

-- Now let's create some blog posts
INSERT INTO blogs (title, content, image, topic, author_id, like_count) VALUES
(
    'The Future of Web Development in 2024',
    'As we move further into 2024, the landscape of web development continues to evolve at an unprecedented pace. Artificial Intelligence and Machine Learning are becoming increasingly integrated into web applications, transforming how we build and interact with websites. Full-stack developers are now expected to understand not just traditional web technologies, but also AI integration patterns and serverless architectures. The rise of Web Assembly has opened new possibilities for high-performance web applications, while Progressive Web Apps continue to blur the line between web and native applications. This post explores these trends and what they mean for developers and businesses alike.',
    './img/blog.png',
    'Technology',
    1,
    156
),
(
    'Digital Marketing Strategies for Small Businesses',
    'CSS Grid has revolutionized how we approach web layouts, offering a level of control and flexibility that was previously impossible. In this comprehensive guide, we''ll explore everything from basic grid concepts to advanced layout techniques. Learn how to create responsive layouts without media queries, master grid template areas, and understand the power of fractional units. We''ll also cover grid auto-placement algorithms and how to create magazine-style layouts with overlapping elements. Whether you''re a beginner or an experienced developer, this guide will help you take your CSS Grid skills to the next level.',
    './img/blog.png',
    'Business',
    2,
    234
),
(
    'Staying Productive While Working Remotely',
    'In today''s digital landscape, maintaining productivity while working from home is more important than ever. This comprehensive guide covers essential practices every remote worker should know. From setting up an efficient home office to maintaining work-life balance, we''ll explore the fundamental aspects of remote work productivity. Learn about time management techniques, virtual collaboration tools, and strategies for staying focused. We''ll also discuss common challenges and how to overcome them using proven productivity methods and tools.',
    './img/blog.png',
    'Productivity',
    1,
    189
),
(
    'The Impact of Technology on Modern Education',
    'Technology is revolutionizing how we approach education in the modern world. This guide explores how digital tools and platforms are transforming the learning experience. We''ll cover the rise of online learning platforms, virtual classrooms, and interactive educational technologies. Learn about the benefits and challenges of digital education, accessibility considerations, and how to effectively integrate technology into traditional learning environments. We''ll also discuss future trends and their potential impact on educational systems worldwide.',
    './img/blog.png',
    'Education',
    3,
    167
),
(
    'Entertainment in the Digital Age',
    'The entertainment industry has undergone a massive transformation in the digital era. From streaming services to virtual reality experiences, this post explores how technology is reshaping how we consume and interact with entertainment. Learn about the latest trends in digital entertainment, the rise of interactive content, and how traditional media is adapting to the new digital landscape. We''ll also discuss the future of entertainment technology and its impact on content creators and consumers alike.',
    './img/blog.png',
    'Entertainment',
    2,
    198
); 