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
	
	/**
	 * get the complete menu / hierarchy tree for the cms navigation structure
	 * @language integer the language id to get the cms menu items for
	 *
	 * @param
	 *        	expandLevel integer the level depth, until which the folders should be marked as expanded ($expanded = true)
	 *        	@hideMissingLanguages boolean hide or show hierarchy items that do not have a translation (cms menu item) in the requested language
	 *        	@filterDisplayState array an array if integers indicating which displayStates should be filtered from the results (@see CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION)
	 * @return array an SimpleHierarchyItem instance, containing all subnodes in a node-tree structure (children property holds children of each node)
	 */
	public static function getMenuTree($language, $expandLevel, $hideMissingLanguages, $filterDisplayState = []) {
		$language = intval ( $language );
		$expandLevel = intval ( $expandLevel );
		
		// query all hierarchy items (including hidden, etc.)
		$hierarchyQuery = CmsHierarchyItem::find ()->orderby ( 'id ASC' )->asarray ( true );
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
			if (isset ( $menuItemIndex [$hierarchyItem ['id']] )) {
				if (isset ( $menuItemIndex [$hierarchyItem ['id']] [$language] )) {
					$hierarchyItem ['menu_item'] = $menuItemIndex [$hierarchyItem ['id']] [$language];
				} else if (! $hideMissingLanguages) {
					// get fallback language as menu item if primary requested language not available (and missing items should not be hidden)
					$hierarchyItem ['menu_item'] = reset ( $menuItemIndex [$hierarchyItem ['id']] ); // return first language item found
					$hierarchyItem ['displaying_fallback_language'] = true;
				}
				$hierarchyItem ['available_menu_items_all_languages'] = $menuItemIndex [$hierarchyItem ['id']];
				
				if (isset ( $hierarchyItem ['menu_item'] ))
					$itemIndex [$hierarchyItem ['id']] = $hierarchyItem;
			}
		}
		
		$rootItem = new SimpleHierarchyItem ( $itemIndex [0], ($expandLevel > 0), 0 );
		CmsHierarchyController::populateChildrenRecursive ( $rootItem, $itemIndex, $expandLevel, 1 );
		return $rootItem;
	}
	
	/**
	 * private helper function go into recursion while building hierarchy tree
	 */
	private static function populateChildrenRecursive($parent, $allItems, $expandLevel, $levelDepth) {
		foreach ( $allItems as $itemId => $item ) {
			if ($item ['parent_id'] == $parent->id) {
				$child = new SimpleHierarchyItem ( $item, ($expandLevel > $levelDepth), $levelDepth );
				CmsHierarchyController::populateChildrenRecursive ( $child, $allItems, $expandLevel, $levelDepth + 1 );
				$parent->addChild ( $child );
				// sort children by position
				usort ( $parent->children, array (
					"common\modules\pn_cms\controllers\SimpleHierarchyItem",
					"compare" 
				) );
			}
		}
	}
	
	/**
	 * change parent id and position of an hierarchy item and reset following sibling positions accordingly
	 *
	 * @param
	 *        	hierarchyItemId integer the item id of the hierarchy item position to set the new position value for
	 * @param
	 *        	newParentHierarchyItemId integer the item id of the new parent hierarchy item to append the item below
	 * @param
	 *        	newPosition integer the position value. Values must equal or larger than 1 (0 is not allowed)
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
	 * change position of an hierarchy item and reset following sibling positions accordingly
	 *
	 * @param
	 *        	hierarchyItemId integer the item id of the hierarchy item position to set the new position value for
	 * @param
	 *        	newPosition integer the position value. Values must equal or larger than 1 (0 is not allowed)
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
	public function actionCreateHierarchyItemJson($parentHierarchyItemId = -1, $newMenuName = null, $position = -1, $language = -1, $contentType = -1) {
		$parentHierarchyItemId = intval ( $parentHierarchyItemId );
		$position = intval ( $position );
		$language = intval ( $language );
		$contentType = intval ( $contentType );
		
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
			} else {
				$result ['result'] = 'failed';
				$result ['success'] = false;
				$result ['message'] .= 'error while trying to validate and save the hierarchy item: ' . implode ( $newHierarchyItem->getFirstErrors () );
			}
		}
		
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( $result, JSON_PRETTY_PRINT );
	}
	
	/**
	 * generate a menu (page) tree of all menus (for admin reasons only, since all items will be displayed including fallback language and all display types, even hidden ones)
	 *
	 * @param
	 *        	language the language id for which to display the page tree (as primary language)
	 * @param
	 *        	expandLevel integer the level depth, until which the folders should be marked as expanded ($expanded = true)
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
	 * @return mixed @menuLabel list all cms hierarchy items
	 *         @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 *         @functionalRight cmsBackendRead
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
	 * @param integer $id        	
	 * @return mixed @menuLabel __HIDDEN__
	 *         @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 *         @functionalRight cmsBackendRead
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
	 * @return mixed @menuLabel __HIDDEN__
	 *         @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 *         @functionalRight cmsBackendWrite
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
	 * @param integer $id        	
	 * @return mixed @menuLabel __HIDDEN__
	 *         @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 *         @functionalRight cmsBackendWrite
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
	 * Deletes an existing CmsHierarchyItem model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id        	
	 * @return mixed @menuLabel __HIDDEN__
	 *         @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 *         @functionalRight cmsBackendWrite
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
		$this->title = $cmsHierarchyItemDetailsArray ['menu_item'] ['name'];
		$this->menu_id = $cmsHierarchyItemDetailsArray ['menu_item'] ['id'];
		$this->content_id = $cmsHierarchyItemDetailsArray ['menu_item'] ['page_content_id'];
		$this->document_id = $cmsHierarchyItemDetailsArray ['menu_item'] ['document_id'];
		$this->direct_url = $cmsHierarchyItemDetailsArray ['menu_item'] ['direct_url'];
		$this->languageId = $cmsHierarchyItemDetailsArray ['menu_item'] ['language'];
		$this->languageCode = Yii::$app->controller->module->getMappingForIdResolveAlias ( $cmsHierarchyItemDetailsArray ['menu_item'] ['language'] )['code'];
		$this->expanded = $displayExpanded;
		$this->displayState = $cmsHierarchyItemDetailsArray ['display_state'];
		$this->isFallbackLanguage = (isset ( $cmsHierarchyItemDetailsArray ['displaying_fallback_language'] ) && $cmsHierarchyItemDetailsArray ['displaying_fallback_language']);
		
		foreach ( $cmsHierarchyItemDetailsArray ['available_menu_items_all_languages'] as $menuItem ) {
			$this->addAvailableLanguageCodes ( Yii::$app->controller->module->getMappingForIdResolveAlias ( $menuItem ['language'] )['code'], $menuItem ['id'] );
		}
		
		// create an array with all languages, where the available languages are marked explicitly (this is used to display the existing and non existing language versions in the frontend
		foreach ( Yii::$app->controller->module->getAllConfiguredLanguageCodes () as $languageId => $languageCode ) {
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
	public function finalizeForOutput() {
		$childCounter = count ( $this->children );
		if ($childCounter > 0) {
			$this->children [0]->firstSibling = true;
			if ($childCounter >= 1) {
				$this->children [$childCounter - 1]->lastSibling = true;
			}
		}
		foreach ( $this->children as $child ) {
			$child->finalizeForOutput ();
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
