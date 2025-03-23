-- Add type column to users table
ALTER TABLE `users` ADD `type` VARCHAR(10) NOT NULL DEFAULT 'user' AFTER `profilepic`;

-- Update the existing user to have the 'user' type
UPDATE `users` SET `type` = 'user' WHERE `type` IS NULL; 