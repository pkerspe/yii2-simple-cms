<?php
/*
 * This file is part of the simple-cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use yii\db\Schema;
use yii\base\InvalidConfigException;
use schallschlucker\simplecms\migrations\Migration;
use schallschlucker\simplecms\controllers\backend\MediaController;

/**
 *
 * @author Paul Kerspe
 */
class m141217_232437_simplecms_init extends Migration {
	
	/**
	 *
	 * @var string
	 */
	protected $tableOptions;
	
	/**
	 * @inheritdoc
	 */
	public function init() {
		parent::init ();
	}
	
	
	public function safeUp() {
		$this->createTable ( '{{%cms_content_media}}', [
			'id' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY",
			'media_type' => Schema::TYPE_STRING . "(50) NOT NULL COMMENT 'the media type (AUDIO, VIDEO, IMAGE)'",
			'file_name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'the name of the file in the file system on the server'",
			'file_path' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'the path in the servers media repository'",
			'mime_type' => Schema::TYPE_STRING . "(30) NOT NULL COMMENT 'the mime type of the file'",
			'dimension_width' => Schema::TYPE_SMALLINT . "(6) DEFAULT NULL COMMENT 'width of image or video if known'",
			'dimension_height' => Schema::TYPE_SMALLINT . "(6) DEFAULT NULL COMMENT 'height of image or video if known'",
			'meta_keywords' => Schema::TYPE_STRING . "(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'keywords used in the image browser search'",
			'meta_description' => Schema::TYPE_STRING . "(100) DEFAULT NULL COMMENT 'a short description of the contents used in alt text and in image browser search'",
			'modification_datetime' => Schema::TYPE_DATETIME . " DEFAULT NULL COMMENT 'last modification date and time of the page content element'",
			'modification_userid' => Schema::TYPE_INTEGER . "(11) unsigned DEFAULT NULL COMMENT 'user id of the user who modified the page content element for the last time'",
			'created_datetime' => Schema::TYPE_DATETIME . " NOT NULL COMMENT 'creation date and time of the page content element'",
			'createdby_userid' => Schema::TYPE_INTEGER . "(11) unsigned NOT NULL COMMENT 'user id of the user who created the page content element'",
		], $this->tableOptions. " COMMENT='a media item (image,video,audio) to be embeded in a page content via the editor'");
		
		$this->createTable ( '{{%cms_content_media_variation}}', [
			'id' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY",
			'parent_content_media_id' => Schema::TYPE_INTEGER . "(11) unsigned NOT NULL COMMENT 'the parent item where this variation is beloning to'",
			'dimension_width' => Schema::TYPE_SMALLINT . "(5) unsigned DEFAULT NULL COMMENT 'width (if applicable) of this media item'",
			'dimension_height' => Schema::TYPE_SMALLINT. "(5) unsigned DEFAULT NULL COMMENT 'height (if applicable) of this media item'",
			'mime_type' => Schema::TYPE_STRING . "(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'mime_type of this variation'",
			'file_name' => Schema::TYPE_STRING . "(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the name of the file in the file system on the server'",
			'file_path' => Schema::TYPE_STRING . "(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'the path in the servers media repository'",
		], $this->tableOptions. " COMMENT='a variation of a media item is e.g. a thumbnail image of a media item.'" );

		$this->createTable ( '{{%cms_menu_item}}', [
			'id' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY",
			'cms_hierarchy_item_id' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL COMMENT 'the hierarchy_item where this menu item belongs to'",
			'alias' => Schema::TYPE_STRING . "(255) DEFAULT NULL COMMENT 'an alias (e.g. human readable text that relates to the topic of the menu items content) to be used for the link (URL) pointing to the menu item instead of using an integer id.'",
			'language' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL COMMENT 'the language of this menu item'",
			'name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'the display name as displayed in the navigation'",
			'page_content_id' => Schema::TYPE_INTEGER . "(10) unsigned DEFAULT NULL COMMENT 'the content id of the page content to be displayed. This settings is optional since the menu item could also be linked to a document id instead.'",
			'document_id' => Schema::TYPE_INTEGER . "(10) unsigned DEFAULT NULL COMMENT 'the document id of the file content to be displayed. This settings is optional since the menu item could also be linked to a content id instead.'",
			'direct_url' => Schema::TYPE_STRING . "(255) DEFAULT NULL COMMENT 'a direct url to be called (e.g. for linking in yii2 action calls into the navigation)'",
			'link_target' => Schema::TYPE_STRING . "(30) DEFAULT NULL COMMENT 'the target to be used in the link (e.g. _blank to open the link in a new window)'",
			'link_css_class' => Schema::TYPE_STRING . "(50) DEFAULT NULL COMMENT 'one ore more (space separated) css classes to be added to the link created in the navigation for this specific menu item language version'",
			'modification_datetime' => Schema::TYPE_DATETIME . " DEFAULT NULL COMMENT 'last modification date and time of the page content element'",
			'modification_userid' => Schema::TYPE_INTEGER . "(11) unsigned DEFAULT NULL COMMENT 'user id of the user who modified the page content element for the last time'",
			'created_datetime' => Schema::TYPE_DATETIME . " NOT NULL COMMENT 'creation date and time of the page content element'",
			'createdby_userid' => Schema::TYPE_INTEGER . "(11) unsigned NOT NULL COMMENT 'user id of the user who created the page content element'",
		], $this->tableOptions." COMMENT='a cms_menu is a language specific menu entry used to be displayed in the navigation'" );

		$this->createTable ( '{{%cms_document}}', [ 
			'id' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY",
			'language' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL COMMENT 'the language id of the document'",
			'file_name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'the file name of the document'",
			'file_path' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'the system path to the folder containing the document'",
			'mime_type' => Schema::TYPE_STRING . "(30)  NOT NULL COMMENT 'the mime type of the document'",
			'meta_keywords' => Schema::TYPE_STRING . "(255) DEFAULT NULL COMMENT 'keywords of the contents of this document rendered in the meta tags (if the content is displayed inline in the default layout) and used in the search'",
			'meta_description' => Schema::TYPE_STRING . "(255) DEFAULT NULL COMMENT 'a short description of the contents of this document rendered in the meta tags (if the content is displayed inline in the default layout) and used in the search'",
			'modification_datetime' => Schema::TYPE_DATETIME . " DEFAULT NULL COMMENT 'last modification date and time of the page content element'",
			'modification_userid' => Schema::TYPE_INTEGER . "(11) unsigned DEFAULT NULL COMMENT 'user id of the user who modified the document element (not the document itself) for the last time'",
			'created_datetime' => Schema::TYPE_DATETIME . " NOT NULL COMMENT 'creation date and time of the document element (not the document itself)'",
			'createdby_userid' => Schema::TYPE_INTEGER . "(11) unsigned NOT NULL COMMENT 'user id of the user who created the document element (not the document itself)'",
			'presentation_style' => Schema::TYPE_SMALLINT . "(2) NOT NULL DEFAULT '2' COMMENT 'The style for presenting this document when the link is called'" 
		], $this->tableOptions );
		
		$this->createTable ( '{{%cms_hierarchy_item}}', [ 
			'id' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'the id of the navigation item'",
			'parent_id' => Schema::TYPE_INTEGER . "(10) unsigned DEFAULT '1' COMMENT 'the id of the parent item in the hierarchy'",
			'position' => Schema::TYPE_SMALLINT . "(5) unsigned NOT NULL COMMENT 'the position of the item within its siblings (for defining the order of the navigation items when being displayed)'",
			'display_state' => Schema::TYPE_SMALLINT . "(2) unsigned NOT NULL DEFAULT '1' COMMENT 'a status that influences the display status of this item in the navigation.'" 
		], $this->tableOptions );
		
		//root hierarchy item
		$this->insert('{{%cms_hierarchy_item}}', ['id' => 1, 'parent_id' => NULL ,'position' => 1,'display_state' => 1]);
		
		$this->createTable ( '{{%cms_page_content}}', [ 
			'id' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'the id of the page content item'",
			'language' => Schema::TYPE_INTEGER . "(10) unsigned NOT NULL COMMENT 'the language id of the page content'",
			'metatags_general' => Schema::TYPE_STRING . "(500) DEFAULT NULL COMMENT 'metatags to be rendered in the frontend view page'",
			'meta_keywords' => Schema::TYPE_STRING . "(255) DEFAULT NULL COMMENT 'keywords to be used in the search as well as in the metatags in the frontend'",
			'description' => Schema::TYPE_STRING . "(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'a short description of the contents of this page. Used in Metatags as well as to display a preview of the page content in the search results or teaser lists'",
			'content' => Schema::TYPE_TEXT . " COMMENT 'the content of this page (HTML)'",
			'javascript' => Schema::TYPE_TEXT . " COMMENT 'additional javascript to be rendered on the bottom of the page html source'",
			'css' => Schema::TYPE_TEXT . " COMMENT 'additional css to be rendered on the top of the page html source'",
			'modification_datetime' => Schema::TYPE_DATETIME . " DEFAULT NULL COMMENT 'last modification date and time of the page content element'",
			'modification_userid' => Schema::TYPE_INTEGER . "(11) unsigned DEFAULT NULL COMMENT 'user id of the user who modified the page content element for the last time'",
			'created_datetime' => Schema::TYPE_DATETIME . " NOT NULL COMMENT 'creation date and time of the page content element'",
			'createdby_userid' => Schema::TYPE_INTEGER . "(11) unsigned NOT NULL COMMENT 'user id of the user who created the page content element'" 
		], $this->tableOptions );
		
		$this->createTable ( '{{%cms_content_category}}', [
			'id' => Schema::TYPE_INTEGER . "(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'the id of the category'",
			'parent_id' => Schema::TYPE_INTEGER . "(11) unsigned DEFAULT NULL COMMENT 'the parnet category id to allow building a tree structure'",
			'displayname' => Schema::TYPE_STRING . "(50) NOT NULL COMMENT 'the name of the virtual folder / category'",
		], $this->tableOptions . " COMMENT='the content categories are used to build a virtual folder structure to categorize/sort the media items (videos/images/sound files) '" );

		//root media category item
		$this->insert('{{%cms_content_category}}', ['id' => MediaController::$ROOT_MEDIA_CATEGORY_ID, 'parent_id' => NULL ,'displayname' => 'root']);
		$this->insert('{{%cms_content_category}}', ['id' => MediaController::$MEDIA_IMAGE_BASE_CATEGORY_ID, 'parent_id' => MediaController::$ROOT_MEDIA_CATEGORY_ID ,'displayname' => 'images']);
		$this->insert('{{%cms_content_category}}', ['id' => MediaController::$MEDIA_VIDEO_BASE_CATEGORY_ID, 'parent_id' => MediaController::$ROOT_MEDIA_CATEGORY_ID ,'displayname' => 'videos']);
		$this->insert('{{%cms_content_category}}', ['id' => MediaController::$MEDIA_AUDIO_BASE_CATEGORY_ID, 'parent_id' => MediaController::$ROOT_MEDIA_CATEGORY_ID ,'displayname' => 'audio']);
		
		$this->createIndex ( 'fk_parent_category_item_id_idx', '{{%cms_content_category}}', 'parent_id', false );
		$this->addForeignKey('fk_parent_category_item_id', '{{%cms_content_category}}', 'parent_id', '{{%cms_content_category}}', 'id','NO ACTION','NO ACTION');
		
		$this->createIndex ( 'fk_parent_content_media_id_idx', '{{%cms_content_media_variation}}', 'parent_content_media_id', false );
		$this->addForeignKey('fk_parent_content_media_id', '{{%cms_content_media_variation}}', 'parent_content_media_id', '{{%cms_content_media}}', 'id','NO ACTION','NO ACTION');
		
		$this->createIndex ( 'unique_hierarchy_lang', '{{%cms_menu_item}}', ['language','cms_hierarchy_item_id'], true );
		$this->createIndex ( 'fk_menu_document_id_idx', '{{%cms_menu_item}}', 'document_id', false );
		$this->createIndex ( 'fk_menu_page_content_id_idx', '{{%cms_menu_item}}', 'page_content_id', false );
		
		$this->addForeignKey('fk_cms_hierarchy_item', '{{%cms_menu_item}}', 'cms_hierarchy_item_id', '{{%cms_hierarchy_item}}', 'id','NO ACTION','NO ACTION');
		$this->addForeignKey('fk_menu_document_id', '{{%cms_menu_item}}', 'document_id', '{{%cms_document}}', 'id','NO ACTION','NO ACTION');
		$this->addForeignKey('fk_menu_page_content_id', '{{%cms_menu_item}}', 'page_content_id', '{{%cms_page_content}}', 'id','NO ACTION','NO ACTION');
		
		$this->addForeignKey ( 'fk_parent_hierarchy_item_id', '{{%cms_hierarchy_item}}', 'parent_id', '{{%cms_hierarchy_item}}', 'id','NO ACTION','NO ACTION' );
		$this->createIndex ( 'fk_parent_hierarchy_item_id_idx', '{{%cms_hierarchy_item}}', 'parent_id', false );
		
		// insert rquired root items

	}
	
	public function down() {
		$this->dropTable ( '{{%cms_content_media_variation}}' );
		$this->dropTable ( '{{%cms_content_media}}' );
		$this->dropTable ( '{{%cms_menu_item}}' );
		$this->dropTable ( '{{%cms_document}}' );
		$this->dropTable ( '{{%cms_page_content}}' );
		$this->dropTable ( '{{%cms_hierarchy_item}}' );
	}
}
?>