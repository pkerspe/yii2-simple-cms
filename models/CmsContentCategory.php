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
 * This is the model class for table "cms_content_category".
 *
 * @property integer $id the id of the category
 * @property integer $parent_id the parnet category id to allow building a tree structure
 * @property string $displayname the name of the virtual folder / category
 *
 * @property CmsContentCategory $parent
 * @property CmsContentCategory[] $cmsContentCategories
 */
class CmsContentCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cms_content_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['displayname'], 'required'],
            [['id', 'parent_id'], 'integer'],
            [['displayname'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/cms', 'ID'),
            'parent_id' => Yii::t('app/cms', 'Parent ID'),
            'displayname' => Yii::t('app/cms', 'Displayname'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(CmsContentCategory::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentCategories()
    {
        return $this->hasMany(CmsContentCategory::className(), ['parent_id' => 'id']);
    }
}
?>