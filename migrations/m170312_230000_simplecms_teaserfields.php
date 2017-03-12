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
class m170312_230000_simplecms_teaserfields extends Migration {
	
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
	    echo "Creating new database fields teaser_image_id and teaser_text in cms_page_content table\n";
	    $this->addColumn('{{%cms_page_content}}', 'teaser_image_id', Schema::TYPE_INTEGER.'(11) DEFAULT NULL COMMENT \'the id of the teaser image media item to be displayed e.g. in sub page list widgets\'');
        $this->addColumn('{{%cms_page_content}}', 'teaser_text', Schema::TYPE_STRING.'(500) DEFAULT NULL COMMENT \'some teaser text (max 500 chars) for the page that can be used in widgets\'');
	}
	
	public function down() {
	    echo "removing database fields teaser_image_id and teaser_text in cms_page_content table\n";
		$this->dropColumn( '{{%cms_page_content}}' , 'teaser_image_id');
        $this->dropColumn( '{{%cms_page_content}}' , 'teaser_text');
	}
}
?>