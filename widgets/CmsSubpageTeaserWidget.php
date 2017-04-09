<?php
/*
 * This file is part of the simple cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace schallschlucker\simplecms\widgets;

use schallschlucker\simplecms\models\CmsHierarchyItem;
use schallschlucker\simplecms\models\CmsMenuItem;
use schallschlucker\simplecms\models\CmsPageContent;
use Yii;
use yii\base\Widget;

/**
 * A widget to render a tabular listing of subpage teasers (sub pages (if any) of the menu item id given in the parameter).
 * The following parameters are supported:
 *
 * @param $cmsHierarchyItem   CmsHierarchyItem required: the hierarchy item to render the subpage teasers for
 * @param $propertiesToRender string optional: a comma separated list of property names to render for each subpage. Will be displayed in the order of appearance in the csv-string. If not provided a default set of properies will be rendered
 */
class CmsSubpageTeaserWidget extends Widget
{

    public $propertiesToRender = null;
    /* @var $cmsHierarchyItem CmsHierarchyItem */
    public $cmsHierarchyItem = null;
    public $renderCreationDate = false;

    public function init()
    {
        parent::init();
    }

    public function renderSubpageTeasers(&$widgetHtml)
    {
        /* @var $cmsHierarchyItem CmsHierarchyItem */
        $cmsHierarchyItem = $this->cmsHierarchyItem;
        $cmsChildHierarchyItems = $cmsHierarchyItem->getCmsHierarchyItems()->where(['display_state' => CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION])->with('cmsMenus')->orderBy("position asc")->all();

        if (count($cmsChildHierarchyItems) > 0) {
            $widgetHtml .= '<ul class="cms-subpage-teaser-list">' . PHP_EOL;

            foreach ($cmsChildHierarchyItems as $child) {
                /* @var $child CmsHierarchyItem */
                $cmsMenus = $child->cmsMenus;  //TODO: probably need to determine proper lang version here by applying a where filter
                if (count($cmsMenus) > 0) {
                    /* @var $cmsMenuItem CmsMenuItem */
                    $cmsMenuItem = $cmsMenus[0];
                    /* @var $pageContent CmsPageContent */
                    $pageContent = $cmsMenuItem->getPageContent()->one();
                    $widgetHtml .= '<li class="cms-subpage-teaser-item">' . PHP_EOL;
                    if ($this->renderCreationDate) {
                        $widgetHtml .= '<span class="cms-subpage-teaser-item-date"><a href="' . $cmsMenus[0]->getFormattedUrl() . '">' . date_format(date_create($pageContent->created_datetime), "d.m.Y") . '</a></span>';
                    }

                    $displayName = (!empty($pageContent) && !empty($pageContent->teaser_name)) ? $pageContent->teaser_name : $cmsMenuItem->name;
                    $displayDescription = (!empty($pageContent) && !empty($pageContent->teaser_text)) ? $pageContent->teaser_text : (!empty($pageContent)) ? $pageContent->description : "";

                    $widgetHtml .= '<span class="cms-subpage-teaser-item-title"><a href="' . $cmsMenus[0]->getFormattedUrl() . '">' . $displayName . '</a></span>';

                    if (!empty($pageContent) && !empty($pageContent->teaser_image_id)) {
                        $widgetHtml .= '<span class="cms-subpage-teaser-item-image"><img src="/media_manager/media/get-media?mediaItemId=' . $pageContent->teaser_image_id . '"></span>';
                    }

                    $widgetHtml .= ' <span class="cms-subpage-teaser-item-description">' . $displayDescription . '</span>';
                    $widgetHtml .= ' <span class="cms-subpage-teaser-item-morelink"><a href="' . $cmsMenus[0]->getFormattedUrl() . '">' . Yii::t('simplecms', 'read more...') . '</a></span>';
                    $widgetHtml .= '</li>' . PHP_EOL;
                }
            }

            $widgetHtml .= '</ul>' . PHP_EOL;
        }
        return $widgetHtml;
    }

    public function run()
    {
        $widgetHtml = '<div class="cms-subpage-teasers">' . PHP_EOL;

        $this->renderSubpageTeasers($widgetHtml);

        $widgetHtml .= '</div>' . PHP_EOL;
        return $widgetHtml;
    }
}