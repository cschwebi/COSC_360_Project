# ccblogs

ccblogs is a blog platform designed for posting, browsing, and managing content across various topics. It supports user registration, blog creation, and admin-level content moderation.

**MAY HAVE TO MANUALLY RUN DDL TO LOAD DUMMY DATA**

## Features

### User Registration and Login
- Register with a unique email and hashed password.
- Profile image uploads with unique filenames.
- Session-based authentication.

### Home Page
- Shows 5 most recent blog posts.
- Highlights trending topics with links to search pages.

### Search
- Search by blog title, content, or author.
- Filter by topic.
- Defaults to 100 most recent posts if no input.
- Pagination: 12 posts per page.

### Blog Pages
- Displays full blog content, image, like count, and comments.
- Comments and likes update with AJAX (no page reload).

### Author Pages
- View all authors, ordered by number of posts.
- Author profile shows bio, post count, total likes, and their posts.

### Profile Page
- View and edit your name, bio, and password.
- View your posts and comments.
- Links to create a post or edit profile.

### Admin Page
- Accessible only to users with admin role.
- Search/delete users by name, email, or post title.
- Search/delete posts by title or author.
- Confirmation prompts before deletion.

### Post Creation
- Create blog posts with title, content, and optional image.
- Default image used if none uploaded.

## Demo Users

| Name           | Email                     | Password      | Role   |
|----------------|---------------------------|---------------|--------|
| John Smith     | john.smith@email.com      | password123   | Admin  |
| Sarah Johnson  | sarah.j@email.com         | password123   | User   |
| Michael Chen   | mike.chen@email.com       | password123   | User   |
| Emma Wilson    | emma.wilson@email.com     | password123   | User   |

## Notes
- Profile image editing is still in progress.
- Favorites table exists but is not fully integrated yet.
- All uploaded images are stored in ./img/ or /uploads/