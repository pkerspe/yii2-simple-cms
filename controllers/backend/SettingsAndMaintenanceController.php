<?php

/*
 * This file is part of the simple cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace schallschlucker\simplecms\controllers\backend;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use schallschlucker\simplecms\models\CmsAdministrationMainTreeViewForm;
use schallschlucker\simplecms\models\MenuItemAndContentForm;
use schallschlucker\simplecms\models\CmsDocument;
use schallschlucker\simplecms\models\CmsMenuItem;
use schallschlucker\simplecms\models\CmsPageContent;
use schallschlucker\simplecms\models\CmsHierarchyItem;
use schallschlucker\simplecms\models\CmsMaintenanceForm;
use schallschlucker\simplecms\controllers\backend\DefaultController;
use yii\db\Expression;

/**
 * The settings and maintenance controller of the CMS Backend.
 * Default action is actionIndex, which renders an all in one administration mask for creating and maintaining the page tree.
 *
 * @menuLabel CMS Administration
 * @menuIcon <i class="fa fa-files-o"></i>
 */
class SettingsAndMaintenanceController extends Controller {


	/**
	 * cms maintenance page
	 * @menuLabel Maintenance functions
	 * @menuIcon <span class="glyphicon glyphicon-cog"></span>
	 * @functionalRight cmsBackendWrite
	 * @return string
	 */
	public function actionIndex() {
		$model = new CmsMaintenanceForm ();
		$result = [ ];
		if ($model->load ( Yii::$app->request->post () )) {
			if ($model->checkPositionsrecursive) {
				$updateCounter = 0;
				$failedUpdateCounter = 0;
				$checkedItems = 0;
				SettingsAndMaintenanceController::fixItemPositionsForChildren ( DefaultController::$ROOT_HIERARCHY_ITEM_ID, $updateCounter, $failedUpdateCounter, $checkedItems, true );
				$result ['updateCounter'] = $updateCounter;
				$result ['failedUpdateCounter'] = $failedUpdateCounter;
				$result ['checkedItems'] = $checkedItems;
			}
		}
		return $this->render ( 'maintenance', [
			'data' => $result,
			'model' => $model
		] );
	}
	
	//counter for profiling iterations
	public static $iterationCounter = 0;

	/**
	 * basically a test action for development puposes only
	 *
	 * @menuLabel Profiling test page
	 * @menuIcon <i class="fa fa-cogs"></i>
	 * @functionalRight cmsBackendRead
	 *
	 * @return \yii\web\Response|string
	 */
	public function actionProfiling() {
		SettingsAndMaintenanceController::$iterationCounter = 0;
		Yii::beginProfile ( __METHOD__, 'simplecms' );
		$results = DefaultController::getMenuTree ( 1, 999, false );
		Yii::endProfile ( __METHOD__, 'simplecms' );
		
		return $this->render ( 'profiling', [ 
			'data' => DefaultController::$iterationCounter 
		] );
	}
	
	/**
	 * create a random page structure for testing
	 *
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 *
	 * @param unknown $startItemId
	 *        	the item id where to create the dummy structure below
	 * @param unknown $languageId
	 *        	the language id for which to create the new menu items
	 * @param unknown $maxdepth
	 *        	the maximum directory/acnestors depth to create new items for (this is a maximum level depth, due to the fact that a random function is used to either create children or siblings, it cannot be guaranteed if the level depth is reached)
	 * @param unknown $maxitemCount
	 *        	the number of items to create
	 * @return \yii\web\Response|string
	 */
	public function actionCreateDummyTreeStructure($startItemId, $languageId, $maxdepth, $maxitemCount) {
		$currentItemCount = 0;
		while ( $currentItemCount < $maxitemCount ) {
			$currentItemCount = $this->generateRandomChildOrSibling ( $startItemId, $startItemId, $languageId, $maxdepth, $maxitemCount, 0, $currentItemCount );
		}
		// tree generation does not set proper position values, so we fix all positions values recursive after creation.
		$updateCounter = 0;
		$failedUpdateCounter = 0;
		$checkedItems = 0;
		SettingsAndMaintenanceController::fixItemPositionsForChildren ( $startItemId, $updateCounter, $failedUpdateCounter, $checkedItems, true );
		
		return $this->render ( 'profiling', [ 
			'data' => $currentItemCount 
		] );
	}
	
	/**
	 * internal helper method to generate a dummy tree for testing.
	 * After this function is called, a call to fixItemPositionsForChildren() should be performed to ensure the position values are integer for the generated subtree
	 *
	 * @param integer $parentItemId        	
	 * @param integer $currentItemId        	
	 * @param integer $languageId        	
	 * @param integer $maxdepth        	
	 * @param integer $maxitemCount        	
	 * @param integer $currentDepth        	
	 * @param integer $currentItemCount        	
	 * @return integer number of generated items
	 */
	private function generateRandomChildOrSibling($parentItemId, $currentItemId, $languageId, $maxdepth, $maxitemCount, $currentDepth, $currentItemCount) {
		if ($currentItemCount < $maxitemCount) {
			if ($currentDepth == $maxdepth) {
				// create sibling / or nothing since max depth already reached
				if (rand ( 0, 1 )) {
					$name = 'auto generated test menu ' . $currentItemCount;
					$result = $this->actionCreateHierarchyItemJson ( $parentItemId, $name, 1, 1, 1, false );
					$simpleHierarchyItem = $result ['item'];
					return $currentItemCount + 1;
				}
			} else if ($currentDepth == 0) {
				// only create child nodes when depth == 0
				$name = 'auto generated test menu ' . $currentItemCount;
				$result = $this->actionCreateHierarchyItemJson ( $currentItemId, $name, 1, 1, 1, false );
				if (isset ( $result ['item'] )) {
					$simpleHierarchyItem = $result ['item'];
					$currentItemCount = $this->generateRandomChildOrSibling ( $currentItemId, $simpleHierarchyItem->id, $languageId, $maxdepth, $maxitemCount, $currentDepth + 1, $currentItemCount + 1 );
				} else {
					print_r ( $result );
				}
				return $currentItemCount + 1;
			} else {
				// random decission if a sibling or child should be created
				if (rand ( 0, 1 )) {
					// create sibling
					$name = 'auto generated test menu ' . $currentItemCount;
					$result = $this->actionCreateHierarchyItemJson ( $parentItemId, $name, 1, 1, 1, false );
					$simpleHierarchyItem = $result ['item'];
					// go into recursion
					$currentItemCount = $this->generateRandomChildOrSibling ( $currentItemId, $simpleHierarchyItem->id, $languageId, $maxdepth, $maxitemCount, $currentDepth, $currentItemCount + 1 );
					return $currentItemCount + 1;
				} else {
					// create child
					$name = 'auto generated test menu ' . $currentItemCount;
					$result = $this->actionCreateHierarchyItemJson ( $currentItemId, $name, 1, 1, 1, false );
					$simpleHierarchyItem = $result ['item'];
					$currentItemCount = $this->generateRandomChildOrSibling ( $currentItemId, $simpleHierarchyItem->id, $languageId, $maxdepth, $maxitemCount, $currentDepth + 1, $currentItemCount + 1 );
					return $currentItemCount + 1;
				}
			}
		}
		return $currentItemCount;
	}

	
	/**
	 * Fix the position values of all child items for the given parent id.
	 * This should be called once a new item is inserted amongst other siblings or just to perform an integrity check and avoid duplicate position values amongst siblings.
	 * This function is quite expensive due to the sql calls (espcially in recusrion mode) so it must not be used in often called functions.
	 *
	 * @param unknown $parentItemId
	 *        	the item id to get the children for an set the position values
	 * @param boolean $recurseTree
	 *        	recurse into the subtree structure
	 * @param unknown $updateCounter        	
	 * @param unknown $failedUpdateCounter        	
	 * @param unknown $checkedItems        	
	 */
	public static function fixItemPositionsForChildren($parentItemId, &$updateCounter, &$failedUpdateCounter, &$checkedItems, $recurseTree = false) {
		$childItems = CmsHierarchyItem::find ()->where ( [ 
			'parent_id' => $parentItemId 
		] )->orderby ( 'position ASC' )->all ();
		foreach ( $childItems as $arrayIndex => $item ) {
			$checkedItems ++;
			$positionValue = $arrayIndex + 1;
			if ($item->position != $positionValue) {
				$item->position = $positionValue;
				if ($item->save ()) {
					$updateCounter ++;
				} else {
					$failedUpdateCounter ++;
				}
			}
			if ($recurseTree) {
				SettingsAndMaintenanceController::fixItemPositionsForChildren ( $item->id, $updateCounter, $failedUpdateCounter, $checkedItems, true );
			}
		}
	}
}