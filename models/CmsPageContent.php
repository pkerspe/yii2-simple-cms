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

use schallschlucker\simplecms\behaviours\CmsBlameableBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "cms_page_content".
 *
 * @property integer       $id
 * @property integer       $language               the language id of this page content
 * @property string        $html_title             value to override the html title tag. If empy the menu item name will be used instead. Title will be prefixed/suffixed with configured string from module config is set (check Frontnend module parameters htmlTitlePrefix and htmlTitleSuffix)
 * @property string        $metatags_general       metatags to be rendered in the frontend view page
 * @property string        $meta_keywords          keywords to be used in the search as well as in the metatags in the frontend
 * @property string        $description            a short description of the contents of this page. Used in Metatags as well as to display a preview of the page content in the search results or teaser lists
 * @property string        $content                the content of this page (HTML)
 * @property boolean       $render_subpage_teasers Should the cms render a list of subpage teasers below the content area?
 * @property string        $javascript             additional javascript to be rendered on the bottom of the page html source
 * @property string        $css                    additional css definitions to be rendered on top of the page in the head section
 * @property string        $modification_datetime  last modification date and time of the page content element
 * @property integer       $modification_userid    user id of the user who modified the page content element for the last time
 * @property string        $created_datetime       creation date and time of the page content element
 * @property integer       $createdby_userid       user id of the user who created the page content element
 * @property string        $teaser_text            some teaser text (max 500 chars) for the page that can be used in widgets
 * @property integer       $teaser_image_id        the id of the teaser image media item to be displayed e.g. in sub page list widgets
 * @property string        $teaser_name            Name of the teaser to be displayed e.g. as link text
 * @property string        $teaser_link            Custom link for the teaser if we do not want to link to the page itsself
 *
 * @property CmsMenuItem[] $cmsMenuItems
 */
class CmsPageContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_page_content}}';
    }

    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_datetime',
                'updatedAtAttribute' => 'modification_datetime',
                'value'              => new Expression ('NOW()')
            ],
            [
                'class'              => CmsBlameableBehavior::className(),
                'createdByAttribute' => 'createdby_userid',
                'updatedByAttribute' => 'modification_userid'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'language',
                    'created_datetime',
                    'createdby_userid'
                ],
                'required'
            ],
            [
                [
                    'language',
                    'modification_userid',
                    'createdby_userid',
                    'teaser_image_id',
                    'render_subpage_teasers'
                ],
                'integer'
            ],
            [
                [
                    'content',
                    'javascript',
                    'css',
                    'html_title',
                ],
                'string'
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
                    'metatags_general',
                    'description',
                    'teaser_text',
                    'teaser_name',
                    'teaser_link'
                ],
                'string',
                'max' => 500
            ],
            [
                [
                    'meta_keywords',
                    'html_title',
                ],
                'string',
                'max' => 255
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                     => Yii::t('simplecms', 'ID'),
            'language'               => Yii::t('simplecms', 'Language'),
            'html_title'             => Yii::t('simplecms', 'HTML Title'),
            'metatags_general'       => Yii::t('simplecms', 'Metatags General'),
            'meta_keywords'          => Yii::t('simplecms', 'Meta Keywords'),
            'description'            => Yii::t('simplecms', 'Description'),
            'content'                => Yii::t('simplecms', 'Content'),
            'javascript'             => Yii::t('simplecms', 'Javascript'),
            'css'                    => Yii::t('simplecms', 'Css'),
            'modification_datetime'  => Yii::t('simplecms', 'Modification Datetime'),
            'modification_userid'    => Yii::t('simplecms', 'Modification Userid'),
            'created_datetime'       => Yii::t('simplecms', 'Created Datetime'),
            'createdby_userid'       => Yii::t('simplecms', 'Createdby Userid'),
            'render_subpage_teasers' => Yii::t('simplecms', 'Render Subpage Tesaers'),
            'teaser_image_id'        => Yii::t('simplecms', 'Teaser Image'),
            'teaser_text'            => Yii::t('simplecms', 'Teaser Text'),
            'teaser_name'            => Yii::t('simplecms', 'Teaser Name'),
            'teaser_link'            => Yii::t('simplecms', 'Teaser Link'),
        ];
    }

    /**
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTeaserImageMedia()
    {
        return $this->hasOne(CmsContentMedia::className(), [
            'id' => 'teaser_image_id'
        ]);
    }

    /**
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCmsMenuItems()
    {
        return $this->hasMany(CmsMenuItem::className(), [
            'page_content_id' => 'id'
        ]);
    }
}
