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
use schallschlucker\simplecms\models\CmsMenuItem;
use schallschlucker\simplecms\models\CmsHierarchyItem;
use schallschlucker\simplecms\models\CmsHierarchyItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Expression;

/**
 * CmsHierarchyController implements the CRUD actions for CmsHierarchyItem model.
 * @menuLabel CMS Administration
 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
 */
class CmsHierarchyController extends Controller {
	public function behaviors() {
		return [ 
			'verbs' => [ 
				'class' => VerbFilter::className (),
				'actions' => [ 
					'delete' => [ 
						'post' 
					] 
				] 
			] 
		];
	}
	
	//counter for profiling iterations
	public static $iterationCounter = 0;
	
	/**
	 * basically a test action for development puposes only
	 * 
	 * @menuLabel Profiling test page
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendRead
	 * 
	 * @return \yii\web\Response|string
	 */
	public function actionProfiling() {
		CmsHierarchyController::$iterationCounter = 0;
		Yii::beginProfile(__METHOD__,'simplecms');
		$results = $this->getMenuTree(1, 999, false);
		Yii::endProfile(__METHOD__,'simplecms');
		
		return $this->render ( 'profiling', [
			'data' => CmsHierarchyController::$iterationCounter,
		] );
	}
	
	/**
	 * create a random page structure for testing
	 * 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 * 
	 * @param unknown $startItemId the item id where to create the dummy structure below
	 * @param unknown $languageId the language id for which to create the new menu items
	 * @param unknown $maxdepth the maximum directory/acnestors depth to create new items for (this is a maximum level depth, due to the fact that a random function is used to either create children or siblings, it cannot be guaranteed if the level depth is reached) 
	 * @param unknown $maxitemCount the number of items to create
	 * @return \yii\web\Response|string
	 */
	public function actionCreateDummyTreeStructure($startItemId, $languageId, $maxdepth, $maxitemCount){
		$currentItemCount = 0;
		while($currentItemCount < $maxitemCount){
			$currentItemCount = $this->generateRandomChildOrSibling($startItemId, $startItemId, $languageId, $maxdepth, $maxitemCount, 0, $currentItemCount);
		}
		//tree generation does not set proper position values, so we fix all positions values recursive after creation.
		$updateCounter = 0;
		$failedUpdateCounter = 0;
		$checkedItems = 0;
		$this->fixItemPositionsForChildren($startItemId,$updateCounter,$failedUpdateCounter,$checkedItems,true);
		
		return $this->render ( 'profiling', [
			'data' => $currentItemCount,
		]);
	}
	
	/**
	 * internal helper method to generate a dummy tree for testing.
	 * After this function is called, a call to fixItemPositionsForChildren() should be performed to ensure the position values are integer for the generated subtree
	 * @param integer $parentItemId
	 * @param integer $currentItemId
	 * @param integer $languageId
	 * @param integer $maxdepth
	 * @param integer $maxitemCount
	 * @param integer $currentDepth
	 * @param integer $currentItemCount
	 * @return integer number of generated items
	 */
	private function generateRandomChildOrSibling($parentItemId, $currentItemId, $languageId, $maxdepth, $maxitemCount, $currentDepth, $currentItemCount){
		if($currentItemCount < $maxitemCount){
			if($currentDepth == $maxdepth){
				//create sibling / or nothing since max depth already reached
				if(rand(0,1)){
					$name = 'auto generated test menu '.$currentItemCount;
					$result = $this->actionCreateHierarchyItemJson($parentItemId, $name, 1 , 1, 1,false);
					$simpleHierarchyItem = $result['item'];
					return $currentItemCount+1;		
				}
			} else if($currentDepth == 0) { 
				//only create child nodes when depth == 0
				$name = 'auto generated test menu '.$currentItemCount;
				$result = $this->actionCreateHierarchyItemJson($currentItemId, $name, 1 , 1, 1,false);
				if(isset($result['item'])){
					$simpleHierarchyItem = $result['item'];
					$currentItemCount = $this->generateRandomChildOrSibling($currentItemId, $simpleHierarchyItem->id, $languageId, $maxdepth, $maxitemCount, $currentDepth+1, $currentItemCount+1);
				} else {
					print_r($result);
				}
				return $currentItemCount+1;
			} else {
				//random decission if a sibling or child should be created
				if(rand(0,1)){
					//create sibling
					$name = 'auto generated test menu '.$currentItemCount;
					$result = $this->actionCreateHierarchyItemJson($parentItemId, $name, 1 , 1, 1,false);
					$simpleHierarchyItem = $result['item'];
					//go into recursion
					$currentItemCount = $this->generateRandomChildOrSibling($currentItemId, $simpleHierarchyItem->id , $languageId, $maxdepth, $maxitemCount, $currentDepth, $currentItemCount+1);
					return $currentItemCount+1;
				} else {
					//create child
					$name = 'auto generated test menu '.$currentItemCount;
					$result = $this->actionCreateHierarchyItemJson($currentItemId, $name, 1 , 1, 1,false);
					$simpleHierarchyItem = $result['item'];
					$currentItemCount = $this->generateRandomChildOrSibling($currentItemId, $simpleHierarchyItem->id, $languageId, $maxdepth, $maxitemCount, $currentDepth+1, $currentItemCount+1);
					return $currentItemCount+1;
				}
			}
		}
		return $currentItemCount;
	}
	
	/**
	 * get the complete menu / hierarchy tree for the cms navigation structure
	 * 
	 * @functionalRight cmsBackendRead
	 * 
	 * @param language integer the language id to get the cms menu items for
	 * @param expandLevel integer the level depth, until which the folders should be marked as expanded ($expanded = true)
	 * @param hideMissingLanguages boolean hide or show hierarchy items that do not have a translation (cms menu item) in the requested language
	 * @param filterDisplayState array an array if integers indicating which displayStates should be filtered from the results (@see CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION)
	 * @return array an SimpleHierarchyItem instance, containing all subnodes in a node-tree structure (children property holds children of each node)
	 */
	public static function getMenuTree($language, $expandLevel, $hideMissingLanguages, $removeHierarchyItemsWithNoContent = false, $filterDisplayState = []) {
		$language = intval ( $language );
		$expandLevel = intval ( $expandLevel );
		
		// query all hierarchy items (including hidden, etc.)
		$hierarchyQuery = CmsHierarchyItem::find ()->orderby ( 'parent_id ASC, position DESC' )->asarray ( true ); //****** IMPORTANT ******* do not modify the order by clause, since it is required by the recursive subroutine to work properly
		if (count ( $filterDisplayState ) > 0)
			$hierarchyQuery->where ( [ 
				'display_state' => $filterDisplayState 
			] );
		$hierarchyItems = $hierarchyQuery->all ();
		
		// query all menu items (check if missing should be filtered)
		$query = CmsMenuItem::find ()->orderby ( 'id ASC' )->asarray ( true );
		$menuItemsForLanguage = $query->all ();
		
		$menuItemIndex = [ ]; // two dimensions, first dimension is id, second is language
		foreach ( $menuItemsForLanguage as $menuItem ) {
			$menuItemIndex [$menuItem ['cms_hierarchy_item_id']] [$menuItem ['language']] = $menuItem;
		}
		
		$itemIndex = [ ];
		foreach ( $hierarchyItems as $hierarchyItem ) {
			// check if menu is available in current language, skip if not
			if (isset ( $menuItemIndex [$hierarchyItem ['id']] ) || !$removeHierarchyItemsWithNoContent) {
				if (isset ( $menuItemIndex [$hierarchyItem ['id']] [$language] )) {
					$hierarchyItem ['menu_item'] = $menuItemIndex [$hierarchyItem ['id']] [$language];
				} else if (! $hideMissingLanguages) {
					// get fallback language as menu item if primary requested language not available (and missing items should not be hidden)
					if(isset($menuItemIndex [$hierarchyItem ['id']]) && $menuItemIndex [$hierarchyItem ['id']] != null)
						$hierarchyItem ['menu_item'] = reset ( $menuItemIndex [$hierarchyItem ['id']] ); // return first language item found
					$hierarchyItem ['displaying_fallback_language'] = true;
				}
				if(isset($menuItemIndex [$hierarchyItem ['id']]) && $menuItemIndex [$hierarchyItem ['id']] != null){
					$hierarchyItem ['available_menu_items_all_languages'] = $menuItemIndex [$hierarchyItem ['id']];
				} else {
					$hierarchyItem ['available_menu_items_all_languages'] = [];
				}
				
				if (isset ( $hierarchyItem ['menu_item'] ) ||  !$removeHierarchyItemsWithNoContent)
					$itemIndex [$hierarchyItem ['id']] = $hierarchyItem;
			}
		}
	
		$rootItem = new SimpleHierarchyItem ( $itemIndex [0], ($expandLevel > 0), 0 );
		unset($itemIndex [0]);
		CmsHierarchyController::populateChildrenRecursive ( $rootItem, $itemIndex, $expandLevel, 1 );
		return $rootItem;
	}
	
	/**
	 * 
	 * private helper function go into recursion while building hierarchy tree
	 */
	private static function populateChildrenRecursive($parent, $allItems, $expandLevel, $levelDepth) {
		$foundChild = false;
		foreach ( $allItems as $itemId => $item ) {
			CmsHierarchyController::$iterationCounter++;
			if ($item ['parent_id'] == $parent->id) {
				$foundChild = true;
				$child = new SimpleHierarchyItem ( $item, ($expandLevel > $levelDepth), $levelDepth );
				//save iteration steps in child iterations by removing current index
				unset($allItems[$itemId]);
				
				CmsHierarchyController::populateChildrenRecursive ( $child, $allItems, $expandLevel, $levelDepth + 1 );
				$parent->addChild ( $child );
			} else if($foundChild){
				// since we found a child before and the result set is ordered by parent_id, 
				// if this item is not a child it means, we found all child nodes for this parent node
				// so we can break out of the foreach loop 
				break;
			}
		}
	}
	
	/**
	 * change parent id and position of an hierarchy item and reset following sibling positions accordingly
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 * 
	 * @param integer $hierarchyItemId  the item id of the hierarchy item position to set the new position value for
	 * @param integer $newParentHierarchyItemId the item id of the new parent hierarchy item to append the item below
	 * @param integer $newPosition the position value. Values must equal or larger than 1 (0 is not allowed)
	 * @return string json encoded result array
	 */
	public function actionSetItemParentAndPositionJson($hierarchyItemId = -1, $newParentHierarchyItemId = -1, $newPosition = 0) {
		$result = [ ];
		$result ['result'] = 'failed';
		$result ['success'] = false;
		
		$hierarchyItemId = intval ( $hierarchyItemId );
		$newParentHierarchyItemId = intval ( $newParentHierarchyItemId );
		
		$hierarchyItem = CmsHierarchyItem::findOne ( $hierarchyItemId );
		if ($hierarchyItemId == 0 || ! $hierarchyItem || $newParentHierarchyItemId < 0) {
			$result ['message'] = 'invalid or no hierarchy or parent hierarchy item id given';
		} else if ($hierarchyItem->id == 0) {
			$result ['message'] = 'the root hierarchy items position value cannot be modified';
		} else {
			// move item if needed at all
			if ($hierarchyItem->parent_id != $newParentHierarchyItemId) {
				$parentHierarchyItem = CmsHierarchyItem::findOne ( $newParentHierarchyItemId );
				if ($parentHierarchyItem) {
					$hierarchyItem->parent_id = $newParentHierarchyItemId;
					if (! $hierarchyItem->save ()) {
						$result ['message'] = 'failed updating the parent item id';
					} else {
						// now fix position
						return $this->actionSetItemPositionWithinSiblingsJson ( $hierarchyItemId, $newPosition, true );
					}
				} else {
					$result ['message'] = 'new parent hierarchy item not found.';
				}
			} else {
				// now fix position
				return $this->actionSetItemPositionWithinSiblingsJson ( $hierarchyItemId, $newPosition );
			}
		}
		// only return here in error case
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( $result, JSON_PRETTY_PRINT );
	}
	
	/**
	 * Fix the position values of all child items for the given parent id.
	 * This should be called once a new item is inserted amongst other siblings or just to perform an integrity check and avoid duplicate position values amongst siblings.
	 * This function is quite expensive due to the sql calls (espcially in recusrion mode) so it must not be used in often called functions.
	 * @param unknown $parentItemId the item id to get the children for an set the position values
	 * @param boolean $recurseTree recurse into the subtree structure
	 * @return string
	 */
	public function actionFixPositionsForChildren($parentItemId,$recurseTree=false){
		$result = [];
		$updateCounter = 0;
		$failedUpdateCounter = 0;
		$checkedItems = 0;
		$this->fixItemPositionsForChildren($parentItemId,$updateCounter,$failedUpdateCounter,$checkedItems,$recurseTree);
		$result['updateCounter'] = $updateCounter;
		$result['failedUpdateCounter'] = $failedUpdateCounter;
		$result['checkedItems'] = $checkedItems;
		
		return $this->render ( 'profiling', [
			'data' => $result,
		] );
	}
	
	private function fixItemPositionsForChildren($parentItemId,&$updateCounter,&$failedUpdateCounter,&$checkedItems,$recurseTree=false){
		$childItems = CmsHierarchyItem::find()->where(['parent_id' => $parentItemId])->orderby('position ASC')->all();
		foreach($childItems as $arrayIndex => $item){
			$checkedItems++;
			$positionValue = $arrayIndex+1;
			if($item->position != $positionValue){
				$item->position = $positionValue;
				if($item->save()){
					$updateCounter++;
				} else {
					$failedUpdateCounter++;
				}
			}
			if($recurseTree){
				$this->fixItemPositionsForChildren($item->id,$updateCounter,$failedUpdateCounter,$checkedItems, true);
			}
		}
	}
	
	/**
	 * change position of an hierarchy item and reset following sibling positions accordingly
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 * 
	 * @param integer $hierarchyItemId the item id of the hierarchy item position to set the new position value for
	 * @param integer $newPosition  the position value. Values must equal or larger than 1 (0 is not allowed)
	 * @return string json encoded, associcative result array
	 */
	public function actionSetItemPositionWithinSiblingsJson($hierarchyItemId = -1, $newPosition = 0, $overwritePosition = false) {
		$result = [ ];
		$result ['result'] = 'failed';
		$result ['success'] = false;
		
		$hierarchyItemId = intval ( $hierarchyItemId );
		$newPosition = intval ( $newPosition );
		$hierarchyItem = CmsHierarchyItem::findOne ( $hierarchyItemId );
		if ($hierarchyItemId == 0 || ! $hierarchyItem) {
			$result ['message'] = 'invalid or no hierarchy item id given';
		} else if ($hierarchyItem->id == 0) {
			$result ['message'] = 'the root hierarchy items position value cannot be modified';
		} else if ($newPosition <= 0) {
			$result ['message'] = 'invalid position value given, must equal to or larger than 1';
		} else if ($hierarchyItem->position == $newPosition && ! $overwritePosition) {
			$result ['message'] = 'new item position is equal to old position, no changes needed';
		} else {
			// parameters are fine so far, get all siblings of item to change position for
			$hierarchyItemSiblings = CmsHierarchyItem::find ()->where ( [ 
				'parent_id' => $hierarchyItem->parent_id 
			] )->orderby ( 'position ASC' )->all ();
			
			$newItemOrder = [ ];
			// fill array with all items, except the one to move
			foreach ( $hierarchyItemSiblings as $item ) {
				if ($item->id != intval ( $hierarchyItem->id )) {
					$newItemOrder [] = $item;
				}
			}
			// now splice in the new one at the desired position
			array_splice ( $newItemOrder, $newPosition - 1, 0, [ 
				$hierarchyItem 
			] );
			
			$returnArray = [ ];
			$allOk = true;
			// update items and fill return array
			for($i = 0; $i < count ( $newItemOrder ); $i ++) {
				$item = $newItemOrder [$i];
				$updated = false;
				// check if db item needs update
				if ($item->position != $i + 1) {
					$item->position = $i + 1;
					$updated = $item->save ();
					if (! $updated)
						$allOk = false;
				}
				$returnItem = new BasicHierarchyItem ( $item );
				$returnItem->updated = $updated;
				$returnArray [] = $returnItem;
			}
			$result ['result'] = 'success';
			$result ['success'] = $allOk;
			$result ['message'] = 'new item positions set';
			$result ['items'] = $returnArray;
		}
		
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( $result, JSON_PRETTY_PRINT );
	}
	
	/**
	 * Create a new hierarchy item and return the result as json encoded object
	 * 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 * 
	 * @param integer $parentHierarchyItemId the parent item id to create the new element below
	 * @param string $newMenuName the language sepcific display name of the menu item
	 * @param integer $position the position (for specifiying the display order amongst all siblings) within the other siblings of the newly created item
	 * @param integer $language the language id of the menu to create
	 * @param integer $contentType the content type of the newly created item. Use the constants of CmsHierarchyItem class. 
	 * @see CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION
	 * @see CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_HIDDEN_IN_NAVIGATION
	 * @see CmsHierarchyItem::DISPLAYSTATE_UNPUBLISHED
	 * @return string
	 */
	public function actionCreateHierarchyItemJson($parentHierarchyItemId = -1, $newMenuName = null, $position = -1, $language = -1, $contentType = -1, $jsonEncode = true) {
		$parentHierarchyItemId = intval ( $parentHierarchyItemId );
		$position = intval ( $position );
		$language = intval ( $language );
		$contentType = intval ( $contentType ); //FIXME: variable is never used at the moment, so it could just as well be omitted
		
		$result = [ ];
		$result ['message'] = '';
		$result ['success'] = false;
		if ($parentHierarchyItemId < 0 || $newMenuName == '' || $position <= 0 || $language < 1 || $contentType < 1) {
			$result ['result'] = 'failed';
			$result ['success'] = false;
			$result ['message'] .= 'error, missing required parameters or invalid values given';
		} else {
			$newHierarchyItem = new CmsHierarchyItem ();
			$newHierarchyItem->parent_id = $parentHierarchyItemId;
			$newHierarchyItem->display_state = CmsHierarchyItem::DISPLAYSTATE_UNPUBLISHED;
			$newHierarchyItem->position = $position;
			if ($newHierarchyItem->validate () && $newHierarchyItem->save ()) {
				$result ['message'] .= 'hierarchy item created, id is ' . $newHierarchyItem->id . " | ";
				$newMenuItem = new CmsMenuItem ();
				$newMenuItem->created_datetime = new Expression ( 'NOW()' );
				$newMenuItem->createdby_userid = Yii::$app->user->id;
				$newMenuItem->cms_hierarchy_item_id = $newHierarchyItem->id;
				$newMenuItem->language = $language;
				$newMenuItem->name = $newMenuName;
				if ($newMenuItem->validate () && $newMenuItem->save ()) {
					$result ['result'] = 'success';
					$result ['success'] = true;
					$result ['message'] .= 'new hierarchy item created';
					$hierarchyItemDetailsArray = $newHierarchyItem->getAttributes ();
					$hierarchyItemDetailsArray ['menu_item'] = $newMenuItem->getAttributes ();
					$hierarchyItemDetailsArray ['children'] = [ ];
					$hierarchyItemDetailsArray ['available_menu_items_all_languages'] = [ 
						'language' => $newMenuItem 
					];
					$result ['item'] = new SimpleHierarchyItem ( $hierarchyItemDetailsArray, true, 1 );
				} else {
					$result ['result'] = 'failed';
					$result ['success'] = false;
					$result ['message'] .= 'error, hierarchy item created, but creation of language version for menu failed: ' . implode ( $newMenuItem->getFirstErrors () );
				}
				$updateCounter = 0;
				$failedUpdateCounter = 0;
				$checkedItems = 0;
				$this->fixItemPositionsForChildren($parentHierarchyItemId,$updateCounter,$failedUpdateCounter,$checkedItems,false);
			} else {
				$result ['result'] = 'failed';
				$result ['success'] = false;
				$result ['message'] .= 'error while trying to validate and save the hierarchy item: ' . implode ( $newHierarchyItem->getFirstErrors () );
			}
		}
		if($jsonEncode){
			Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
			$headers = Yii::$app->response->headers;
			$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
			Yii::$app->response->charset = 'UTF-8';
			return json_encode ( $result, JSON_PRETTY_PRINT );
		} else 
			return $result;
	}
	
	/**
	 * generate a menu (page) tree of all menus (for admin reasons only, since all items will be displayed including fallback language and all display types, even hidden ones).
	 * The result is returned as a json encoded hierarchy
	 * 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendRead
	 * 
	 * @param integer language the language id for which to display the page tree (as primary language)
	 * @param integer expandLevel the level depth, until which the folders should be marked as expanded ($expanded = true)
	 */
	public function actionPageTreeJson($language, $hideMissingLanguages = false, $expandLevel = 9999) {
		$rootItem = CmsHierarchyController::getMenuTree ( $language, $expandLevel, $hideMissingLanguages, [ ] );
		$rootItem->finalizeForOutput ();
		
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( [ 
			$rootItem 
		], JSON_PRETTY_PRINT );
	}
	/**
	 * Set the display state of the hierarchy item with the given id to the given displayState value.
	 * The result is returned as a json encoded object.
	 * 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 * 
	 * @param $hierarchyItemId integer
	 * @param $displayState integer
	 * @return string
	 */
	public function actionSetDisplayStateJson($hierarchyItemId = -1, $displayState = -1) {
		$result = [ ];
		
		if ($hierarchyItemId == - 1 || $displayState == - 1) {
			$result ['result'] = 'failed';
			$result ['message'] = 'missing required parameters for action';
		} else {
			$displayState = intval ( $displayState );
			if ($displayState <= CmsHierarchyItem::DISPLAYSTATE_MAX_VALUE && $displayState >= CmsHierarchyItem::DISPLAYSTATE_MIN_VALUE) {
				$cmsHierarchyItem = CmsHierarchyItem::findOne ( $hierarchyItemId );
				if ($cmsHierarchyItem) {
					$cmsHierarchyItem->display_state = $displayState;
					if ($cmsHierarchyItem->save ()) {
						$result ['result'] = 'success';
						$result ['newDisplayState'] = $displayState;
						$result ['hierarchyItemId'] = $cmsHierarchyItem->id;
					} else {
						$result ['result'] = 'failed';
						$result ['message'] = 'error while trying to update hierarchy node';
					}
				} else {
					$result ['result'] = 'failed';
					$result ['message'] = 'unknown hierarchy node id given. Update failed';
				}
			} else {
				$result ['result'] = 'failed';
				$result ['message'] = 'invalid value for display_state given. Not within valid range';
			}
		}
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		
		return json_encode ( $result, JSON_PRETTY_PRINT );
	}
	
	/**
	 * Lists all CmsHierarchyItem models.
	 * 
	 * @menuLabel List all hierarchy items
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendRead
	 * 
	 * @return mixed rendered view
	 */
	public function actionIndex() {
		$searchModel = new CmsHierarchyItemSearch ();
		$dataProvider = $searchModel->search ( Yii::$app->request->queryParams );
		
		return $this->render ( 'index', [ 
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider 
		] );
	}
	
	/**
	 * Displays a single CmsHierarchyItem model.
	 * 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendRead
	 * 
	 * @param integer id the id of the hierarchy item to display  	
	 * @return mixed
	 */
	public function actionView($id) {
		return $this->render ( 'view', [ 
			'model' => $this->findModel ( $id ) 
		] );
	}
	
	/**
	 * Creates a new CmsHierarchyItem model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @menuLabel Create new hierarchy item
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 * 
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new CmsHierarchyItem ();
		
		if ($model->load ( Yii::$app->request->post () ) && $model->save ()) {
			return $this->redirect ( [ 
				'view',
				'id' => $model->id 
			] );
		} else {
			return $this->render ( 'create', [ 
				'model' => $model 
			] );
		}
	}
	
	/**
	 * Updates an existing CmsHierarchyItem model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 * 
	 * @param integer $id the id of the hierarchy item to update   	
	 * @return \yii\web\Response|string
	 */
	public function actionUpdate($id) {
		$model = $this->findModel ( $id );
		
		if ($model->load ( Yii::$app->request->post () ) && $model->save ()) {
			return $this->redirect ( [ 
				'view',
				'id' => $model->id 
			] );
		} else {
			return $this->render ( 'update', [ 
				'model' => $model 
			] );
		}
	}
	
	/**
	 * Deletes the CmsHierarchyItem, specified by the id parameter.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * 
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 *
	 * @param integer $id the id of the hierarchy item to delete 	
	 * @return \yii\web\Response|string
	 */
	public function actionDelete($id) {
		$this->findModel ( $id )->delete ();
		
		return $this->redirect ( [ 
			'index' 
		] );
	}
	
	/**
	 * Finds the CmsHierarchyItem model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id        	
	 * @return CmsHierarchyItem the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = CmsHierarchyItem::findOne ( $id )) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException ( 'The requested page does not exist.' );
		}
	}
}
/**
 * internal helper class for slim representation of an hierarhcy item
 * @author Paul Kerspe
 *
 */
class BasicHierarchyItem {
	public $parent_id;
	public $id;
	public $position;
	public $updated;
	function __construct($cmsHierarchyItem) {
		$this->id = $cmsHierarchyItem->id;
		$this->position = $cmsHierarchyItem->position;
		$this->parent_id = $cmsHierarchyItem->parent_id;
	}
}

/**
 * internal helper class for less memory consuming representation of an hierarhcy item with capability of representing a tree structure (parent/child relations)
 * @author Paul Kerspe
 *
 */
class SimpleHierarchyItem {
	public $children = [ ];
	public $parent_id;
	public $id;
	public $menu_id;
	public $content_id;
	public $document_id;
	public $direct_url;
	public $languageId;
	public $languageCode;
	public $availableLanguageCodes = [ ];
	public $key;
	public $title;
	public $expanded;
	public $position;
	public $displayState;
	public $isFallbackLanguage = false; // if current language is not available and fallback language is returned instead
	public $levelDepth;
	public $allLanguagesWithMarker = [ ];
	public $firstSibling = false;
	public $lastSibling = false;
	function __construct($cmsHierarchyItemDetailsArray, $displayExpanded, $levelDepth) {
		$this->levelDepth = $levelDepth;
		$this->id = $cmsHierarchyItemDetailsArray ['id'];
		$this->key = $cmsHierarchyItemDetailsArray ['id'];
		$this->parent_id = $cmsHierarchyItemDetailsArray ['parent_id'];
		$this->position = $cmsHierarchyItemDetailsArray ['position'];
		if(isset($cmsHierarchyItemDetailsArray ['menu_item']) && $cmsHierarchyItemDetailsArray ['menu_item'] != null){
			$this->title = $cmsHierarchyItemDetailsArray ['menu_item'] ['name'];
			$this->menu_id = $cmsHierarchyItemDetailsArray ['menu_item'] ['id'];
			$this->content_id = $cmsHierarchyItemDetailsArray ['menu_item'] ['page_content_id'];
			$this->document_id = $cmsHierarchyItemDetailsArray ['menu_item'] ['document_id'];
			$this->direct_url = $cmsHierarchyItemDetailsArray ['menu_item'] ['direct_url'];
			$this->languageId = $cmsHierarchyItemDetailsArray ['menu_item'] ['language'];
			$this->languageCode = Yii::$app->controller->module->getLanguageManager()->getMappingForIdResolveAlias ( $cmsHierarchyItemDetailsArray ['menu_item'] ['language'] )['code'];
		}
		$this->expanded = $displayExpanded;
		$this->displayState = $cmsHierarchyItemDetailsArray ['display_state'];
		$this->isFallbackLanguage = (isset ( $cmsHierarchyItemDetailsArray ['displaying_fallback_language'] ) && $cmsHierarchyItemDetailsArray ['displaying_fallback_language']);
		
		foreach ( $cmsHierarchyItemDetailsArray ['available_menu_items_all_languages'] as $menuItem ) {
			$this->addAvailableLanguageCodes ( Yii::$app->controller->module->getLanguageManager()->getMappingForIdResolveAlias ( $menuItem ['language'] )['code'], $menuItem ['id'] );
		}
		
		// create an array with all languages, where the available languages are marked explicitly (this is used to display the existing and non existing language versions in the frontend
		foreach ( Yii::$app->controller->module->getLanguageManager()->getAllConfiguredLanguageCodes () as $languageId => $languageCode ) {
			$this->allLanguagesWithMarker [] = [ 
				'code' => $languageCode,
				'language_id' => $languageId,
				'available' => array_key_exists ( $languageCode, $this->availableLanguageCodes ),
				'menu_item_id' => (isset ( $this->availableLanguageCodes [$languageCode] )) ? $this->availableLanguageCodes [$languageCode] : '' 
			];
		}
	}
	public function addAvailableLanguageCodes($languageCode, $menuItemId) {
		$this->availableLanguageCodes [$languageCode] = $menuItemId;
	}
	public function addChild($simpleHierarchyItem) {
		if ($simpleHierarchyItem instanceof SimpleHierarchyItem) {
			$this->children [] = $simpleHierarchyItem;
		} else {
			throw new Exception ( 'Wrong object type given as child element.' );
		}
	}
	/**
	 * set final valies and order children by position for beforeoutput as json encoded string to client
	 */
	public function finalizeForOutput() {
		if (count ( $this->children ) > 0) {
			// sort children by position
			usort ( $this->children, array (get_class($this->children [0]) ,"compare") );
			foreach ( $this->children as $child ) {
				$child->finalizeForOutput ();
			}
			$this->children [0]->firstSibling = true;
			end ($this->children)->lastSibling = true;
		}
	}
	public function getAllChildren() {
		return $this->children;
	}
	
	/*
	 * static comparing function for sorting items depending on their position
	 */
	static function compare($a, $b) {
		$al = $a->position;
		$bl = $b->position;
		if ($al == $bl) {
			return 0;
		}
		return ($al > $bl) ? + 1 : - 1;
	}
}
