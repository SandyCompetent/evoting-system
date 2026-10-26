-- Category foreign-key migration (2026-10-26). Idempotent: safe to run on a
-- database in any of the following states:
--   * old schema with election_category_id NOT NULL DEFAULT 0 and no FK
--   * nullable column with the original ON DELETE CASCADE FK
--   * already-migrated nullable column with ON DELETE RESTRICT
--
-- Back up first:
--   mysqldump -u root --set-gtid-purged=OFF --single-transaction evoting_system > evoting_backup.sql
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

-- 3. Replace any existing foreign key on this column. RESTRICT makes
--    PdoCategoryRepository::deleteWithCascade() the single owner of the
--    deletion semantics: the repository deletes the dependent votes,
--    candidates and non-admin users in one transaction, and the database
--    refuses to orphan an account if that cleanup is ever skipped.
SET @fk_name = (
  SELECT CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'system_users'
     AND COLUMN_NAME = 'election_category_id'
     AND REFERENCED_TABLE_NAME IS NOT NULL
   LIMIT 1
);

SET @drop_fk = IF(
  @fk_name IS NULL,
  'SELECT 1',
  CONCAT('ALTER TABLE `system_users` DROP FOREIGN KEY `', @fk_name, '`')
);
PREPARE drop_stmt FROM @drop_fk;
EXECUTE drop_stmt;
DEALLOCATE PREPARE drop_stmt;

SET @add_fk = 'ALTER TABLE `system_users`
  ADD CONSTRAINT `fk_system_users_category`
  FOREIGN KEY (`election_category_id`)
  REFERENCES `election_category` (`id`)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT';
PREPARE add_stmt FROM @add_fk;
EXECUTE add_stmt;
DEALLOCATE PREPARE add_stmt;

-- 4. Verify with:
--   SELECT DELETE_RULE FROM information_schema.REFERENTIAL_CONSTRAINTS
--    WHERE CONSTRAINT_SCHEMA = DATABASE()
--      AND CONSTRAINT_NAME = 'fk_system_users_category';
