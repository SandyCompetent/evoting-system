-- Category foreign-key migration (2026-10-26).
-- Back up first:
--   mysqldump -u root evoting_system > evoting_backup.sql
-- Apply:
--   mysql -u root evoting_system < db/migration-2026-10-26-category-fk.sql

-- 1. Admins do not belong to an election category. Allow the column to be NULL.
ALTER TABLE `system_users`
  MODIFY `election_category_id` INT NULL DEFAULT NULL;

-- 2. Clear legacy zero values and any reference whose category no longer exists.
UPDATE `system_users` u
LEFT JOIN `election_category` c ON c.id = u.election_category_id
SET u.election_category_id = NULL
WHERE u.election_category_id = 0 OR c.id IS NULL;

-- 3. Enforce category ownership. Deleting a category removes its users, matching
--    PdoCategoryRepository::deleteWithCascade().
ALTER TABLE `system_users`
  ADD CONSTRAINT `fk_system_users_category`
  FOREIGN KEY (`election_category_id`)
  REFERENCES `election_category` (`id`)
  ON DELETE CASCADE;
