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
 * This is the model class for table "cms_content_media".
 *
 * @property integer $id
 * @property integer $content_category_id the id of the content category (folder in media browser) this item belongs to
 * @property string $media_type the media type category
 * @property integer $filesize_bytes the size of the media file in bytes
 * @property string $file_name the name of the file in the file system on the server
 * @property string $file_path the path in the servers media repository
 * @property string $mime_type the mime type of the file
 * @property integer $dimension_width width of image or video if known
 * @property integer $dimension_height height of image or video if known
 * @property string $meta_keywords keywords used in the image browser search
 * @property string $meta_description a short description of the contents used in alt text and in image browser search
 * @property string $modification_datetime last modification date and time of the page content element
 * @property integer $modification_userid user id of the user who modified the page content element for the last time
 * @property string $created_datetime creation date and time of the page content element
 * @property integer $createdby_userid user id of the user who created the page content element
 *
 * @property CmsContentMediaVariation[] $cmsContentMediaVariations
 */
class CmsContentMedia extends \yii\db\ActiveRecord
{
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
    public static function tableName()
    {
        return 'cms_content_media';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['media_type', 'file_name', 'file_path', 'mime_type', 'created_datetime', 'createdby_userid','content_category_id','filesize_bytes'], 'required'],
            [['media_type'], 'string'],
            [['dimension_width', 'dimension_height', 'modification_userid', 'createdby_userid','content_category_id','filesize_bytes'], 'integer'],
            [['modification_datetime', 'created_datetime'], 'safe'],
            [['file_name', 'file_path', 'meta_keywords'], 'string', 'max' => 255],
            [['mime_type'], 'string', 'max' => 30],
            [['meta_description'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('simplecms', 'ID'),
        	'content_category_id' => Yii::t('simplecms', 'Category'),
            'media_type' => Yii::t('simplecms', 'Media Type'),
            'filesize_bytes' => Yii::t('simplecms', 'Filesize'),
        	'file_name' => Yii::t('simplecms', 'File Name'),
            'file_path' => Yii::t('simplecms', 'File Path'),
            'mime_type' => Yii::t('simplecms', 'Mime Type'),
            'dimension_width' => Yii::t('simplecms', 'Dimension Width'),
            'dimension_height' => Yii::t('simplecms', 'Dimension Height'),
            'meta_keywords' => Yii::t('simplecms', 'Meta Keywords'),
            'meta_description' => Yii::t('simplecms', 'Meta Description'),
            'modification_datetime' => Yii::t('simplecms', 'Modification Datetime'),
            'modification_userid' => Yii::t('simplecms', 'Modification Userid'),
            'created_datetime' => Yii::t('simplecms', 'Created Datetime'),
            'createdby_userid' => Yii::t('simplecms', 'Createdby Userid'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentMediaVariations()
    {
        return $this->hasMany(CmsContentMediaVariation::className(), ['parent_content_media_id' => 'id']);
    }
}
?>