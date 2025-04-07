CREATE DATABASE IF NOT EXISTS clewendo;
USE clewendo;

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

-- Create likes table to track who liked which posts
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    blog_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (user_id, blog_id),  -- Prevents duplicate likes
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE
);

-- Trigger updates like_count on liking and unliking
DELIMITER //
CREATE TRIGGER after_like_insert
AFTER INSERT ON likes
FOR EACH ROW
BEGIN
    UPDATE blogs SET like_count = (
        SELECT COUNT(*) FROM likes WHERE blog_id = NEW.blog_id
    ) WHERE id = NEW.blog_id;
END//

CREATE TRIGGER after_like_delete
AFTER DELETE ON likes
FOR EACH ROW
BEGIN
    UPDATE blogs SET like_count = (
        SELECT COUNT(*) FROM likes WHERE blog_id = OLD.blog_id
    ) WHERE id = OLD.blog_id;
END//
DELIMITER ;

-- First, let's insert some test users
INSERT INTO users (fullName, email, password, profileImage, bio, role) VALUES
(
    'John Smith',
    'john.smith@email.com',
    MD5('password123'),
    './img/default.png',
    'Tech enthusiast and professional software developer with 10 years of experience.',
    'admin'
),
(
    'Sarah Johnson',
    'sarah.j@email.com',
    MD5('password123'),
    './img/default.png',
    'Digital marketing specialist and tech blogger. Love sharing knowledge about web development.',
    'user'
),
(
    'Michael Chen',
    'mike.chen@email.com',
    MD5('password123'),
    './img/default.png',
    'UI/UX designer with a passion for creating beautiful and functional web experiences.',
    'user'
),
(
    'Emma Wilson',
    'emma.wilson@email.com',
    MD5('password123'),
    './img/default.png',
    'Health and wellness expert with a focus on mental health and work-life balance.',
    'user'
);


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

),
(
    'Mindful Eating: A Guide to Healthy Food Habits',
    'Discover the principles of mindful eating and how it can transform your relationship with food. This comprehensive guide explores the psychology behind eating habits, practical tips for mindful meal planning, and the benefits of conscious food choices. Learn about portion control, nutritional balance, and how to develop a healthy relationship with food that lasts a lifetime. We''ll also discuss common challenges and provide actionable strategies for maintaining healthy eating habits in today''s fast-paced world.',
    './img/blog.png',
    'Health',
    4,
    145
),
(
    'Financial Planning for Young Professionals',
    'Navigate the complexities of personal finance with this comprehensive guide tailored for young professionals. From budgeting basics to investment strategies, learn how to build a strong financial foundation early in your career. We''ll cover essential topics like emergency funds, retirement planning, and debt management. Discover practical tips for saving money, making smart investments, and planning for long-term financial security in today''s economic landscape.',
    './img/blog.png',
    'Finance',
    1,
    267
),
(
    'The Art of Travel Photography',
    'Elevate your travel photography skills with this in-depth guide to capturing memorable moments on your journeys. Learn about essential camera techniques, composition rules, and post-processing tips that will help you create stunning travel photographs. We''ll explore how to tell stories through images, capture local culture, and make the most of different lighting conditions. Whether you''re using a professional camera or smartphone, these tips will help you document your travels beautifully.',
    './img/blog.png',
    'Travel',
    3,
    178
),
(
    'Modern Parenting in a Digital World',
    'Navigate the challenges of raising children in today''s technology-driven world. This guide addresses common concerns about screen time, online safety, and digital literacy for children of all ages. Learn practical strategies for setting healthy boundaries, promoting positive technology use, and maintaining open communication with your children about digital media. We''ll also explore educational technology resources and tools that can support your child''s development.',
    './img/blog.png',
    'Parenting',
    4,
    203
),
(
    'Sustainable Business Practices for 2024',
    'Explore how businesses can thrive while maintaining environmental responsibility. This comprehensive guide covers sustainable business strategies, green technologies, and eco-friendly operational practices. Learn about reducing carbon footprints, implementing recycling programs, and creating environmentally conscious supply chains. We''ll also discuss the business benefits of sustainability and how to effectively communicate your green initiatives to stakeholders and customers.',
    './img/blog.png',
    'Business',
    2,
    189
),
(
    'Quick and Healthy Meal Prep Ideas',
    'Transform your weekly meal planning with these efficient and nutritious meal prep strategies. Discover time-saving techniques, budget-friendly recipes, and tips for maintaining food quality throughout the week. Learn how to create balanced meals, store food properly, and avoid common meal prep pitfalls. We''ll also share shopping lists, storage solutions, and creative ways to keep your meal prep interesting and delicious.',
    './img/blog.png',
    'Food',
    4,
    221
),
(
    'Maximizing Productivity with AI Tools',
    'Harness the power of artificial intelligence to boost your daily productivity. This guide explores the latest AI tools and technologies that can streamline your workflow and automate routine tasks. Learn about AI-powered project management, content creation, data analysis, and personal organization tools. We''ll also discuss how to integrate these tools effectively while maintaining a human-centered approach to work.',
    './img/blog.png',
    'Productivity',
    1,
    256
),
(
    'The Evolution of Online Learning',
    'Explore the transformative impact of digital technology on education and learning methodologies. This comprehensive analysis covers the latest trends in online education, including adaptive learning systems, gamification, and personalized learning paths. Learn about the most effective online learning strategies, tools for virtual collaboration, and ways to maintain engagement in digital classrooms. We''ll also examine case studies of successful online learning implementations and their implications for the future of education.',
    './img/blog.png',
    'Education',
    3,
    198
); 

-- Insert some dummy comments
INSERT INTO comments (blog_id, user_id, content) VALUES
(11, 2, 'Great insights on the future of web development!'),
(12, 3, 'I completely agree, especially with the AI integration.'),
(12, 1, 'This guide is really helpful for small businesses.'),
(13, 2, 'I struggle with remote work too, thanks for the tips!'),
(1, 3, 'Modern education is evolving fast—interesting read!'),
(5, 1, 'I love how technology is reshaping entertainment!');

-- Insert dummy likes data
INSERT INTO likes (user_id, blog_id, created_at) VALUES
-- John Smith (user_id: 1) likes
(1, 2, '2024-03-15 10:30:00'),  -- Digital Marketing Strategies
(1, 4, '2024-03-14 15:45:00'),  -- Impact of Technology on Education
(1, 7, '2024-03-13 09:20:00'),  -- Financial Planning
(1, 12, '2024-03-12 14:15:00'), -- Maximizing Productivity with AI

-- Sarah Johnson (user_id: 2) likes
(2, 1, '2024-03-16 11:20:00'),  -- Future of Web Development
(2, 3, '2024-03-15 16:30:00'),  -- Staying Productive While Working Remotely
(2, 8, '2024-03-14 13:45:00'),  -- Art of Travel Photography
(2, 11, '2024-03-13 10:15:00'), -- Quick and Healthy Meal Prep

-- Michael Chen (user_id: 3) likes
(3, 5, '2024-03-17 09:30:00'),  -- Entertainment in Digital Age
(3, 6, '2024-03-16 14:20:00'),  -- Mindful Eating
(3, 9, '2024-03-15 11:45:00'),  -- Modern Parenting
(3, 13, '2024-03-14 16:15:00'), -- Evolution of Online Learning

-- Emma Wilson (user_id: 4) likes
(4, 1, '2024-03-18 10:15:00'),  -- Future of Web Development
(4, 3, '2024-03-17 15:30:00'),  -- Staying Productive While Working Remotely
(4, 7, '2024-03-16 12:45:00'),  -- Financial Planning
(4, 12, '2024-03-15 09:20:00'); -- Maximizing Productivity with AI

