-- **************************************************************************
-- This script creates the database for Another Recipe Site
-- Robert Uhren
-- WEB-289
-- February 15, 2026
-- Modify lines 317-319 to your preferred user credentials
-- **************************************************************************

-- create the database (consider renaming database to fit your preferences)
DROP DATABASE IF EXISTS recipedb;
CREATE DATABASE recipedb;

-- select the database
USE recipedb;

-- create the tables
CREATE TABLE `image_img` (
  `id_img` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `file_name_img` VARCHAR(255) NOT NULL,
  `created_at_img` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `user_usr` (
  `id_usr` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `username_usr` VARCHAR(32) UNIQUE NOT NULL,
  `email_usr` VARCHAR(255) UNIQUE NOT NULL,
  `password_hash_usr` VARCHAR(255) NOT NULL,
  `status_usr` ENUM ('pending', 'active', 'disabled') NOT NULL DEFAULT 'pending',
  `id_img_usr` INT UNSIGNED DEFAULT NULL,
  `created_at_usr` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_usr` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_img_usr` FOREIGN KEY (`id_img_usr`) REFERENCES `image_img` (`id_img`) ON DELETE SET NULL,
  INDEX `idx_status_usr` (`status_usr`)
);

CREATE TABLE `role_rol` (
  `id_rol` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name_rol` VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE `user_role_usrrol` (
  `id_usrrol` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_usr_usrrol` INT UNSIGNED NOT NULL,
  `id_rol_usrrol` INT UNSIGNED NOT NULL,
  CONSTRAINT `uq_usr_rol_usrrol` UNIQUE (`id_usr_usrrol`, `id_rol_usrrol`),
  CONSTRAINT `fk_usr_usrrol` FOREIGN KEY (`id_usr_usrrol`) REFERENCES `user_usr` (`id_usr`) ON DELETE CASCADE,
  CONSTRAINT `fk_rol_usrrol` FOREIGN KEY (`id_rol_usrrol`) REFERENCES `role_rol` (`id_rol`),
  INDEX `idx_rol_usrrol` (`id_rol_usrrol`)
);

CREATE TABLE `badge_bdg` (
  `id_bdg` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name_bdg` VARCHAR(25) UNIQUE NOT NULL
);

CREATE TABLE `recipe_rcp` (
  `id_rcp` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_usr_rcp` INT UNSIGNED NOT NULL,
  `title_rcp` VARCHAR(255) NOT NULL,
  `description_rcp` VARCHAR(255),
  `serving_rcp` DECIMAL(6,2) UNSIGNED,
  `id_bdg_rcp` INT UNSIGNED,
  `privacy_rcp` ENUM ('public', 'unlisted', 'private') NOT NULL DEFAULT 'public',
  `prep_time_minutes_rcp` INT UNSIGNED NOT NULL DEFAULT 0,
  `cook_time_minutes_rcp` INT UNSIGNED NOT NULL DEFAULT 0,
  `youtube_url_rcp` VARCHAR(500),
  `created_at_rcp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_rcp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_usr_rcp` FOREIGN KEY (`id_usr_rcp`) REFERENCES `user_usr` (`id_usr`),
  CONSTRAINT `fk_bdg_rcp` FOREIGN KEY (`id_bdg_rcp`) REFERENCES `badge_bdg` (`id_bdg`) ON DELETE SET NULL,
  INDEX `idx_usr_rcp` (`id_usr_rcp`), 
  INDEX `idx_bdg_rcp` (`id_bdg_rcp`)
);

CREATE TABLE `user_save_recipe_usrrcp` (
  `id_usrrcp` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_usr_usrrcp` INT UNSIGNED NOT NULL,
  `id_rcp_usrrcp` INT UNSIGNED NOT NULL,
  CONSTRAINT `uq_usr_rcp_usrrcp` UNIQUE (`id_usr_usrrcp`, `id_rcp_usrrcp`),
  CONSTRAINT `fk_usr_usrrcp` FOREIGN KEY (`id_usr_usrrcp`) REFERENCES `user_usr` (`id_usr`) ON DELETE CASCADE,
  CONSTRAINT `fk_rcp_usrrcp` FOREIGN KEY (`id_rcp_usrrcp`) REFERENCES `recipe_rcp` (`id_rcp`) ON DELETE CASCADE,
  INDEX `idx_rcp_usrrcp` (`id_rcp_usrrcp`)
);

CREATE TABLE `rating_rtg` (
  `id_rtg` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_rcp_rtg` INT UNSIGNED NOT NULL,
  `id_usr_rtg` INT UNSIGNED NOT NULL,
  `rating_rtg` TINYINT UNSIGNED NOT NULL,
  `created_at_rtg` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at_rtg` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
  CONSTRAINT `uq_rtg_rcp_usr` UNIQUE (`id_rcp_rtg`, `id_usr_rtg`),
  CONSTRAINT `chk_rating_1_5` CHECK (`rating_rtg` BETWEEN 1 AND 5),
  CONSTRAINT `fk_rcp_rtg` FOREIGN KEY (`id_rcp_rtg`) REFERENCES `recipe_rcp` (`id_rcp`) ON DELETE CASCADE,
  CONSTRAINT `fk_usr_rtg` FOREIGN KEY (`id_usr_rtg`) REFERENCES `user_usr` (`id_usr`) ON DELETE CASCADE, 
  INDEX `idx_rcp_rtg` (`id_rcp_rtg`),
  INDEX `idx_usr_rtg` (`id_usr_rtg`)
);

CREATE TABLE `meal_type_mty` (
  `id_mty` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name_mty` VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE `recipe_meal_type_rcpmty` (
  `id_rcpmty` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_rcp_rcpmty` INT UNSIGNED NOT NULL,
  `id_mty_rcpmty` INT UNSIGNED NOT NULL,
  CONSTRAINT `uq_rcp_mty_rcpmty` UNIQUE (`id_rcp_rcpmty`, `id_mty_rcpmty`),
  CONSTRAINT `fk_rcp_rcpmty` FOREIGN KEY (`id_rcp_rcpmty`) REFERENCES `recipe_rcp` (`id_rcp`) ON DELETE CASCADE,
  CONSTRAINT `fk_mty_rcpmty` FOREIGN KEY (`id_mty_rcpmty`) REFERENCES `meal_type_mty` (`id_mty`),
  INDEX `idx_mty_rcpmty` (`id_mty_rcpmty`)
);

CREATE TABLE `cuisine_csn` (
  `id_csn` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name_csn` VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE `recipe_cuisine_rcpcsn` (
  `id_rcpcsn` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_rcp_rcpcsn` INT UNSIGNED NOT NULL,
  `id_csn_rcpcsn` INT UNSIGNED NOT NULL,
  CONSTRAINT `uq_rcp_csn_rcpcsn` UNIQUE (`id_rcp_rcpcsn`, `id_csn_rcpcsn`),
  CONSTRAINT `fk_rcp_rcpcsn` FOREIGN KEY (`id_rcp_rcpcsn`) REFERENCES `recipe_rcp` (`id_rcp`) ON DELETE CASCADE,
  CONSTRAINT `fk_csn_rcpcsn` FOREIGN KEY (`id_csn_rcpcsn`) REFERENCES `cuisine_csn` (`id_csn`),
  INDEX `idx_csn_rcpcsn` (`id_csn_rcpcsn`)
);

CREATE TABLE `dietary_style_dst` (
  `id_dst` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name_dst` VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE `recipe_dietary_style_rcpdst` (
  `id_rcpdst` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_rcp_rcpdst` INT UNSIGNED NOT NULL,
  `id_dst_rcpdst` INT UNSIGNED NOT NULL,
  CONSTRAINT `uq_rcp_dst_rcpdst` UNIQUE (`id_rcp_rcpdst`, `id_dst_rcpdst`),
  CONSTRAINT `fk_rcp_rcpdst` FOREIGN KEY (`id_rcp_rcpdst`) REFERENCES `recipe_rcp` (`id_rcp`) ON DELETE CASCADE,
  CONSTRAINT `fk_dst_rcpdst` FOREIGN KEY (`id_dst_rcpdst`) REFERENCES `dietary_style_dst` (`id_dst`),
  INDEX `idx_dst_rcpdst` (`id_dst_rcpdst`)
);

CREATE TABLE `direction_dir` (
  `id_dir` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_rcp_dir` INT UNSIGNED NOT NULL,
  `step_dir` TINYINT UNSIGNED NOT NULL,
  `instruction_dir` TEXT NOT NULL,
  CONSTRAINT `uq_rcp_step_dir` UNIQUE (`id_rcp_dir`, `step_dir`),
  CONSTRAINT `fk_rcp_dir` FOREIGN KEY (`id_rcp_dir`) REFERENCES `recipe_rcp` (`id_rcp`) ON DELETE CASCADE
);

CREATE TABLE `measurement_mes` (
  `id_mes` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name_mes` VARCHAR(100) UNIQUE NOT NULL,
  `abbr_mes` VARCHAR(10) UNIQUE NOT NULL,
  `unit_type_mes` ENUM ('mass', 'volume', 'count') NOT NULL
);

CREATE TABLE `ingredient_ing` (
  `id_ing` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `name_ing` VARCHAR(255) UNIQUE NOT NULL,
  `created_at_ing` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `recipe_ingredient_rcping` (
  `id_rcping` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_rcp_rcping` INT UNSIGNED NOT NULL,
  `id_ing_rcping` INT UNSIGNED NOT NULL,
  `quantity_rcping` DECIMAL(8,2) UNSIGNED NOT NULL,
  `id_mes_rcping` INT UNSIGNED NOT NULL,
  CONSTRAINT `fk_rcp_rcping` FOREIGN KEY (`id_rcp_rcping`) REFERENCES `recipe_rcp` (`id_rcp`) ON DELETE CASCADE,
  CONSTRAINT `fk_ing_rcping` FOREIGN KEY (`id_ing_rcping`) REFERENCES `ingredient_ing` (`id_ing`),
  CONSTRAINT `fk_mes_rcping` FOREIGN KEY (`id_mes_rcping`) REFERENCES `measurement_mes` (`id_mes`),
  INDEX `idx_rcp_rcping` (`id_rcp_rcping`),
  INDEX `idx_ing_rcping` (`id_ing_rcping`),
  INDEX `idx_mes_rcping` (`id_mes_rcping`)
);

CREATE TABLE `recipe_image_rcpimg` (
  `id_rcpimg` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_rcp_rcpimg` INT UNSIGNED NOT NULL,
  `id_img_rcpimg` INT UNSIGNED NOT NULL,
  CONSTRAINT `uq_rcp_img_rcpimg` UNIQUE (`id_rcp_rcpimg`, `id_img_rcpimg`),
  CONSTRAINT `fk_rcp_rcpimg` FOREIGN KEY (`id_rcp_rcpimg`) REFERENCES `recipe_rcp` (`id_rcp`) ON DELETE CASCADE,
  CONSTRAINT `fk_img_rcpimg` FOREIGN KEY (`id_img_rcpimg`) REFERENCES `image_img` (`id_img`) ON DELETE CASCADE,
  INDEX `idx_img_rcpimg` (`id_img_rcpimg`)
);

-- insert roles
INSERT INTO `role_rol` (`name_rol`) VALUES 
('member'),
('admin'),
('super admin');

-- insert measurements
INSERT INTO `measurement_mes` (`name_mes`, `abbr_mes`, `unit_type_mes`) VALUES
-- volume
('teaspoon', 'tsp', 'volume'),
('tablespoon', 'tbsp', 'volume'),
('fluid ounce', 'fl oz', 'volume'),
('cup', 'cup', 'volume'),
('pint', 'pt', 'volume'),
('quart', 'qt', 'volume'),
('gallon', 'gal', 'volume'),
('milliliter', 'ml', 'volume'),
('liter', 'l', 'volume'),
-- mass
('ounce', 'oz', 'mass'),
('pound', 'lb', 'mass'),
('milligram', 'mg', 'mass'),
('gram', 'g', 'mass'),
('kilogram', 'kg', 'mass'),
-- count
('count', 'ct', 'count'),
('each', 'ea', 'count'),
('piece', 'pc', 'count'),
('clove', 'clove', 'count'),
('slice', 'slice', 'count'),
('can', 'can', 'count'),
('package', 'pkg', 'count'),
('bunch', 'bunch', 'count');

-- insert meal types
INSERT INTO `meal_type_mty` (`name_mty`) VALUES
('breakfast'),
('brunch'),
('lunch'),
('dinner'),
('dessert'),
('snack'),
('appetizer'),
('side dish'),
('main course'),
('soup'),
('salad'),
('condiment'),
('beverage');

-- insert cuisines
INSERT INTO `cuisine_csn` (`name_csn`) VALUES 
('american'),
('italian'),
('mexican'),
('chinese'),
('japanese'),
('korean'),
('thai'),
('vietnamese'),
('indian'),
('mediterranean'),
('greek'),
('french'),
('spanish'),
('german'),
('british'),
('caribbean'),
('brazilian'),
('peruvian'),
('middle eastern'),
('persian'),
('african'),
('moroccan'),
('ethiopian'),
('cajun'),
('tex-mex'),
('fusion');

-- insert dietary styles
INSERT INTO `dietary_style_dst` (`name_dst`) VALUES 
('vegetarian'),
('vegan'),
('pescatarian'),
('keto'),
('paleo'),
('low-carb'),
('high-protein'),
('low-fat'),
('gluten-free'),
('dairy-free'),
('low-sodium'),
('whole30'),
('mediterranean diet'),
('dash diet'),
('low-fodmap');

-- insert badges
INSERT INTO `badge_bdg` (`name_bdg`) VALUES
-- popularity
('most saved'),
('trending'),
('community favorite'),
('top rated'),
('staff pick'),
-- speed / convenience
('30-minute meal'),
('quick & easy'),
('one-pot'),
('meal prep friendly'),
('beginner friendly'),
-- dietary
('high protein'),
('low carb'),
('vegetarian'),
('vegan'),
('gluten-free'),
('dairy-free'),
-- occasion
('holiday favorite'),
('game day'),
('comfort food'),
('kid friendly'),
('date night');

-- setup user
DROP USER IF EXISTS 'recipeuser';
CREATE USER 'recipeuser' IDENTIFIED BY 'P@ssw0rd123!';
GRANT SELECT, INSERT, UPDATE, DELETE ON recipedb.* TO 'recipeuser'@'%';
FLUSH PRIVILEGES;