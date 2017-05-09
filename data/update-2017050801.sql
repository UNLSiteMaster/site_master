-- -----------------------------------------------------
-- Table `scanned_page_analytics`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `scanned_page_analytics` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `scanned_page_id` INT NOT NULL,
  `data_type` ENUM('ELEMENT', 'CLASS', 'ATTRIBUTE', 'SELECTOR') NOT NULL,
  `data_key` VARCHAR(256) NOT NULL COMMENT 'the key found, often the element, class, or attribute name',
  `data_value` VARCHAR(512) COMMENT 'the value found in the data, often the attribute value',
  `num_instances` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_page_analytics_scanned_page1_idx` (`scanned_page_id` ASC),
  INDEX `scanned_page_analytics` (`data_type` ASC, `data_key` ASC, `data_value` ASC, `scanned_page_id` ASC),
  CONSTRAINT `fk_page_analytics_scanned_page1`
  FOREIGN KEY (`scanned_page_id`)
  REFERENCES `scanned_page` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
  ENGINE = InnoDB;
