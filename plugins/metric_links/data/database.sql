SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `metric_links_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `metric_links_status` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `url_hash` VARCHAR(32) NOT NULL COMMENT 'the md5 of the URL',
  `date_created` DATETIME NOT NULL COMMENT 'The date of the last check',
  `http_code` INT(4) NULL COMMENT 'The http code',
  `curl_code` INT(4) NULL COMMENT 'the curl code',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `url_UNIQUE` (`url_hash` ASC))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
