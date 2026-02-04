-- Add role column to users table if it doesn't exist
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `role` VARCHAR(20) NOT NULL DEFAULT 'user';

-- Create admin user (password: Admin@123)
-- The password is hashed using PHP's password_hash() function
INSERT INTO `users` (`username`, `password`, `email`, `role`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'admin')
ON DUPLICATE KEY UPDATE 
    `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    `role` = 'admin';

-- Make sure the admin has all necessary permissions
-- (This assumes you have a permissions system in place)
-- INSERT IGNORE INTO `user_permissions` (`user_id`, `permission`)
-- SELECT id, 'admin' FROM `users` WHERE `username` = 'admin';

-- Update existing admin user's role if exists
UPDATE `users` SET `role` = 'admin' WHERE `username` = 'admin';

-- Verify the admin user was created/updated
SELECT `id`, `username`, `email`, `role`, `created_at` 
FROM `users` 
WHERE `username` = 'admin';
