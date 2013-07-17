SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `MeetRocket` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `MeetRocket` ;

-- -----------------------------------------------------
-- Table `MeetRocket`.`user_settings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_settings` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NULL ,
  `name` VARCHAR(45) NULL ,
  `value` VARCHAR(150) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`addresses`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`addresses` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `city_id` INT NULL ,
  `country_id` INT NULL ,
  `x` FLOAT NULL ,
  `y` FLOAT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`user_photos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_photos` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NULL ,
  `thumb` VARCHAR(150) NULL ,
  `orig` VARCHAR(150) NULL ,
  `user_id` BIGINT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_photos_Users1_idx` (`user_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`industries`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`industries` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`users` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(150) NULL ,
  `name` VARCHAR(150) NULL ,
  `lastname` VARCHAR(150) NULL ,
  `industry_id` INT NULL ,
  `summary` VARCHAR(500) NULL ,
  `photo_id` BIGINT NULL ,
  `phone` VARCHAR(16) NULL ,
  `business_email` VARCHAR(150) NULL ,
  `skype` VARCHAR(150) NULL ,
  `address_id` BIGINT NULL ,
  `rating` FLOAT NULL ,
  `experience` FLOAT NULL ,
  `facebook_key` VARCHAR(45) NULL ,
  `linkedin_key` VARCHAR(45) NULL ,
  `private_key` VARCHAR(45) NULL ,
  `update` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_Users_addresses1_idx` (`address_id` ASC) ,
  INDEX `fk_Users_user_photos1_idx` (`photo_id` ASC) ,
  INDEX `fk_users_industries1_idx` (`industry_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`goals`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`goals` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`user_goals_has_goals`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_goals_has_goals` (
  `user_goals_id` INT NOT NULL ,
  `goals_id` INT NOT NULL ,
  PRIMARY KEY (`user_goals_id`, `goals_id`) ,
  INDEX `fk_user_goals_has_goals_goals1_idx` (`goals_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`user_goals`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_goals` (
  `goals_id` INT NOT NULL ,
  `users_id` BIGINT NOT NULL ,
  PRIMARY KEY (`goals_id`, `users_id`) ,
  INDEX `fk_goals_has_Users_Users1_idx` (`users_id` ASC) ,
  INDEX `fk_goals_has_Users_goals1_idx` (`goals_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`skills`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`skills` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`user_skills`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_skills` (
  `users_id` BIGINT NOT NULL ,
  `skills_id` INT NOT NULL ,
  PRIMARY KEY (`users_id`, `skills_id`) ,
  INDEX `fk_Users_has_skills_skills1_idx` (`skills_id` ASC) ,
  INDEX `fk_Users_has_skills_Users1_idx` (`users_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`countries`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`countries` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`cities`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`cities` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NULL ,
  `country_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_cities_countries1_idx` (`country_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`languages` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`user_languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_languages` (
  `languages_id` INT NOT NULL ,
  `users_id` BIGINT NOT NULL ,
  PRIMARY KEY (`languages_id`, `users_id`) ,
  INDEX `fk_languages_has_Users_Users1_idx` (`users_id` ASC) ,
  INDEX `fk_languages_has_Users_languages1_idx` (`languages_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`user_languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_languages` (
  `languages_id` INT NOT NULL ,
  `users_id` BIGINT NOT NULL ,
  PRIMARY KEY (`languages_id`, `users_id`) ,
  INDEX `fk_languages_has_Users_Users1_idx` (`users_id` ASC) ,
  INDEX `fk_languages_has_Users_languages1_idx` (`languages_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`users_has_industries`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`users_has_industries` (
  `users_id` BIGINT NOT NULL ,
  `industries_id` INT NOT NULL ,
  PRIMARY KEY (`users_id`, `industries_id`) ,
  INDEX `fk_users_has_industries_industries1_idx` (`industries_id` ASC) ,
  INDEX `fk_users_has_industries_users1_idx` (`users_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`user_contacts_wait`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_contacts_wait` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(150) NULL ,
  `user_id` BIGINT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_contacts_users1_idx` (`user_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`user_jobs`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_jobs` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NULL ,
  `name` VARCHAR(45) NULL ,
  `description` VARCHAR(45) NULL ,
  `users_id` BIGINT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_jobs_users1_idx` (`users_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`user_friends`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`user_friends` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NULL ,
  `friend_id` BIGINT NULL ,
  `status` TINYINT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_user_friends_users1_idx` (`user_id` ASC) ,
  INDEX `fk_user_friends_users2_idx` (`friend_id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`notifications`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`notifications` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `from` BIGINT NULL ,
  `to` BIGINT NULL ,
  `type` TINYINT NULL ,
  `text` VARCHAR(150) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_notifications_users1_idx` (`from` ASC) ,
  INDEX `fk_notifications_users2_idx` (`to` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`meetings`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`meetings` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id_first` BIGINT NULL ,
  `user_id_second` BIGINT NULL ,
  `rating` FLOAT NULL ,
  `status` TINYINT NULL ,
  `address_id` BIGINT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_meetings_addresses1_idx` (`address_id` ASC) ,
  INDEX `fk_meetings_users1_idx` (`user_id_first` ASC) ,
  INDEX `fk_meetings_users2_idx` (`user_id_second` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`complaints`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`complaints` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `type` TINYINT NULL ,
  `from` BIGINT NULL ,
  `to` BIGINT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_complaints_users1_idx` (`from` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`chat`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`chat` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `when` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  `from` BIGINT NULL ,
  `to` BIGINT NULL ,
  `text` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_chat_users1_idx` (`to` ASC) ,
  INDEX `fk_chat_users2_idx` (`from` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `MeetRocket`.`calendar`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `MeetRocket`.`calendar` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `user_id` BIGINT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_calendar_users1_idx` (`user_id` ASC) )
ENGINE = InnoDB;

USE `MeetRocket` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
