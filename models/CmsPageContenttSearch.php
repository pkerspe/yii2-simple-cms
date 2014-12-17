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
use schallschlucker\simplecms\models\CmsPageContent;

/**
 * CmsPageContenttSearch represents the model behind the search form about `common\modules\pn_cms\models\CmsPageContent`.
 */
class CmsPageContenttSearch extends CmsPageContent {
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
			[ 
				[ 
					'id',
					'language' 
				],
				'integer' 
			],
			[ 
				[ 
					'meta_tags',
					'description',
					'content',
					'javascript',
					'css' 
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
		$query = CmsPageContent::find ();
		
		$dataProvider = new ActiveDataProvider ( [ 
			'query' => $query 
		] );
		
		if (! ($this->load ( $params ) && $this->validate ())) {
			return $dataProvider;
		}
		
		$query->andFilterWhere ( [ 
			'id' => $this->id,
			'language' => $this->language 
		] );
		
		$query->andFilterWhere ( [ 
			'like',
			'meta_tags',
			$this->meta_tags 
		] )->andFilterWhere ( [ 
			'like',
			'description',
			$this->description 
		] )->andFilterWhere ( [ 
			'like',
			'content',
			$this->content 
		] )->andFilterWhere ( [ 
			'like',
			'javascript',
			$this->javascript 
		] )->andFilterWhere ( [ 
			'like',
			'css',
			$this->css 
		] );
		
		return $dataProvider;
	}
}
