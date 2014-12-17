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
use yii\data\ActiveDataProvider;
use schallschlucker\simplecms\models\CmsMenuItem;

/**
 * CmsMenuItemSearch represents the model behind the search form about `common\modules\pn_cms\models\CmsMenuItem`.
 */
class CmsMenuItemSearch extends CmsMenuItem {
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
			[ 
				[ 
					'id',
					'cms_hierarchy_item_id',
					'language',
					'page_content_id',
					'document_id' 
				],
				'integer' 
			],
			[ 
				[ 
					'name' 
				],
				'safe' 
			] 
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function scenarios() {
		// bypass scenarios() implementation in the parent class
		return Model::scenarios ();
	}
	
	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params        	
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params) {
		$query = CmsMenuItem::find ();
		
		$dataProvider = new ActiveDataProvider ( [ 
			'query' => $query 
		] );
		
		if (! ($this->load ( $params ) && $this->validate ())) {
			return $dataProvider;
		}
		
		$query->andFilterWhere ( [ 
			'id' => $this->id,
			'cms_hierarchy_item_id' => $this->cms_hierarchy_item_id,
			'language' => $this->language,
			'page_content_id' => $this->page_content_id,
			'document_id' => $this->document_id 
		] );
		
		$query->andFilterWhere ( [ 
			'like',
			'name',
			$this->name 
		] );
		
		return $dataProvider;
	}
}
