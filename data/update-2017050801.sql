-- -----------------------------------------------------
-- Table `feature_analytics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `feature_analytics` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `unique_hash` VARCHAR(32),
  `data_type` ENUM('ELEMENT', 'CLASS', 'ATTRIBUTE', 'SELECTOR') NOT NULL,
  `data_key` VARCHAR(512) NOT NULL COMMENT 'the key found, often the element, class, or attribute name',
  `data_value` VARCHAR(1024) NOT NULL COMMENT 'the value found in the data, often the attribute value',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feature_analytics_hash` (`unique_hash`),
  INDEX `feature_analytics` (`data_type` ASC, `data_key` ASC, `data_value`(256) ASC))
  ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `scanned_page_has_feature_analytics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `scanned_page_has_feature_analytics` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `scanned_page_id` INT NOT NULL,
  `feature_analytics_id` INT UNSIGNED NOT NULL,
  `num_instances` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_page_analytics_scanned_page1_idx` (`scanned_page_id` ASC),
  CONSTRAINT `fk_scanned_page_has_feature_analytics1`
  FOREIGN KEY (`scanned_page_id`)
  REFERENCES `scanned_page` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;