-- Database schema and security migration (2026-10-20).
-- Safe to run on the existing schema. Back up your database before running.

-- 1. Move the remaining MyISAM tables to InnoDB so later work can use
--    transactions and foreign keys.
ALTER TABLE `system_users`      ENGINE=InnoDB;
ALTER TABLE `election_category` ENGINE=InnoDB;

-- 2. Remove the insecure hard-coded default password on the column.
--    Passwords must always be set explicitly as a password_hash() value.
ALTER TABLE `system_users` ALTER COLUMN `password` DROP DEFAULT;

-- 3. Fix the corrupted quoted default on request_to_register.adult_voter
--    (was literally '''No''').
ALTER TABLE `request_to_register` ALTER COLUMN `adult_voter` SET DEFAULT 'No';

-- 4. Enforce unique emails so accounts cannot be silently duplicated.
--    (Run the SELECT first; resolve duplicates before adding the index.)
-- SELECT email, COUNT(*) c FROM system_users GROUP BY email HAVING c > 1;
ALTER TABLE `system_users` ADD UNIQUE KEY `uq_system_users_email` (`email`);
