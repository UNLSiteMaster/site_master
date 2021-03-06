-- -----------------------------------------------------
-- Table `overrides`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `overrides` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sites_id` INT NOT NULL,
  `users_id` INT NOT NULL,
  `date_created` DATETIME NOT NULL,
  `marks_id` INT NOT NULL,
  `scope` ENUM('SITE', 'PAGE', 'ELEMENT') NOT NULL DEFAULT 'ELEMENT',
  `url` VARCHAR(2100) NULL,
  `context` TEXT NULL,
  `line` INT NULL,
  `col` INT NULL,
  `value_found` TEXT NULL,
  `expires` DATETIME NULL,
  `reason` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `indx_overrides` (`sites_id` ASC, `marks_id` ASC, `expires` ASC),
  CONSTRAINT `fk_overrides_sites`
  FOREIGN KEY (`sites_id`)
  REFERENCES `sites` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_overrides_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_overrides_marks_id`
    FOREIGN KEY (`marks_id`)
    REFERENCES `marks` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
CHARACTER SET utf8 COLLATE utf8_bin
ENGINE = InnoDB;

ALTER TABLE marks ADD allow_perm_override ENUM('YES', 'NO') NOT NULL DEFAULT 'NO';
