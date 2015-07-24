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
/**
 * A widget to render a complete sitemap of the cms tree structure for all published cms menu items. The generated source will be html lists using ul-tags
 * The following parameters are supported:
 * @param $languageId integer the id of the language to render the navigation for (the language of the menu items). If paramete is omitted, the current app language (as returned by Yii::$app->language) is used and converted to languageid via the configured languageManager
 * @param $levelDepth the maximum level depth to be rendered. 0 based, so root = Level 0, Area Items = Level 1. Default is 99 Levels.
 * @param $displayRootItem boolean true displays the root item, false supresses the root item
 */
class CmsSitemapWidget extends Widget {
	
	public $languageId;
	public $levelDepth = 99;
	public $displayRootItem = true;
	
	public function init() {
		if($this->languageId == null){
		    if(Yii::$app->getModule('simplecms_frontend') == null)
		        throw new \Exception('Could not find module with name simplecms_frontend. Make sure you included the moduel in your configuration');
			$this->languageId = Frontend::getLanguageManagerStatic()->getLanguageIdForString(Yii::$app->language);
		}
		parent::init ();
	}
	
	public function renderChildrenRecursive($simpleHierarchyItem, $currentLevel, $maxLevel, &$widgetHtml){
	    if($currentLevel >= $maxLevel)
	        return;
	    
	    if(count($simpleHierarchyItem->children) > 0){
	        $widgetHtml .= '<ul class="cms-sitemap-level-'.$currentLevel.'">'.PHP_EOL;
	        foreach($simpleHierarchyItem->getAllChildren() as $childItem){
	            /* @var $areaItem SimpleHierarchyItem */
	            $widgetHtml .= '<li class="cms-sitemap-level-'.$currentLevel.'">'.$childItem->getLinkTag();
	            if(count($childItem->children) > 0){
	                $this->renderChildrenRecursive($childItem, $currentLevel+1, $maxLevel, $widgetHtml);
	            }
	            $widgetHtml .= '</li>'.PHP_EOL;
	        }
	        $widgetHtml .= '</ul>'.PHP_EOL;
	    }
		return $widgetHtml;
	}
	
	public function run() {
		$widgetHtml = '<ul class="cms-sitemap-level-0">'.PHP_EOL;
		
		/* @var $rootHierarchyItem SimpleHierarchyItem */
		
		$rootHierarchyItem = NavigationController::getRootHierarchyItemCached($this->languageId,false,false);
		
		if($this->displayRootItem){
			$widgetHtml .= '<li class="cms-sitemap-level-0">'.$rootHierarchyItem->getLinkTag();
		}
		
		$this->renderChildrenRecursive($rootHierarchyItem, 1, $this->levelDepth, $widgetHtml);
		
		if($this->displayRootItem){
            $widgetHtml .= '</li>'.PHP_EOL;
		}
		$widgetHtml .= '</ul>'.PHP_EOL;
		return $widgetHtml;
	}
}
?>