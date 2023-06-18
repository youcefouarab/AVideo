CREATE TABLE IF NOT EXISTS `CachesInDB` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `created` DATETIME NULL,
  `modified` DATETIME NULL,
  `content` BLOB NULL,
  `domain` VARCHAR(255) NULL,
  `ishttps` TINYINT NULL,
  `loggedType` ENUM('n', 'l', 'a') NULL DEFAULT 'n' COMMENT 'n=not logged\nl=logged\na=admin',
  `user_location` VARCHAR(255) NULL,
  `expires` DATETIME NULL,
  `timezone` VARCHAR(255) NULL,
  `name` VARCHAR(500) NULL,
  PRIMARY KEY (`id`),
  INDEX `cacheds1` (`domain` ASC),
  INDEX `caches2` (`ishttps` ASC),
  INDEX `caches3` (`loggedType` ASC),
  INDEX `caches4` (`user_location` ASC),
  INDEX `caches5` (`created` ASC),
  INDEX `caches6` (`modified` ASC),
  INDEX `caches7` (`expires` ASC),
  INDEX `caches8` (`timezone` ASC),
  INDEX `caches9` (`name` ASC))
ENGINE = InnoDB;
ALTER TABLE CachesInDB ADD FULLTEXT(name);