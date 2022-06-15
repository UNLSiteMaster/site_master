-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 02, 2022 at 10:56 AM
-- Server version: 5.5.68-MariaDB
-- PHP Version: 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sitemaster`
--

-- --------------------------------------------------------

--
-- Table structure for table `feature_analytics`
--

CREATE TABLE IF NOT EXISTS `feature_analytics` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `unique_hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_type` enum('ELEMENT','CLASS','ATTRIBUTE','SELECTOR') COLLATE utf8_unicode_ci NOT NULL,
  `data_key` varchar(512) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the key found, often the element, class, or attribute name',
  `data_value` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the value found in the data, often the attribute value',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feature_analytics_hash` (`unique_hash`),
  KEY `feature_analytics` (`data_type`,`data_key`,`data_value`(256))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `group_scan_history`
--

CREATE TABLE IF NOT EXISTS `group_scan_history` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `gpa` decimal(5,2) NOT NULL DEFAULT '0.00',
  `date_created` datetime NOT NULL,
  `total_pages` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_group_scan_history_group_idx` (`group_name`,`date_created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `group_scan_metric_history`
--

CREATE TABLE IF NOT EXISTS `group_scan_metric_history` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `metrics_id` int(11) NOT NULL,
  `gpa` decimal(5,2) NOT NULL DEFAULT '0.00',
  `group_scan_history_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_group_scan_metric_history_metrics1_idx` (`metrics_id`),
  KEY `fk_group_scan_metric_history_group_scan_history1_idx` (`group_scan_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE IF NOT EXISTS `marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metrics_id` int(11) NOT NULL,
  `machine_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Machine readable name of the metric.  IE: 404_link\n\nThis must be unique to the metric.\n\nThe machine_name is how modules can easily retrieve marks.',
  `name` varchar(512) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The name of the mark.  i.e.  "404 Link"',
  `point_deduction` decimal(5,2) NOT NULL DEFAULT '0.00',
  `description` mediumtext COLLATE utf8_unicode_ci COMMENT 'A longer description of the mark and why it was marked',
  `help_text` mediumtext COLLATE utf8_unicode_ci COMMENT 'General ''how to fix'' text',
  `allow_perm_override` enum('YES','NO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`id`),
  UNIQUE KEY `marks_unique` (`metrics_id`,`machine_name`),
  KEY `fk_marks_metrics1_idx` (`metrics_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `metrics`
--

CREATE TABLE IF NOT EXISTS `metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `machine_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the name of the module for the metic.  ie:  metric_wdn_version',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='These are metrics, such as links checks, html validity, acce' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `metric_spelling_wordnik_cache`
--

CREATE TABLE IF NOT EXISTS `metric_spelling_wordnik_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(100) COLLATE utf8_bin NOT NULL,
  `date_created` datetime NOT NULL,
  `result` enum('OKAY','ERROR') COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scan_html_version_index` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `overrides`
--

CREATE TABLE IF NOT EXISTS `overrides` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sites_id` int(11) DEFAULT NULL,
  `users_id` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `marks_id` int(11) NOT NULL,
  `scope` enum('GLOBAL','SITE','PAGE','ELEMENT') COLLATE utf8_bin NOT NULL DEFAULT 'ELEMENT',
  `url` varchar(2100) COLLATE utf8_bin DEFAULT NULL,
  `context` mediumtext COLLATE utf8_bin,
  `line` int(11) DEFAULT NULL,
  `col` int(11) DEFAULT NULL,
  `value_found` mediumtext COLLATE utf8_bin,
  `expires` datetime DEFAULT NULL,
  `reason` mediumtext COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `indx_overrides` (`sites_id`,`marks_id`,`expires`),
  KEY `fk_overrides_users` (`users_id`),
  KEY `fk_overrides_marks_id` (`marks_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `page_marks`
--

CREATE TABLE IF NOT EXISTS `page_marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marks_id` int(11) NOT NULL,
  `scanned_page_id` int(11) NOT NULL,
  `points_deducted` decimal(5,2) NOT NULL DEFAULT '0.00',
  `context` mediumtext COLLATE utf8_bin,
  `line` int(11) DEFAULT NULL,
  `col` int(11) DEFAULT NULL,
  `value_found` mediumtext COLLATE utf8_bin COMMENT 'The incorrect value that was found',
  `help_text` mediumtext COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `fk_page_marks_marks1_idx` (`marks_id`),
  KEY `fk_page_marks_scanned_page1_idx` (`scanned_page_id`),
  KEY `index4` (`value_found`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `page_metric_grades`
--

CREATE TABLE IF NOT EXISTS `page_metric_grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metrics_id` int(11) NOT NULL,
  `scanned_page_id` int(11) NOT NULL,
  `points_available` decimal(5,2) NOT NULL DEFAULT '100.00' COMMENT 'The total points available for this metric',
  `weighted_grade` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'total earned points when the weight is accounted for',
  `point_grade` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'The point grade for this metric.  Overall points gained for page.',
  `changes_since_last_scan` int(11) NOT NULL DEFAULT '0' COMMENT 'The number of changes since the last scan. \n',
  `pass_fail` enum('YES','NO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NO' COMMENT 'Was the grade a pass/fail?',
  `incomplete` enum('YES','NO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NO' COMMENT 'YES if the metric was unable to complete for any reason.  For Example: the html check was unable to get a response from the validator service.',
  `weight` decimal(5,2) NOT NULL DEFAULT '0.00',
  `letter_grade` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `num_errors` int(11) DEFAULT NULL COMMENT 'The total number of errors found',
  `num_notices` int(11) DEFAULT NULL COMMENT 'The total number of notices found',
  PRIMARY KEY (`id`),
  KEY `fk_page_metric_grades_metrics1_idx` (`metrics_id`),
  KEY `fk_page_metric_grades_scanned_page1_idx` (`scanned_page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `protected` enum('YES','NO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NO' COMMENT 'A protected role means that only a manager can assign/approve it',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rolename_UNIQUE` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `scanned_page`
--

CREATE TABLE IF NOT EXISTS `scanned_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scans_id` int(11) NOT NULL,
  `sites_id` int(11) NOT NULL,
  `uri` varchar(2100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The same URI can be found multiple times in a single scan.  A single page can be rescanned instead of the entire site.  Those scans should be able to be compared with each other and should not overwrite history.',
  `uri_hash` binary(16) NOT NULL COMMENT 'md5 hash of the URI for indexing',
  `status` enum('CREATED','QUEUED','RUNNING','COMPLETE','ERROR') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CREATED',
  `scan_type` enum('USER','AUTO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'AUTO',
  `percent_grade` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'This is the percent grade of the page',
  `points_available` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Total available points to earn',
  `point_grade` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'total earned points out of the total available',
  `priority` int(11) NOT NULL DEFAULT '300' COMMENT 'The priority for this job. 0 is the most urgent',
  `date_created` datetime NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `title` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `letter_grade` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `error` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tries` int(10) DEFAULT NULL COMMENT 'The number of times that the scan for this page has tried to run',
  `num_errors` int(11) DEFAULT NULL COMMENT 'The total number of errors found',
  `num_notices` int(11) DEFAULT NULL COMMENT 'The total number of notices found',
  `found_with` enum('SITE_MAP','CRAWL','MANUAL') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CRAWL',
  `link_limit_hit` enum('NO','YES') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NO',
  `daemon_name` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_scanned_page_scans1_idx` (`scans_id`),
  KEY `fk_scanned_page_sites1_idx` (`sites_id`),
  KEY `scanned_page_scans_uri` (`scans_id`,`uri_hash`),
  KEY `scanned_page_uri` (`uri_hash`),
  KEY `status` (`status`,`priority`,`start_time`),
  KEY `scanned_page_daeomon_name` (`daemon_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `scanned_page_has_feature_analytics`
--

CREATE TABLE IF NOT EXISTS `scanned_page_has_feature_analytics` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `scanned_page_id` int(11) NOT NULL,
  `feature_analytics_id` int(10) UNSIGNED NOT NULL,
  `num_instances` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_page_analytics_scanned_page1_idx` (`scanned_page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `scanned_page_links`
--

CREATE TABLE IF NOT EXISTS `scanned_page_links` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_created` datetime NOT NULL,
  `scanned_page_id` int(11) NOT NULL,
  `original_url` varchar(2100) COLLATE utf8_unicode_ci NOT NULL,
  `original_url_hash` binary(16) NOT NULL,
  `original_curl_code` int(4) NOT NULL,
  `original_status_code` int(4) NOT NULL,
  `final_url` varchar(2100) COLLATE utf8_unicode_ci NOT NULL,
  `final_url_hash` binary(16) NOT NULL,
  `final_curl_code` int(4) NOT NULL,
  `final_status_code` int(4) NOT NULL,
  `cached` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_scan_links_scanned_page1_idx` (`scanned_page_id`),
  KEY `scanned_page_links_final_url` (`final_url_hash`),
  KEY `scanned_page_links_original_status` (`original_status_code`),
  KEY `scanned_page_links_final_status` (`final_status_code`),
  KEY `scanned_page_page_url` (`original_url_hash`,`final_url_hash`,`scanned_page_id`),
  KEY `scanned_page_links_last_uncached` (`date_created`,`scanned_page_id`,`original_url_hash`,`cached`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `scans`
--

CREATE TABLE IF NOT EXISTS `scans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sites_id` int(11) NOT NULL,
  `gpa` decimal(5,2) DEFAULT NULL,
  `status` enum('CREATED','QUEUED','RUNNING','COMPLETE','ERROR') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'CREATED',
  `scan_type` enum('USER','AUTO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'AUTO',
  `date_created` datetime NOT NULL,
  `date_updated` datetime DEFAULT NULL COMMENT 'The date that this scan was last updated',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `error` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pass_fail` enum('YES','NO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NO' COMMENT 'Was this scan a pass/fail scan of the site?',
  PRIMARY KEY (`id`),
  KEY `fk_scans_sites1_idx` (`sites_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE IF NOT EXISTS `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `base_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the base url of the site',
  `gpa` decimal(5,2) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The title of the site',
  `support_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_connection_error` datetime DEFAULT NULL,
  `http_code` int(10) DEFAULT NULL,
  `curl_code` int(10) DEFAULT NULL,
  `production_status` enum('PRODUCTION','DEVELOPMENT','ARCHIVED') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'PRODUCTION',
  `source` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_connection_success` datetime DEFAULT NULL,
  `support_groups` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `site_map_url` varchar(2100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `crawl_method` enum('CRAWL_ONLY','SITE_MAP_ONLY','HYBRID') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'HYBRID',
  `group_name` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `group_is_overridden` enum('YES','NO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NO',
  PRIMARY KEY (`id`),
  UNIQUE KEY `baseurl_UNIQUE` (`base_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `site_members`
--

CREATE TABLE IF NOT EXISTS `site_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL,
  `sites_id` int(11) NOT NULL,
  `source` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The source of this entry.  NULL means that it was generated by this system',
  `date_added` datetime NOT NULL COMMENT 'The date that this record was created',
  `verification_code` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_site_members` (`users_id`,`sites_id`,`source`),
  KEY `fk_site_members_users_idx` (`users_id`),
  KEY `fk_site_members_sites1_idx` (`sites_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `site_member_roles`
--

CREATE TABLE IF NOT EXISTS `site_member_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_members_id` int(11) NOT NULL,
  `roles_id` int(11) NOT NULL,
  `approved` enum('YES','NO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NO',
  `source` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The source of the member role.  If null, it means that the system generated it.',
  PRIMARY KEY (`id`,`site_members_id`),
  UNIQUE KEY `unique_site_member_roles` (`site_members_id`,`roles_id`),
  KEY `fk_site_member_roles_site_members1_idx` (`site_members_id`),
  KEY `fk_site_member_roles_roles1_idx` (`roles_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `site_reviews`
--

CREATE TABLE IF NOT EXISTS `site_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sites_id` int(11) NOT NULL,
  `creator_users_id` int(11) NOT NULL,
  `last_edited_users_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL COMMENT 'The date that this record was created',
  `date_edited` varchar(45) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The date that this record was last edited',
  `date_scheduled` datetime NOT NULL,
  `date_reviewed` datetime DEFAULT NULL,
  `status` enum('SCHEDULED','IN_REVIEW','REVIEW_FINISHED') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'SCHEDULED',
  `internal_notes` longtext COLLATE utf8_unicode_ci,
  `public_notes` longtext COLLATE utf8_unicode_ci,
  `result` enum('OKAY','NEEDS WORK') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_table1_users1_idx` (`creator_users_id`),
  KEY `fk_table1_users2_idx` (`last_edited_users_id`),
  KEY `fk_site_reviews_sites1_idx` (`sites_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `site_scan_history`
--

CREATE TABLE IF NOT EXISTS `site_scan_history` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sites_id` int(11) NOT NULL,
  `gpa` decimal(5,2) NOT NULL DEFAULT '0.00',
  `date_created` datetime NOT NULL,
  `total_pages` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_site_scan_history_sites1_idx` (`sites_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `site_scan_metric_history`
--

CREATE TABLE IF NOT EXISTS `site_scan_metric_history` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `metrics_id` int(11) NOT NULL,
  `gpa` decimal(5,2) NOT NULL DEFAULT '0.00',
  `site_scan_history_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_site_scan_metric_history_metrics1_idx` (`metrics_id`),
  KEY `fk_site_scan_metric_history_site_scan_history1_idx` (`site_scan_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `unl_page_attributes`
--

CREATE TABLE IF NOT EXISTS `unl_page_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scanned_page_id` int(11) NOT NULL,
  `html_version` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dep_version` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_unl_page_attributes_page1` (`scanned_page_id`),
  KEY `scan_html_version_index` (`html_version`),
  KEY `scan_dep_version_index` (`dep_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `unl_scan_attributes`
--

CREATE TABLE IF NOT EXISTS `unl_scan_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scans_id` int(11) NOT NULL,
  `html_version` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dep_version` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `root_site_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_unl_scan_attributes_scans1` (`scans_id`),
  KEY `scan_html_version_index` (`html_version`),
  KEY `scan_dep_version_index` (`dep_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `unl_site_progress`
--

CREATE TABLE IF NOT EXISTS `unl_site_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sites_id` int(11) NOT NULL,
  `estimated_completion` date DEFAULT '2010-01-01',
  `self_progress` int(3) NOT NULL DEFAULT '0',
  `self_comments` mediumtext COLLATE utf8_unicode_ci,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `replaced_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_unl_site_status_site1` (`sites_id`),
  KEY `fk_replaced_by` (`replaced_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `unl_version_history`
--

CREATE TABLE IF NOT EXISTS `unl_version_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version_type` enum('HTML','DEP') COLLATE utf8_unicode_ci NOT NULL,
  `version_number` varchar(56) COLLATE utf8_unicode_ci DEFAULT NULL,
  `number_of_sites` int(11) NOT NULL DEFAULT '0',
  `date_created` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `date_created` (`date_created`,`version_type`,`version_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The Internal ID for the user',
  `uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'An user identifier unique to the given provider. ',
  `provider` varchar(45) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The provider with which the user authenticated (e.g. ''Twitter'' or ''Facebook'')',
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role` enum('ADMIN','USER') COLLATE utf8_unicode_ci DEFAULT 'USER' COMMENT 'The user''s role for the system.  Either ADMIN or USER.',
  `last_login` datetime DEFAULT NULL,
  `total_logins` int(11) NOT NULL DEFAULT '0',
  `is_private` enum('YES','NO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_unique` (`provider`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `group_scan_metric_history`
--
ALTER TABLE `group_scan_metric_history`
  ADD CONSTRAINT `fk_group_scan_metric_history_group_scan_history1` FOREIGN KEY (`group_scan_history_id`) REFERENCES `group_scan_history` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_group_scan_metric_history_metrics1` FOREIGN KEY (`metrics_id`) REFERENCES `metrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `fk_marks_metrics1` FOREIGN KEY (`metrics_id`) REFERENCES `metrics` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `overrides`
--
ALTER TABLE `overrides`
  ADD CONSTRAINT `fk_overrides_marks_id` FOREIGN KEY (`marks_id`) REFERENCES `marks` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_overrides_sites` FOREIGN KEY (`sites_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_overrides_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `page_marks`
--
ALTER TABLE `page_marks`
  ADD CONSTRAINT `fk_page_marks_marks1` FOREIGN KEY (`marks_id`) REFERENCES `marks` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_page_marks_scanned_page1` FOREIGN KEY (`scanned_page_id`) REFERENCES `scanned_page` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `page_metric_grades`
--
ALTER TABLE `page_metric_grades`
  ADD CONSTRAINT `fk_page_metric_grades_metrics1` FOREIGN KEY (`metrics_id`) REFERENCES `metrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_page_metric_grades_scanned_page1` FOREIGN KEY (`scanned_page_id`) REFERENCES `scanned_page` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `scanned_page`
--
ALTER TABLE `scanned_page`
  ADD CONSTRAINT `fk_scanned_page_scans1` FOREIGN KEY (`scans_id`) REFERENCES `scans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_scanned_page_sites1` FOREIGN KEY (`sites_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `scanned_page_has_feature_analytics`
--
ALTER TABLE `scanned_page_has_feature_analytics`
  ADD CONSTRAINT `fk_scanned_page_has_feature_analytics1` FOREIGN KEY (`scanned_page_id`) REFERENCES `scanned_page` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `scanned_page_links`
--
ALTER TABLE `scanned_page_links`
  ADD CONSTRAINT `fk_scan_links_scanned_page1` FOREIGN KEY (`scanned_page_id`) REFERENCES `scanned_page` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `scans`
--
ALTER TABLE `scans`
  ADD CONSTRAINT `fk_scans_sites1` FOREIGN KEY (`sites_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `site_members`
--
ALTER TABLE `site_members`
  ADD CONSTRAINT `fk_site_members_sites1` FOREIGN KEY (`sites_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_site_members_users` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `site_member_roles`
--
ALTER TABLE `site_member_roles`
  ADD CONSTRAINT `fk_site_member_roles_roles1` FOREIGN KEY (`roles_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_site_member_roles_site_members1` FOREIGN KEY (`site_members_id`) REFERENCES `site_members` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `site_reviews`
--
ALTER TABLE `site_reviews`
  ADD CONSTRAINT `fk_site_reviews_sites1` FOREIGN KEY (`sites_id`) REFERENCES `sites` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_table1_users1` FOREIGN KEY (`creator_users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_table1_users2` FOREIGN KEY (`last_edited_users_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `site_scan_history`
--
ALTER TABLE `site_scan_history`
  ADD CONSTRAINT `fk_site_scan_history_sites1` FOREIGN KEY (`sites_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `site_scan_metric_history`
--
ALTER TABLE `site_scan_metric_history`
  ADD CONSTRAINT `fk_site_scan_metric_history_metrics1` FOREIGN KEY (`metrics_id`) REFERENCES `metrics` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_site_scan_metric_history_site_scan_history1` FOREIGN KEY (`site_scan_history_id`) REFERENCES `site_scan_history` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `unl_page_attributes`
--
ALTER TABLE `unl_page_attributes`
  ADD CONSTRAINT `fk_unl_page_attributes_page1` FOREIGN KEY (`scanned_page_id`) REFERENCES `scanned_page` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `unl_scan_attributes`
--
ALTER TABLE `unl_scan_attributes`
  ADD CONSTRAINT `fk_unl_scan_attributes_scans1` FOREIGN KEY (`scans_id`) REFERENCES `scans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `unl_site_progress`
--
ALTER TABLE `unl_site_progress`
  ADD CONSTRAINT `fk_replaced_by` FOREIGN KEY (`replaced_by`) REFERENCES `sites` (`id`),
  ADD CONSTRAINT `fk_unl_site_status_site1` FOREIGN KEY (`sites_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
