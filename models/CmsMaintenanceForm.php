<?php

/*
 * This file is part of the simple-cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace schallschlucker\simplecms\models;

use Yii;
use yii\base\Model;

class CmsMaintenanceForm extends Model {
	public $checkPositionsrecursive;

	
	/**
	 *
	 * @return array the validation rules.
	 */
	public function rules() {
		return [
			[['checkPositionsrecursive'],'boolean'],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
			'checkPositionsrecursive' => Yii::t ( 'app\cms', 'check and fix position values for complete tree' ),
		];
	}
}
?>
