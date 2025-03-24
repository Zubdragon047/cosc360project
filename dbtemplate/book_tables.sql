-- Table structure for books
CREATE TABLE IF NOT EXISTS `books` (
  `book_id` INT AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `username` VARCHAR(25) NOT NULL,
  `cover_image` VARCHAR(100),
  `status` ENUM('available', 'borrowed', 'reserved') DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`book_id`),
  FOREIGN KEY (`username`) REFERENCES `users`(`username`) ON DELETE CASCADE
);

-- Table structure for book requests
CREATE TABLE IF NOT EXISTS `book_requests` (
  `request_id` INT AUTO_INCREMENT,
  `book_id` INT NOT NULL,
  `requester_username` VARCHAR(25) NOT NULL,
  `status` ENUM('pending', 'accepted', 'declined', 'completed') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE CASCADE,
  FOREIGN KEY (`requester_username`) REFERENCES `users`(`username`) ON DELETE CASCADE
);

-- Creating indexes for faster queries
CREATE INDEX idx_book_username ON books(username);
CREATE INDEX idx_book_category ON books(category);
CREATE INDEX idx_book_status ON books(status);
CREATE INDEX idx_book_requests_book_id ON book_requests(book_id);
CREATE INDEX idx_book_requests_requester ON book_requests(requester_username); 