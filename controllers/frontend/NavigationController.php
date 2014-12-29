<?php

/*
 * This file is part of the simple cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace schallschlucker\simplecms\controllers\frontend;

use Yii;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use yii\web\Controller;
use schallschlucker\simplecms\models\CmsAdministrationMainTreeViewForm;
use schallschlucker\simplecms\models\MenuItemAndContentForm;
use schallschlucker\simplecms\models\CmsDocument;
use schallschlucker\simplecms\models\CmsMenuItem;
use schallschlucker\simplecms\models\CmsPageContent;
use schallschlucker\simplecms\models\CmsHierarchyItem;
use schallschlucker\simplecms\models\CmsMaintenanceForm;
use schallschlucker\simplecms\controllers\backend\SettingsAndMaintenanceController;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use schallschlucker\simplecms\controllers\backend\DefaultController;

/**
 * The navigation controller of the CMS frontend to provide actions for displaying tree structures
 *
 * @menuLabel CMS Frontend Controller
 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
 */
class NavigationController extends Controller {
	
	public static $BASE_CACHE_KEY_FRONTEND_NAVIGATION = 'NavigationController-navigation-';
	public static $FRONTEND_NAVIGATION_CACHE_LIVETIME_SECONDS = 1000;
	
	public $defaultAction = 'page-tree-as-xml';
	
	/**
	 * display the hierarchy tree of all items with display state 'visible' and for the current language
	 * @menuLabel display hierarchy tree as xml
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 */
	public function actionPageTreeAsXml() {
		$currentLanguageId = Yii::$app->controller->module->getLanguageManager()->getLanguageIdForString(Yii::$app->language);
		$simpleHierarchyItem  = NavigationController::getRootHierarchyItemCached($currentLanguageId);
		
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'text/xml; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return $simpleHierarchyItem->getAsXmlString();
	}

	/**
	 * 
	 * @param unknown $currentLanguageId
	 * @param string $hideMissingLanguages
	 * @param string $removeHierarchyItemsWithNoContent
	 * @return \schallschlucker\simplecms\models\SimpleHierarchyItem
	 */
	public static function getRootHierarchyItemCached($currentLanguageId,$hideMissingLanguages = true, $removeHierarchyItemsWithNoContent = true){
		Yii::beginProfile(__METHOD__,'CMS-NAVIGATION');
		$cacheKey = NavigationController::$BASE_CACHE_KEY_FRONTEND_NAVIGATION.$currentLanguageId.'-'.$hideMissingLanguages.'-'.$removeHierarchyItemsWithNoContent;
		$simpleHierarchyItem  = Yii::$app->controller->module->getCachedValue($cacheKey,false);
		if($simpleHierarchyItem === false){
			Yii::info('no cached page tree found for given parameters, will rebuild tree from database');
			$simpleHierarchyItem  = DefaultController::getMenuTree($currentLanguageId, 9999, $hideMissingLanguages, $removeHierarchyItemsWithNoContent, [CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION]);
			$simpleHierarchyItem->finalizeForOutput();
			Yii::$app->controller->module->setCacheValue($cacheKey,$simpleHierarchyItem,NavigationController::$FRONTEND_NAVIGATION_CACHE_LIVETIME_SECONDS);
		}
		Yii::endProfile(__METHOD__);
		return $simpleHierarchyItem;
	}
}
?>