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
use schallschlucker\simplecms\models\CmsHierarchyItem;

/**
 * CmsHierarchyItemSearch represents the model behind the search form about `common\modules\pn_cms\models\CmsHierarchyItem`.
 */
class CmsHierarchyItemSearch extends CmsHierarchyItem {
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
			[ 
				[ 
					'id',
					'parent_id',
					'position',
					'display_state' 
				],
				'integer' 
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
		$query = CmsHierarchyItem::find ();
		
		$dataProvider = new ActiveDataProvider ( [ 
			'query' => $query 
		] );
		
		if (! ($this->load ( $params ) && $this->validate ())) {
			return $dataProvider;
		}
		
		$query->andFilterWhere ( [ 
			'id' => $this->id,
			'parent_id' => $this->parent_id,
			'position' => $this->position,
			'display_state' => $this->display_state 
		] );
		
		return $dataProvider;
	}
}
