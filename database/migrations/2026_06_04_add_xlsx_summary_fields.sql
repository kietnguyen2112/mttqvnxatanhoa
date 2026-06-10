ALTER TABLE `organization_chapters`
  ADD COLUMN `household_count` int unsigned NOT NULL DEFAULT 0 AFTER `name`,
  ADD COLUMN `male_count` int unsigned NOT NULL DEFAULT 0 AFTER `member_count`,
  ADD COLUMN `female_count` int unsigned NOT NULL DEFAULT 0 AFTER `male_count`;

ALTER TABLE `loan_groups`
  ADD COLUMN `customer_count` int unsigned NOT NULL DEFAULT 0 AFTER `leader_phone`,
  ADD COLUMN `outstanding_amount` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `fund_source`,
  ADD COLUMN `savings_amount` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `outstanding_amount`,
  ADD COLUMN `rating` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT '' AFTER `overdue_amount`;
