-- -----------------------------------------------------
-- Table `group_scan_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `group_scan_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(256) NOT NULL,
  `gpa` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `date_created` DATETIME NOT NULL,
  `total_pages` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_group_scan_history_group_idx` (`group_name` ASC, `date_created` ASC)
)
  ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `group_scan_metric_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `group_scan_metric_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `metrics_id` INT NOT NULL,
  `gpa` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `group_scan_history_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_group_scan_metric_history_metrics1_idx` (`metrics_id` ASC),
  INDEX `fk_group_scan_metric_history_group_scan_history1_idx` (`group_scan_history_id` ASC),
  CONSTRAINT `fk_group_scan_metric_history_metrics1`
  FOREIGN KEY (`metrics_id`)
  REFERENCES `metrics` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_group_scan_metric_history_group_scan_history1`
  FOREIGN KEY (`group_scan_history_id`)
  REFERENCES `group_scan_history` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
)
  ENGINE = InnoDB;
