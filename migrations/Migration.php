<?php
namespace schallschlucker\simplecms\migrations;
/*
 * This file is part of the simple-cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 *
 * @author Paul Kerspe
 */
class Migration extends \yii\db\Migration {
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
		
		switch (\Yii::$app->db->driverName) {
			case 'mysql' :
				$this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
				break;
			default :
				throw new \RuntimeException ( 'Your database is not yet supported!' );
		}
	}
}

?>