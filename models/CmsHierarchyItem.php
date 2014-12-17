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

/**
 * This is the model class for table "cms_hierarchy_item".
 *
 * @property integer $id the id of the navigation item
 * @property integer $parent_id the id of the parent item in the hierarchy
 * @property integer $position the position of the item within its siblings (for defining the order of the navigation items when being displayed)
 * @property integer $display_state a status that influences the display status of this item in the navigation. @see DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION
 *          
 * @property CmsHierarchyItem $parent
 * @property CmsHierarchyItem[] $cmsHierarchyItems
 * @property CmsMenu[] $cmsMenus
 */
class CmsHierarchyItem extends \yii\db\ActiveRecord {
	const DISPLAYSTATE_MIN_VALUE = 1;
	const DISPLAYSTATE_MAX_VALUE = 3;
	const DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION = 1;
	const DISPLAYSTATE_PUBLISHED_HIDDEN_IN_NAVIGATION = 2;
	const DISPLAYSTATE_UNPUBLISHED = 3;
	
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'cms_hierarchy_item';
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
			[ 
				[ 
					'parent_id',
					'position' 
				],
				'integer' 
			],
			[ 
				[ 
					'display_state' 
				],
				'integer',
				'min' => CmsHierarchyItem::DISPLAYSTATE_MIN_VALUE,
				'max' => CmsHierarchyItem::DISPLAYSTATE_MAX_VALUE 
			],
			[ 
				[ 
					'position' 
				],
				'required' 
			] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
			'id' => Yii::t ( 'app/cms', 'ID' ),
			'parent_id' => Yii::t ( 'app/cms', 'Parent ID' ),
			'position' => Yii::t ( 'app/cms', 'Position' ),
			'display_state' => Yii::t ( 'app/cms', 'Display State' ) 
		];
	}
	
	/**
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getParent() {
		return $this->hasOne ( CmsHierarchyItem::className (), [ 
			'id' => 'parent_id' 
		] );
	}
	
	/**
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getCmsHierarchyItems() {
		return $this->hasMany ( CmsHierarchyItem::className (), [ 
			'parent_id' => 'id' 
		] );
	}
	
	/**
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getCmsMenus() {
		return $this->hasMany ( CmsMenu::className (), [ 
			'cms_hierarchy_item_id' => 'id' 
		] );
	}
}
