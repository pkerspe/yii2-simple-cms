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
use schallschlucker\simplecms\controllers\mediacontroller\MediaController;

/**
 *
 * @author Paul Kerspe
 */
class m151114_123000_simplecms_newpagefield extends Migration {
	
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
	    echo "Creating new database field html_title in render_subpage_teasers table\n";
	    $this->addColumn('{{%cms_page_content}}', 'render_subpage_teasers', Schema::TYPE_INTEGER.'(1) DEFAULT NULL COMMENT \'Should subpage teasers be rendered below the wysiwyg content of this page?\'');
	}
	
	public function down() {
	    echo "removing database field render_subpage_teasers in cms_page_content table\n";
		$this->dropColumn( '{{%cms_page_content}}' , 'render_subpage_teasers');
	}
}
?>