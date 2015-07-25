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
use yii\helpers\Url;

/**
 * This is the model class for table "cms_menu_item".
 *
 * @property integer $id
 * @property string $alias an alias (e.g. human readable text that relates to the topic of the menu items content) to be used for the link (URL) pointing to the menu item instead of using an integer id.
 * @property integer $cms_hierarchy_item_id the hierarchy_item where this menu item belongs to
 * @property integer $language the language of this menu item
 * @property string $name the display name as displayed in the navigation
 * @property integer $page_content_id the content id of the page content to be displayed. This settings is optional since the menu item could also be linked to a document id instead.
 * @property integer $document_id the document id of the file content to be displayed. This settings is optional since the menu item could also be linked to a content id instead.
 * @property string $direct_url a direct url to be called (e.g. for linking in yii2 action calls into the navigation)
 * @property string $link_target the target to be used in the link (e.g. _blank to open the link in a new window)
 * @property string $link_css_class one ore more (space separated) css classes to be added to the link created in the navigation for this specific menu item language version
 * @property string $modification_datetime last modification date and time of the page content element
 * @property integer $modification_userid user id of the user who modified the page content element for the last time
 * @property string $created_datetime creation date and time of the page content element
 * @property integer $createdby_userid user id of the user who created the page content element
 *          
 * @property CmsHierarchyItem $cmsHierarchyItem
 * @property CmsDocument $document
 * @property CmsPageContent $pageContent
 */
class CmsMenuItem extends \yii\db\ActiveRecord {
	public function behaviors() {
		return [ 
			[ 
				'class' => TimestampBehavior::className (),
				'createdAtAttribute' => 'created_datetime',
				'updatedAtAttribute' => 'modification_datetime',
				'value' => new Expression( 'NOW()' ) 
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
		return 'cms_menu_item';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
			[ 
				[ 
					'cms_hierarchy_item_id',
					'language',
					'name',
					'created_datetime',
					'createdby_userid' 
				],
				'required' 
			],
			[ 
				[ 
					'cms_hierarchy_item_id',
					'language',
					'page_content_id',
					'document_id',
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
					'name',
					'direct_url' 
				],
				'string',
				'max' => 255 
			],
			[ 
				[ 
					'alias',
					'link_target' 
				],
				'match',
				'pattern' => '/^[a-zA-Z\-_0-9]*$/',
				'message' => Yii::t ( 'simplecms', 'only characters, numbers and "-" or "_" are allowed (no blanks or special characters)' ) 
			],
			[ 
				[ 
					'link_css_class' 
				],
				'match',
				'pattern' => '/^[a-zA-Z\-_0-9 ]*$/',
				'message' => Yii::t ( 'simplecms', 'only characters, numbers and "-" or "_"  and blanks are allowed (no special characters)' ) 
			],
			[ 
				[ 
					'link_target' 
				],
				'string',
				'max' => 30 
			],
			[ 
				[ 
					'link_css_class' 
				],
				'string',
				'max' => 50 
			],
			[ 
				[ 
					'cms_hierarchy_item_id',
					'language' 
				],
				'unique',
				'targetAttribute' => [ 
					'cms_hierarchy_item_id',
					'language' 
				],
				'message' => 'The combination of Cms Hierarchy Item ID and Language has already been taken.' 
			] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
			'id' => Yii::t ( 'simplecms', 'ID' ),
			'alias' => Yii::t ( 'simplecms', 'Alias' ),
			'cms_hierarchy_item_id' => Yii::t ( 'simplecms', 'Cms Hierarchy Item ID' ),
			'language' => Yii::t ( 'simplecms', 'Language' ),
			'name' => Yii::t ( 'simplecms', 'Name' ),
			'page_content_id' => Yii::t ( 'simplecms', 'Page Content ID' ),
			'document_id' => Yii::t ( 'simplecms', 'Document ID' ),
			'direct_url' => Yii::t ( 'simplecms', 'Direct Url' ),
			'link_target' => Yii::t ( 'simplecms', 'Link Target' ),
			'link_css_class' => Yii::t ( 'simplecms', 'Link Css Class' ),
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
	public function getCmsHierarchyItem() {
		return $this->hasOne ( CmsHierarchyItem::className (), [ 
			'id' => 'cms_hierarchy_item_id' 
		] );
	}
	
	/**
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getDocument() {
		return $this->hasOne ( CmsDocument::className (), [ 
			'id' => 'document_id' 
		] );
	}
	
	/**
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getPageContent() {
		return $this->hasOne ( CmsPageContent::className (), [ 
			'id' => 'page_content_id' 
		] );
	}
	
	/**
	 * get an absolute URL to call this menu item
	 * @return string
	 */
	public function getFormattedUrl(){
        if($this->page_content_id != null){
            if($this->alias != null && $this->alias != ''){
                return Url::toRoute(['/'.Yii::$app->getModule('simplecms_frontend')->routePrefix.'/show/alias','menuItemAlias' => $this->alias]);
            } else {
                return Url::toRoute(['/'.Yii::$app->getModule('simplecms_frontend')->routePrefix.'/show/page','menuItemId' => $this->id]);
            }
        }
        else if($this->document_id)
            return Url::toRoute(['/'.Yii::$app->getModule('simplecms_frontend')->routePrefix.'/show/document','documentId' => $this->document_id]);
        else
            return $this->direct_url;
	}
}
