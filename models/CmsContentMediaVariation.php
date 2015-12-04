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
 * This is the model class for table "cms_content_media_variation".
 *
 * @property integer $id 
 * @property integer $parent_content_media_id the parent item where this variation is beloning to
 * @property integer $dimension_width width (if applicable) of this media item
 * @property integer $dimension_height height (if applicable) of this media item
 * @property string $mime_type mime_type of this variation
 * @property string $file_name the name of the file in the file system on the server
 * @property string $file_path the path in the servers media repository
 * @property integer $filesize_bytes the size of the media file in bytes
 *
 * @property CmsContentMedia $parentContentMedia
 */
class CmsContentMediaVariation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_content_media_variation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_content_media_id', 'mime_type', 'file_name', 'file_path','filesize_bytes'], 'required'],
            [['parent_content_media_id', 'dimension_width', 'dimension_height','filesize_bytes'], 'integer'],
            [['mime_type'], 'string', 'max' => 30],
            [['file_name', 'file_path'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('simplecms', 'ID'),
            'parent_content_media_id' => Yii::t('simplecms', 'Parent Content Media ID'),
        	'filesize_bytes' => Yii::t('simplecms', 'Filesize'),
            'dimension_width' => Yii::t('simplecms', 'Dimension Width'),
            'dimension_height' => Yii::t('simplecms', 'Dimension Height'),
            'mime_type' => Yii::t('simplecms', 'Mime Type'),
            'file_name' => Yii::t('simplecms', 'File Name'),
            'file_path' => Yii::t('simplecms', 'File Path'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentContentMedia()
    {
        return $this->hasOne(CmsContentMedia::className(), ['id' => 'parent_content_media_id']);
    }
}
?>