-- Table structure for book comments
CREATE TABLE IF NOT EXISTS `book_comments` (
  `comment_id` INT AUTO_INCREMENT,
  `book_id` INT NOT NULL,
  `username` VARCHAR(25) NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE CASCADE,
  FOREIGN KEY (`username`) REFERENCES `users`(`username`) ON DELETE CASCADE
);

-- Creating indexes for faster comment retrieval
CREATE INDEX idx_book_id ON book_comments(book_id);
CREATE INDEX idx_book_comment_username ON book_comments(username); 