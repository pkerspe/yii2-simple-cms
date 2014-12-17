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
use schallschlucker\simplecms\models\CmsDocument;

/**
 * CmsDocumentSearch represents the model behind the search form about `common\modules\pn_cms\models\CmsDocument`.
 */
class CmsDocumentSearch extends CmsDocument {
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
					'file_name',
					'file_path',
					'mime_type' 
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
		$query = CmsDocument::find ();
		
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
			'filename',
			$this->filename 
		] )->andFilterWhere ( [ 
			'like',
			'filename_path',
			$this->filename_path 
		] )->andFilterWhere ( [ 
			'like',
			'mime_type',
			$this->mime_type 
		] );
		
		return $dataProvider;
	}
}
