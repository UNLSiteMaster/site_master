SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'The Internal ID for the user' ,
  `uid` VARCHAR(255) NOT NULL COMMENT 'An user identifier unique to the given provider. ' ,
  `provider` VARCHAR(45) NOT NULL COMMENT 'The provider with which the user authenticated (e.g. \'Twitter\' or \'Facebook\')' ,
  `email` VARCHAR(255) NULL ,
  `first_name` VARCHAR(45) NULL ,
  `last_name` VARCHAR(45) NULL ,
  `role` ENUM('ADMIN', 'USER') NULL DEFAULT 'USER' COMMENT 'The user\'s role for the system.  Either ADMIN or USER.' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
