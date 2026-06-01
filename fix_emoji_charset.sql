-- Fix emoji reactions charset issue
-- This script converts the emoji columns to utf8mb4 to properly support emoji characters

-- Step 1: Clear corrupted emoji data (stored as '?')
DELETE FROM `post_reactions` WHERE `emoji` = '?';
DELETE FROM `comment_reactions` WHERE `emoji` = '?';

-- Step 2: Convert tables to utf8mb4
ALTER TABLE `post_reactions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `comment_reactions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Step 3: Explicitly fix emoji column
ALTER TABLE `post_reactions` MODIFY COLUMN `emoji` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `comment_reactions` MODIFY COLUMN `emoji` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

-- Verify the changes
SELECT 'post_reactions table info:' as '';
SHOW CREATE TABLE `post_reactions`\G

SELECT 'comment_reactions table info:' as '';
SHOW CREATE TABLE `comment_reactions`\G

SELECT 'Sample post reactions:' as '';
SELECT * FROM `post_reactions` LIMIT 5;

SELECT 'Sample comment reactions:' as '';
SELECT * FROM `comment_reactions` LIMIT 5;
