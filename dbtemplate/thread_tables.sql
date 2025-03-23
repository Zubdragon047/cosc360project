-- Table structure for threads
CREATE TABLE IF NOT EXISTS `threads` (
  `thread_id` INT AUTO_INCREMENT,
  `title` VARCHAR(100) NOT NULL,
  `username` VARCHAR(25) NOT NULL,
  `book_id` INT,
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`thread_id`),
  FOREIGN KEY (`username`) REFERENCES `users`(`username`) ON DELETE CASCADE
);

-- Table structure for comments
CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` INT AUTO_INCREMENT,
  `thread_id` INT NOT NULL,
  `username` VARCHAR(25) NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  FOREIGN KEY (`thread_id`) REFERENCES `threads`(`thread_id`) ON DELETE CASCADE,
  FOREIGN KEY (`username`) REFERENCES `users`(`username`) ON DELETE CASCADE
);

-- Creating index for faster thread and comment retrieval
CREATE INDEX idx_thread_id ON comments(thread_id);
CREATE INDEX idx_thread_username ON threads(username);
CREATE INDEX idx_comment_username ON comments(username); 