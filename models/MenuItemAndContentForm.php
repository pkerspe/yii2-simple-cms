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

class MenuItemAndContentForm extends Model {
	const CONTENT_TYPE_UNDEFINED = 0;
	const CONTENT_TYPE_PAGE = 1;
	const CONTENT_TYPE_DOCUMENT = 2;
	const CONTENT_TYPE_URL = 3;
	
	// a selector to determine what type the content of the menu item is.
	// can be any of:
	// CONTENT_TYPE_PAGE
	// CONTENT_TYPE_DOCUMENT
	// CONTENT_TYPE_URL
	public $contentType;
	public $newMenuName;
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
			'contentType' => Yii::t ( 'simplecms', 'content type' ) 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
			[ 
				[ 
					'contentType' 
				],
				'required' 
			],
			[ 
				[ 
					'contentType',
					'newMenuName' 
				],
				'required',
				'on' => 'createMenuLanguageVersion' 
			],
			[ 
				[ 
					'contentType' 
				],
				'integer',
				'min' => 0,
				'max' => 3 
			] 
		];
	}
}
?>
