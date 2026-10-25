-- Auditable vote model migration.
-- Back up your database before running:
--   mysqldump -u root evoting_system > evoting_backup.sql
-- Apply:
--   mysql -u root evoting_system < db/migration_vote_model.sql

-- One row per ballot cast. The UNIQUE key is what enforces
-- one-vote-per-voter-per-election at the database level (defence that does
-- not depend on the UI or the application code being correct).
CREATE TABLE IF NOT EXISTS `vote` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `voter_id`     INT NOT NULL,
  `category_id`  INT NOT NULL,
  `candidate_id` INT NOT NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_one_vote_per_election` (`voter_id`, `category_id`),
  KEY `idx_tally` (`category_id`, `candidate_id`),
  CONSTRAINT `fk_vote_voter`     FOREIGN KEY (`voter_id`)     REFERENCES `system_users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vote_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidate` (`id`)        ON DELETE CASCADE,
  CONSTRAINT `fk_vote_category`  FOREIGN KEY (`category_id`)  REFERENCES `election_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- The legacy `vote_details` counter table is no longer used by the
-- application. It is left in place (non-destructive) for historical reference;
-- you may drop it once you have confirmed the new model:
--   DROP TABLE `vote_details`;

-- Tally for a category (authoritative):
--   SELECT c.id, c.full_name, COUNT(v.id) AS votes
--   FROM candidate c
--   LEFT JOIN vote v ON v.candidate_id = c.id AND v.category_id = c.category_id
--   WHERE c.category_id = ?
--   GROUP BY c.id, c.full_name
--   ORDER BY votes DESC, c.full_name ASC;
