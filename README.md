# Book Exchange Platform

An online platform that allows users to exchange books with one another. Users can list books they own and are willing to lend, browse books from other users, request books, and participate in discussions.

## Features

### User Management
- User registration with email verification
- User authentication (login/logout)
- Password reset functionality
- User profile management
- Admin users with additional privileges

### Book Management
- List books you own
- Browse books from other users
- Search books by title, category, or status
- Request books from other users
- View book details including cover image
- Track borrowed books and their status

### Discussion Forums
- Create discussion threads
- Comment on threads
- View all discussions

### Admin Features
- User management (view, promote, demote, delete)
- Book management (view, delete)
- Thread management (view, delete)
- Comment management (view, delete)
- Report management (view, resolve, dismiss)
- Content search across all content types (books, threads, comments)

#### Admin Dashboard
- Tab-based interface for easy navigation between management areas
- Advanced AJAX search functionality for users, books, and threads
- Comprehensive report filtering system with status indicators
- Robust error handling with user-friendly messages and retry options
- Server-side fallback rendering for critical sections when AJAX fails
- Real-time content overview showing recent activity across the platform
- Direct action buttons for content moderation tasks
- Mobile-responsive design for administration on any device

### Content Reporting
- Report inappropriate books, threads, or comments
- Admin panel for reviewing and handling reports
- Status tracking for reports (pending, resolved, dismissed)

## Technical Details

### Database Structure
- Users table: stores user information
- Books table: stores book information
- Book_requests table: tracks book lending requests and status
- Threads table: stores discussion threads
- Comments table: stores comments on threads
- Reports table: stores user-submitted reports of content

### Security Features
- Password hashing
- Input validation and sanitization
- Protection against SQL injection
- Session management
- Access control based on user roles

### UI/UX
- Responsive design
- Intuitive navigation
- User-friendly error messages
- Success notifications
- Admin dashboard with tabs for different management areas

### Admin Dashboard Implementation
- Implements XMLHttpRequest for robust AJAX operations with enhanced browser compatibility
- Comprehensive error handling with graceful degradation
- Server-side fallback rendering ensures content accessibility even when JavaScript fails
- Deep linking support via URL hash and parameters
- Real-time reporting system with filtering and status management
- Enhanced debugging using console logging and specialized diagnostic tools
- Custom CSS styling for admin-specific interface elements

## Site Map
- Home page: Welcome page with featured books
- Browse: View all available books with search/filter options
- Dashboard: User's personal area with their books, requests, etc.
- Discussions: Forum area for book discussions
- User profile: View/edit personal information
- Book detail: Detailed view of a specific book
- Admin panel: Management interface for administrators

## Installation

1. Clone the repository
2. Set up a web server with PHP and MySQL
3. Import the database schema (schema.sql)
4. Configure database connection in protected/config.php
5. Ensure file permissions are set correctly for uploaded images
6. Access the site through your web server