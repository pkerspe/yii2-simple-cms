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
class m150725_130000_simplecms_newpagefield extends Migration {
	
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
	    echo "Creating new database field html_title in cms_page_content table\n";
	    $this->addColumn('{{%cms_page_content}}', 'html_title', Schema::TYPE_STRING.'(255) DEFAULT NULL COMMENT \'optional html title tag text for this page\'');
	}
	
	public function down() {
	    echo "removing database field html_title in cms_page_content table\n";
		$this->dropColumn( '{{%cms_page_content}}' , 'html_title');
	}
}
?>