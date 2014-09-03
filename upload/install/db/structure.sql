-- --------------------------------------------------------
-- Please note:
-- The table prefix (cat_) will be replaced by the
-- installer! Do NOT use this file to create the tables
-- manually! (Or patch it to fit your needs first.)
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;


DROP TABLE IF EXISTS `cat_addons`;
DROP TABLE IF EXISTS `cat_user_has_group`;
DROP TABLE IF EXISTS `cat_groups`;
DROP TABLE IF EXISTS `cat_mimetypes`;
DROP TABLE IF EXISTS `cat_mod_captcha_control`;
DROP TABLE IF EXISTS `cat_mod_droplets_permissions`;
DROP TABLE IF EXISTS `cat_mod_droplets_settings`;
DROP TABLE IF EXISTS `cat_mod_droplets`;
DROP TABLE IF EXISTS `cat_mod_initial_page`;
DROP TABLE IF EXISTS `cat_mod_menu_link`;
DROP TABLE IF EXISTS `cat_mod_wrapper`;
DROP TABLE IF EXISTS `cat_mod_wysiwyg`;
DROP TABLE IF EXISTS `cat_mod_wysiwyg_admin`;
DROP TABLE IF EXISTS `cat_pages_load`;
DROP TABLE IF EXISTS `cat_page_langs`;
DROP TABLE IF EXISTS `cat_pages_settings`;
DROP TABLE IF EXISTS `cat_pages`;
DROP TABLE IF EXISTS `cat_search`;
DROP TABLE IF EXISTS `cat_sections`;
DROP TABLE IF EXISTS `cat_settings`;
DROP TABLE IF EXISTS `cat_system_permissions`;
DROP TABLE IF EXISTS `cat_users`;
DROP TABLE IF EXISTS `cat_users_options`;
DROP TABLE IF EXISTS `cat_class_secure`;
DROP TABLE IF EXISTS `cat_mod_wysiwyg_admin_v2`;
DROP TABLE IF EXISTS `cat_mod_droplets_extension`;

CREATE TABLE IF NOT EXISTS `cat_addons` (
  `addon_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(128) NOT NULL DEFAULT '',
  `directory` varchar(128) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NULL,
  `function` varchar(255) NOT NULL DEFAULT '',
  `version` varchar(255) NOT NULL DEFAULT '',
  `guid` varchar(50) NOT NULL DEFAULT '',
  `platform` varchar(255) NOT NULL DEFAULT '',
  `author` varchar(255) NOT NULL DEFAULT '',
  `license` varchar(255) NOT NULL DEFAULT '',
  `installed` VARCHAR(255) NOT NULL DEFAULT '',
  `upgraded` VARCHAR(255) NOT NULL DEFAULT '',
  `removable` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `bundled` ENUM('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`addon_id`),
  UNIQUE INDEX `type_directory` (`type`,`directory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_groups` (
  `group_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `system_permissions` text NOT NULL,
  `module_permissions` text NOT NULL,
  `template_permissions` text NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mimetypes` (
  `mime_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mime_type` varchar(50) NOT NULL,
  `mime_suffixes` text,
  `mime_label` varchar(50) DEFAULT NULL,
  `mime_icon` varchar(50) DEFAULT NULL,
  `mime_allowed_for` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`mime_id`),
  KEY `mime_id` (`mime_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_captcha_control` (
  `enabled_captcha` varchar(1) NOT NULL DEFAULT '1',
  `enabled_asp` varchar(1) NOT NULL DEFAULT '0',
  `captcha_type` varchar(255) NOT NULL DEFAULT 'calc_text',
  `asp_session_min_age` int(11) NOT NULL DEFAULT '20',
  `asp_view_min_age` int(11) NOT NULL DEFAULT '10',
  `asp_input_min_age` int(11) NOT NULL DEFAULT '5',
  `ct_text` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_droplets_permissions` (
  `id` int(10) unsigned NOT NULL,
  `edit_groups` varchar(50) NOT NULL,
  `view_groups` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_droplets_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute` varchar(50) NOT NULL DEFAULT '0',
  `value` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cat_mod_droplets` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(32) NOT NULL,
	`code` LONGTEXT NOT NULL,
	`description` TEXT NOT NULL,
	`modified_when` INT(11) NOT NULL DEFAULT '0',
	`modified_by` INT(11) NOT NULL DEFAULT '0',
	`active` INT(11) NOT NULL DEFAULT '1',
	`admin_edit` INT(11) NOT NULL DEFAULT '1',
	`admin_view` INT(11) NOT NULL DEFAULT '1',
	`show_wysiwyg` INT(11) NOT NULL DEFAULT '1',
	`comments` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_menu_link` (
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `target_page_id` int(11) NOT NULL DEFAULT '0',
  `redirect_type` int(11) NOT NULL DEFAULT '302',
  `anchor` varchar(255) NOT NULL DEFAULT '0',
  `extern` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_wrapper` (
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `url` text NOT NULL,
  `height` int(11) NOT NULL DEFAULT '0',
  `width` int(11) NOT NULL DEFAULT '0',
  `wtype` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_wysiwyg` (
  `section_id` int(11) NOT NULL DEFAULT '0',
  `page_id` int(11) NOT NULL DEFAULT '0',
  `content` longtext NOT NULL,
  `text` longtext NOT NULL,
  PRIMARY KEY (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL DEFAULT '0',
  `root_parent` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  `link` text NOT NULL,
  `target` varchar(7) NOT NULL DEFAULT '',
  `page_title` varchar(255) NOT NULL DEFAULT '',
  `menu_title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `keywords` text NOT NULL,
  `page_trail` text NOT NULL,
  `template` varchar(255) NOT NULL DEFAULT '',
  `visibility` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `menu` int(11) NOT NULL DEFAULT '0',
  `language` varchar(5) NOT NULL DEFAULT '',
  `searching` int(11) NOT NULL DEFAULT '0',
  `admin_groups` text NOT NULL,
  `admin_users` text NOT NULL,
  `viewing_groups` text NOT NULL,
  `viewing_users` text NOT NULL,
  `modified_when` int(11) NOT NULL DEFAULT '0',
  `modified_by` int(11) NOT NULL DEFAULT '0',
  `page_groups` tinytext,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cat_pages_settings` (
	`page_id` INT(11) NOT NULL,
	`set_type` ENUM('internal','meta') NOT NULL DEFAULT 'internal',
	`set_name` VARCHAR(50) NOT NULL,
	`set_value` TINYTEXT NOT NULL,
	UNIQUE INDEX `set_type_set_name` (`page_id`, `set_type`, `set_name`),
	CONSTRAINT `page_id` FOREIGN KEY (`page_id`) REFERENCES `cat_pages` (`page_id`)
) COMMENT='Additional settings for pages' ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_page_langs` (
  `page_id` int(10) NOT NULL,
  `lang` char(2) NOT NULL,
  `link_page_id` int(10) NOT NULL,
  UNIQUE KEY `page_id_lang_link_page_id` (`page_id`,`lang`,`link_page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Links pages of different languages together';

CREATE TABLE `cat_pages_headers` (
	`page_id` INT(11) NOT NULL,
	`page_js_files` TEXT NULL,
	`page_css_files` TEXT NULL,
	`page_js` TEXT NULL,
	UNIQUE INDEX `page_id` (`page_id`)
) COMMENT='header files' ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_search` (
  `search_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  `extra` text NULL,
  PRIMARY KEY (`search_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cat_sections` (
	`section_id` INT(11) NOT NULL AUTO_INCREMENT,
	`page_id` INT(11) NOT NULL DEFAULT '0',
	`position` INT(11) NOT NULL DEFAULT '0',
	`module` VARCHAR(255) NOT NULL DEFAULT '',
	`block` VARCHAR(255) NOT NULL DEFAULT '',
	`publ_start` VARCHAR(255) NOT NULL DEFAULT '0',
	`publ_end` VARCHAR(255) NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL DEFAULT 'no name',
	`modified_when` INT(11) NOT NULL DEFAULT '0',
	`modified_by` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_system_permissions` (
	`perm_group` VARCHAR(50) NOT NULL,
	`perm_name` VARCHAR(50) NOT NULL,
	`perm_bit` INT(11) NOT NULL,
	`perm_for` ENUM('FE','BE') NOT NULL DEFAULT 'BE',
	`perm_comment` VARCHAR(50) NULL DEFAULT NULL
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cat_users` (
  `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '0',
  `groups_id` varchar(255) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '0',
  `statusflags` int(11) NOT NULL DEFAULT '6',
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `last_reset` int(11) NOT NULL DEFAULT '0',
  `display_name` varchar(255) NOT NULL DEFAULT '',
  `email` text NOT NULL,
  `language` varchar(5) NOT NULL DEFAULT 'DE',
  `home_folder` varchar(255) NOT NULL DEFAULT '',
  `login_when` int(11) NOT NULL DEFAULT '0',
  `login_ip` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_users_options` (
	`user_id` INT(10) NOT NULL,
	`option_name` VARCHAR(50) NOT NULL,
	`option_value` VARCHAR(255) NOT NULL
)
COMMENT='additional expandable settings for users' ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_mod_initial_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '1',
  `init_page` text NOT NULL,
  `page_param` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cat_class_secure` (
	`module` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`filepath` VARCHAR(255) NOT NULL DEFAULT '0',
	UNIQUE INDEX `module_filepath` (`module`, `filepath`)
)
COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cat_mod_wysiwyg_admin_v2` (
	`editor` VARCHAR(50) NOT NULL,
	`set_name` VARCHAR(50) NOT NULL,
	`set_value` TEXT NOT NULL,
	UNIQUE INDEX `editor_set_name` (`editor`, `set_name`)
)
COMMENT='WYSIWYG Admin for Black Cat CMS'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `cat_user_has_group` (
	`user_id` INT(11) UNSIGNED NOT NULL,
	`group_id` INT(11) UNSIGNED NOT NULL,
	INDEX `FK_cat_user_has_group_cat_users` (`user_id`),
	INDEX `FK_cat_user_has_group_cat_groups` (`group_id`),
	CONSTRAINT `FK_cat_user_has_group_cat_groups` FOREIGN KEY (`group_id`) REFERENCES `cat_groups` (`group_id`),
	CONSTRAINT `FK_cat_user_has_group_cat_users` FOREIGN KEY (`user_id`) REFERENCES `cat_users` (`user_id`)
)
COMMENT='Maps users to groups'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `cat_mod_droplets_extension` (
	`drop_id` INT(11) NOT NULL AUTO_INCREMENT,
	`drop_droplet_name` VARCHAR(255) NOT NULL DEFAULT '',
	`drop_page_id` INT(11) NOT NULL DEFAULT '-1',
	`drop_module_dir` VARCHAR(255) NOT NULL DEFAULT '',
	`drop_type` VARCHAR(20) NOT NULL DEFAULT 'undefined',
	`drop_file` VARCHAR(255) NOT NULL DEFAULT '',
	`drop_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`drop_id`)
);


/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
