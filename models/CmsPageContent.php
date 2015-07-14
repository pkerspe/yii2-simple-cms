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
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use schallschlucker\simplecms\behaviours\CmsBlameableBehavior;

/**
 * This is the model class for table "cms_page_content".
 *
 * @property integer $id
 * @property integer $language the language id of this page content
 * @property string $metatags_general metatags to be rendered in the frontend view page
 * @property string $meta_keywords keywords to be used in the search as well as in the metatags in the frontend
 * @property string $description a short description of the contents of this page. Used in Metatags as well as to display a preview of the page content in the search results or teaser lists
 * @property string $content the content of this page (HTML)
 * @property string $javascript additional javascript to be rendered on the bottom of the page html source
 * @property string $css additional css definitions to be rendered on top of the page in the head section
 * @property string $modification_datetime last modification date and time of the page content element
 * @property integer $modification_userid user id of the user who modified the page content element for the last time
 * @property string $created_datetime creation date and time of the page content element
 * @property integer $createdby_userid user id of the user who created the page content element
 *          
 * @property CmsMenuItem[] $cmsMenuItems
 */
class CmsPageContent extends \yii\db\ActiveRecord {
	public function behaviors() {
		return [ 
			[ 
				'class' => TimestampBehavior::className (),
				'createdAtAttribute' => 'created_datetime',
				'updatedAtAttribute' => 'modification_datetime',
				'value' => new Expression ( 'NOW()' ) 
			],
			[ 
				'class' => CmsBlameableBehavior::className (),
				'createdByAttribute' => 'createdby_userid',
				'updatedByAttribute' => 'modification_userid' 
			] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'cms_page_content';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
			[ 
				[ 
					'language',
					'created_datetime',
					'createdby_userid' 
				],
				'required' 
			],
			[ 
				[ 
					'language',
					'modification_userid',
					'createdby_userid' 
				],
				'integer' 
			],
			[ 
				[ 
					'content',
					'javascript',
					'css' 
				],
				'string' 
			],
			[ 
				[ 
					'modification_datetime',
					'created_datetime' 
				],
				'safe' 
			],
			[ 
				[ 
					'metatags_general',
					'description' 
				],
				'string',
				'max' => 500 
			],
			[ 
				[ 
					'meta_keywords' 
				],
				'string',
				'max' => 255 
			] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
			'id' => Yii::t ( 'simplecms', 'ID' ),
			'language' => Yii::t ( 'simplecms', 'Language' ),
			'metatags_general' => Yii::t ( 'simplecms', 'Metatags General' ),
			'meta_keywords' => Yii::t ( 'simplecms', 'Meta Keywords' ),
			'description' => Yii::t ( 'simplecms', 'Description' ),
			'content' => Yii::t ( 'simplecms', 'Content' ),
			'javascript' => Yii::t ( 'simplecms', 'Javascript' ),
			'css' => Yii::t ( 'simplecms', 'Css' ),
			'modification_datetime' => Yii::t ( 'simplecms', 'Modification Datetime' ),
			'modification_userid' => Yii::t ( 'simplecms', 'Modification Userid' ),
			'created_datetime' => Yii::t ( 'simplecms', 'Created Datetime' ),
			'createdby_userid' => Yii::t ( 'simplecms', 'Createdby Userid' ) 
		];
	}
	
	/**
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getCmsMenuItems() {
		return $this->hasMany ( CmsMenuItem::className (), [ 
			'page_content_id' => 'id' 
		] );
	}
}
