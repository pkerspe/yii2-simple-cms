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

class CmsAdministrationMainTreeViewForm extends Model {
	public $treeDisplayLanguageId;
	public $hideItemsWithMissingLanguage = false;
	public $expandFolderDepth = 999;
	
	/**
	 *
	 * @return array the validation rules.
	 */
	public function rules() {
		return [ 
			[['treeDisplayLanguageId'],'string','min' => 1,'max' => 10],
			[['hideItemsWithMissingLanguage'],'boolean'],[['expandFolderDepth'],'integer','min' => 0,'max' => '9999'] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
			'treeDisplayLanguageId' => Yii::t ( 'simplecms', 'menu tree language' ),
			'hideItemsWithMissingLanguage' => Yii::t ( 'simplecms', 'hide menu items without translation in requested language' ),
			'expandFolderDepth' => Yii::t ( 'simplecms', 'expand folder until depth' ) 
		];
	}
}
?>
