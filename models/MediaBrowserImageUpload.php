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

class MediaBrowserImageUpload extends Model {
	
	public $targetCategoryId;
    /**
     * @var UploadedFile|Null file attribute
     */
    public $file;
	
	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [ 
			'targetCategoryId' => Yii::t ( 'app/cms', 'folder' ),
			'file' => Yii::t ( 'app/cms', 'files' ),
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [ 
			[['targetCategoryId','file'],'required'],
			[['file'], 'file', 'maxFiles' => 10],
			[['targetCategoryId'], 'integer', 'min' => 1],
		];
	}
}
?>
