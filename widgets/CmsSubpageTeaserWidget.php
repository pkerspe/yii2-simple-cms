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

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use schallschlucker\simplecms\controllers\backend\CmsHierarchyController;
use schallschlucker\simplecms\controllers\backend\DefaultController;
use yii\helpers\Url;
use schallschlucker\simplecms\controllers\frontend\NavigationController;
use schallschlucker\simplecms\models\SimpleHierarchyItem;
use schallschlucker\simplecms\Frontend;
use schallschlucker\simplecms\models\CmsMenuItem;
use schallschlucker\simplecms\models\CmsHierarchyItem;
use schallschlucker\simplecms\models\CmsPageContent;
/**
 * A widget to render a tabular listing of subpage teasers (sub pages (if any) of the menu item id given in the parameter).
 * The following parameters are supported:
 * @param $cmsHierarchyItem CmsHierarchyItem required: the hierarchy item to render the subpage teasers for 
 * @param $propertiesToRender string optional: a comma separated list of property names to render for each subpage. Will be displayed in the order of appearance in the csv-string. If not provided a default set of properies will be rendered
 */
class CmsSubpageTeaserWidget extends Widget {
	
	public $propertiesToRender = null;
	/* @var $cmsHierarchyItem CmsHierarchyItem */
	public $cmsHierarchyItem = null;
	
	public function init() {
		parent::init ();
	}
	
	public function renderSubpageTeasers(&$widgetHtml){
	    /* @var $cmsHierarchyItem CmsHierarchyItem */
	    $cmsHierarchyItem = $this->cmsHierarchyItem;
	    $cmsChildHierarchyItems = $cmsHierarchyItem->getCmsHierarchyItems()->where(['display_state' => CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION])->all();
	    
	    if(count($cmsChildHierarchyItems) > 0){
	        $widgetHtml .= '<ul class="cms-subpage-teaser-list">'.PHP_EOL;
	        
	        foreach ($cmsChildHierarchyItems as $child){
	            /* @var $child CmsHierarchyItem */
	            $cmsMenus = $child->getCmsMenus()->all();  //TODO: probably need to determine proper lang version here by applying a where filter
	            if(count($cmsMenus) > 0){
	                /* @var $cmsMenuItem CmsMenuItem */
	                $cmsMenuItem = $cmsMenus[0];
	                /* @var $pageContent CmsPageContent */
	                $pageContent = $cmsMenuItem->getPageContent()->one();
    	            $widgetHtml .= '<li class="cms-subpage-teaser-item">'.PHP_EOL;
    	            $widgetHtml .= '<span class="cms-subpage-teaser-item-date"><a href="'.$cmsMenus[0]->getFormattedUrl().'">'.date_format(date_create($pageContent->created_datetime),"d. M Y").'</a></span>';
    	            $widgetHtml .= '<span class="cms-subpage-teaser-item-title"><a href="'.$cmsMenus[0]->getFormattedUrl().'">'.$cmsMenuItem->name.'</a></span>';
    	            $widgetHtml .= '<span class="cms-subpage-teaser-item-description">'.$pageContent->description.'</span>';
    	            $widgetHtml .= '<span class="cms-subpage-teaser-item-morelink"><a href="'.$cmsMenus[0]->getFormattedUrl().'">'.Yii::t('simplecms', 'read more...').'</a></span>';
    	            $widgetHtml .= '</li>'.PHP_EOL;
	            }
	        }
	        
	        $widgetHtml .= '</ul>'.PHP_EOL;
	    }
		return $widgetHtml;
	}
	
	public function run() {
		$widgetHtml = '<div class="cms-subpage-teasers">'.PHP_EOL;
		
		$this->renderSubpageTeasers($widgetHtml);
		
		$widgetHtml .= '</div>'.PHP_EOL;
		return $widgetHtml;
	}
}
?>