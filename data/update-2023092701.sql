ALTER TABLE `roles` ADD COLUMN `max_number_per_site` INT DEFAULT NULL AFTER `protected`;
ALTER TABLE `roles` ADD COLUMN `distinct_from` INT DEFAULT NULL AFTER `max_number_per_site`;