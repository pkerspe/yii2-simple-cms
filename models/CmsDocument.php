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
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "cms_document".
 *
 * @property integer $id
 * @property integer $language the language id of the document
 * @property string $file_name the filename of the document
 * @property string $file_path the system path to the folder containing the document
 * @property string $mime_type the mimetyep of the document
 * @property string $meta_keywords keywords of the contents of this document rendered in the meta tags (if the content is displayed inline in the default layout) and used in the search
 * @property string $meta_description a short description of the contents of this document rendered in the meta tags (if the content is displayed inline in the default layout) and used in the search
 * @property string $modification_datetime last modification date and time of the page content element
 * @property integer $modification_userid user id of the user who modified the page content element for the last time
 * @property string $created_datetime creation date and time of the page content element
 * @property integer $createdby_userid user id of the user who created the page content element
 * @property integer $presentation_style The style for presenting this document when the link is called. One of CmsDocument::PRESENTATION_STYLE_EMBEDED, CmsDocument::PRESENTATION_STYLE_WINDOW, CmsDocument::PRESENTATION_STYLE_DOWNLOAD
 *          
 * @property CmsMenuItem[] $cmsMenuItems
 */
class CmsDocument extends \yii\db\ActiveRecord {
	const PRESENTATION_STYLE_EMBEDED = 0;
	const PRESENTATION_STYLE_WINDOW = 1;
	const PRESENTATION_STYLE_DOWNLOAD = 2;
	public function behaviors() {
		return [ 
			[ 
				'class' => TimestampBehavior::className (),
				'createdAtAttribute' => 'created_datetime',
				'updatedAtAttribute' => 'modification_datetime',
				'value' => new Expression ( 'NOW()' ) 
			],
			[ 
				'class' => BlameableBehavior::className (),
				'createdByAttribute' => 'createdby_userid',
				'updatedByAttribute' => 'modification_userid' 
			] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'cms_document';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
			[ 
				[ 
					'language',
					'file_name',
					'file_path',
					'mime_type',
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
					'modification_datetime',
					'created_datetime' 
				],
				'safe' 
			],
			[ 
				[ 
					'presentation_style' 
				],
				'integer',
				'min' => 1,
				'max' => 10 
			],
			[ 
				[ 
					'file_name',
					'file_path',
					'meta_keywords',
					'meta_description' 
				],
				'string',
				'max' => 255 
			],
			[ 
				[ 
					'mime_type' 
				],
				'string',
				'max' => 30 
			] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
			'id' => Yii::t ( 'app/cms', 'ID' ),
			'language' => Yii::t ( 'app/cms', 'Language' ),
			'file_name' => Yii::t ( 'app/cms', 'File Name' ),
			'file_path' => Yii::t ( 'app/cms', 'File Path' ),
			'mime_type' => Yii::t ( 'app/cms', 'Mime Type' ),
			'meta_keywords' => Yii::t ( 'app/cms', 'Meta Keywords' ),
			'meta_description' => Yii::t ( 'app/cms', 'Meta Description' ),
			'modification_datetime' => Yii::t ( 'app/cms', 'Modification Datetime' ),
			'modification_userid' => Yii::t ( 'app/cms', 'Modification Userid' ),
			'created_datetime' => Yii::t ( 'app/cms', 'Created Datetime' ),
			'createdby_userid' => Yii::t ( 'app/cms', 'Createdby Userid' ),
			'presentation_style' => Yii::t ( 'app/cms', 'Presentation Style' ) 
		];
	}
	
	/**
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getCmsMenuItems() {
		return $this->hasMany ( CmsMenuItem::className (), [ 
			'document_id' => 'id' 
		] );
	}
}
