-- MySQL dump 10.13  Distrib 5.7.9, for osx10.9 (x86_64)
-- Server version	5.7.10

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cms_content_category`
--

DROP TABLE IF EXISTS `cms_content_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cms_content_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'the id of the category',
  `parent_id` int(11) unsigned DEFAULT NULL COMMENT 'the parnet category id to allow building a tree structure',
  `displayname` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the name of the virtual folder / category',
  PRIMARY KEY (`id`),
  KEY `fk_parent_category_item_id_idx` (`parent_id`),
  CONSTRAINT `fk_parent_category_item_id` FOREIGN KEY (`parent_id`) REFERENCES `cms_content_category` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='the content categories are used to build a virtual folder structure to categorize/sort the media items (videos/images/sound files) ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_content_media`
--

DROP TABLE IF EXISTS `cms_content_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cms_content_media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_category_id` int(11) unsigned NOT NULL COMMENT 'the category (virtual folder) this content item belongs to',
  `media_type` enum('IMAGE','AUDIO','VIDEO','UNKNOWN') COLLATE utf8_unicode_ci NOT NULL COMMENT 'the media type category',
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the name of the file in the file system on the server',
  `file_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the path in the servers media repository',
  `filesize_bytes` int(10) unsigned NOT NULL COMMENT 'the size of the media file in bytes',
  `mime_type` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the mime type of the file',
  `dimension_width` smallint(6) DEFAULT NULL COMMENT 'width of image or video if known',
  `dimension_height` smallint(6) DEFAULT NULL COMMENT 'height of image or video if known',
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'keywords used in the image browser search',
  `meta_description` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'a short description of the contents used in alt text and in image browser search',
  `modification_datetime` datetime DEFAULT NULL COMMENT 'last modification date and time of the page content element',
  `modification_userid` int(11) unsigned DEFAULT NULL COMMENT 'user id of the user who modified the page content element for the last time',
  `created_datetime` datetime NOT NULL COMMENT 'creation date and time of the page content element',
  `createdby_userid` int(11) unsigned NOT NULL COMMENT 'user id of the user who created the page content element',
  PRIMARY KEY (`id`),
  KEY `fk_linked_content_category_id_idx` (`content_category_id`),
  CONSTRAINT `fk_content_category_for_item` FOREIGN KEY (`content_category_id`) REFERENCES `cms_content_category` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='a media item (image,video,audio) to be embeded in a page content via the editor';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_content_media_variation`
--

DROP TABLE IF EXISTS `cms_content_media_variation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cms_content_media_variation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_content_media_id` int(11) unsigned NOT NULL COMMENT 'the parent item where this variation is beloning to',
  `dimension_width` smallint(5) unsigned DEFAULT NULL COMMENT 'width (if applicable) of this media item',
  `dimension_height` smallint(5) unsigned DEFAULT NULL COMMENT 'height (if applicable) of this media item',
  `mime_type` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'mime_type of this variation',
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the name of the file in the file system on the server',
  `file_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the path in the servers media repository',
  PRIMARY KEY (`id`),
  KEY `fk_parent_content_media_id_idx` (`parent_content_media_id`),
  CONSTRAINT `fk_parent_content_media_id` FOREIGN KEY (`parent_content_media_id`) REFERENCES `cms_content_media` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='a variation of a media item is e.g. a thumbnail image of a media item.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_document`
--

DROP TABLE IF EXISTS `cms_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cms_document` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language` int(10) NOT NULL COMMENT 'the language id of the document',
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the filename of the document',
  `file_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the system path to the folder containing the document ',
  `mime_type` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the mimetyep of the document',
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'keywords of the contents of this document rendered in the meta tags (if the content is displayed inline in the default layout) and used in the search',
  `meta_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'a short description of the contents of this document rendered in the meta tags (if the content is displayed inline in the default layout) and used in the search',
  `modification_datetime` datetime DEFAULT NULL COMMENT 'last modification date and time of the page content element',
  `modification_userid` int(11) unsigned DEFAULT NULL COMMENT 'user id of the user who modified the page content element for the last time',
  `created_datetime` datetime NOT NULL COMMENT 'creation date and time of the page content element',
  `createdby_userid` int(11) unsigned NOT NULL COMMENT 'user id of the user who created the page content element',
  `presentation_style` smallint(2) NOT NULL DEFAULT '2' COMMENT 'The style for presenting this document when the link is called',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='a document for usage with the cms either as attachment to a page or as a node in the navigation';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_hierarchy_item`
--

DROP TABLE IF EXISTS `cms_hierarchy_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cms_hierarchy_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'the id of the navigation item',
  `parent_id` int(10) unsigned DEFAULT '0' COMMENT 'the id of the parent item in the hierarchy',
  `position` smallint(5) unsigned NOT NULL COMMENT 'the position of the item within its siblings (for defining the order of the navigation items when being displayed)',
  `display_state` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'a status that influences the display status of this item in the navigation.',
  PRIMARY KEY (`id`),
  KEY `fk_parent_hierarchy_item_id_idx` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5686 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='a hierarchy item represents an tiem to be display in the navigation and could be either linked to a page content (to display a website) or a csm_document to directly call a document oject when clicked.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_menu_item`
--

DROP TABLE IF EXISTS `cms_menu_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cms_menu_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'an alias (e.g. human readable text that relates to the topic of the menu items content) to be used for the link (URL) pointing to the menu item instead of using an integer id.',
  `cms_hierarchy_item_id` int(10) unsigned NOT NULL COMMENT 'the hierarchy_item where this menu item belongs to',
  `language` int(10) unsigned NOT NULL COMMENT 'the language of this menu item',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the display name as displayed in the navigation',
  `page_content_id` int(10) unsigned DEFAULT NULL COMMENT 'the content id of the page content to be displayed. This settings is optional since the menu item could also be linked to a document id instead.',
  `document_id` int(10) unsigned DEFAULT NULL COMMENT 'the document id of the file content to be displayed. This settings is optional since the menu item could also be linked to a content id instead.',
  `direct_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'a direct url to be called (e.g. for linking in yii2 action calls into the navigation)',
  `link_target` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'the target to be used in the link (e.g. _blank to open the link in a new window)',
  `link_css_class` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'one ore more (space separated) css classes to be added to the link created in the navigation for this specific menu item language version',
  `modification_datetime` datetime DEFAULT NULL COMMENT 'last modification date and time of the page content element',
  `modification_userid` int(11) unsigned DEFAULT NULL COMMENT 'user id of the user who modified the page content element for the last time',
  `created_datetime` datetime NOT NULL COMMENT 'creation date and time of the page content element',
  `createdby_userid` int(11) unsigned NOT NULL COMMENT 'user id of the user who created the page content element',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hierarchy_lang` (`cms_hierarchy_item_id`,`language`),
  KEY `fk_menu_document_id_idx` (`document_id`),
  KEY `fk_menu_page_content_id_idx` (`page_content_id`),
  KEY `index_alias` (`alias`),
  CONSTRAINT `fk_cms_hierarchy_item` FOREIGN KEY (`cms_hierarchy_item_id`) REFERENCES `cms_hierarchy_item` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_menu_document_id` FOREIGN KEY (`document_id`) REFERENCES `cms_document` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_menu_page_content_id` FOREIGN KEY (`page_content_id`) REFERENCES `cms_page_content` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5700 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='a cms_menu is a language specific menu entry used to be displayed in the navigation';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cms_page_content`
--

DROP TABLE IF EXISTS `cms_page_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cms_page_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language` int(10) unsigned NOT NULL COMMENT 'the language id of this page content',
  `metatags_general` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'metatags to be rendered in the frontend view page',
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'keywords to be used in the search as well as in the metatags in the frontend',
  `description` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'a short description of the contents of this page. Used in Metatags as well as to display a preview of the page content in the search results or teaser lists',
  `content` text COLLATE utf8_unicode_ci COMMENT 'the content of this page (HTML)',
  `javascript` text COLLATE utf8_unicode_ci COMMENT 'additional javascript to be rendered on the bottom of the page html source',
  `css` text COLLATE utf8_unicode_ci COMMENT 'additional css definitions to be rendered on top of the page in the head section',
  `modification_datetime` datetime DEFAULT NULL COMMENT 'last modification date and time of the page content element',
  `modification_userid` int(11) unsigned DEFAULT NULL COMMENT 'user id of the user who modified the page content element for the last time',
  `created_datetime` datetime NOT NULL COMMENT 'creation date and time of the page content element',
  `createdby_userid` int(11) unsigned NOT NULL COMMENT 'user id of the user who created the page content element',
  `html_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'optional html title tag text for this page',
  `render_subpage_teasers` tinyint(1) unsigned DEFAULT NULL COMMENT 'Should the cms render a list of subpage teasers below the content area?',
  `teaser_image_id` int(11) unsigned DEFAULT NULL,
  `teaser_text` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `teaser_name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `teaser_link` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='A cms content element representing the content of a page';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-04-05 19:58:05
