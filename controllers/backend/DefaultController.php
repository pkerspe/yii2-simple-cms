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
use schallschlucker\simplecms\controllers\backend\SettingsAndMaintenanceController;
use yii\db\Expression;
use schallschlucker\simplecms\models\SimpleHierarchyItem;

/**
 * The default controller of the CMS Backend.
 * Default action is actionIndex, which renders an all in one administration mask for creating and maintaining the page tree.
 *
 * @menuLabel CMS Administration
 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
 */
class DefaultController extends Controller {
	
	public static $ROOT_HIERARCHY_ITEM_ID = 1;
	
	/**
	 * @menuLabel display the CMS page tree
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 */
	public function actionIndex() {
		$model = new CmsAdministrationMainTreeViewForm ();
		$model_wrapperform = new MenuItemAndContentForm ();
		if (! $model->load ( Yii::$app->request->post () )) {
			$model->treeDisplayLanguageId = $this->module->getLanguageManager ()->getDefaultLanguageId ();
		}
		
		return $this->render ( 'index', [ 
			'model' => $model,
			'model_wrapperform' => $model_wrapperform 
		] );
	}
	
	//counter for profiling iterations
	public static $iterationCounter = 0;

	/**
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 */
	public function actionEditMenuLanguageVersion($menuItemId, $useget = false) {
		$menuItemId = intval ( $menuItemId );
		$model_menu = CmsMenuItem::find ()->where ( [ 
			'id' => $menuItemId 
		] )->with ( 'cmsHierarchyItem' )->one ();
		
		$hierarchyItem = $model_menu->cmsHierarchyItem;
		$languageCode = $this->module->getLanguageManager ()->getMappingForIdResolveAlias ( $model_menu->language );
		$model_wrapperform = new MenuItemAndContentForm ();
		if ($useget) {
			$model_wrapperform->load ( Yii::$app->request->get () );
		} else {
			$model_wrapperform->load ( Yii::$app->request->post () );
		}
		
		$model_content = null;
		$model_document = null;
		$message = null;
		
		$page_content_id_set = ($model_menu->page_content_id != null);
		$contentType = ($page_content_id_set) ? MenuItemAndContentForm::CONTENT_TYPE_PAGE : intval ( $model_wrapperform->contentType );
		
		/**
		 * **** PAGE CONTENT FORM ****************
		 */
		if ($page_content_id_set || $contentType == intval ( MenuItemAndContentForm::CONTENT_TYPE_PAGE )) {
			$model_content = new CmsPageContent ();
			if ($page_content_id_set) {
				$model_content = CmsPageContent::findOne ( $model_menu->page_content_id );
			}
			$loadSuccess_content = $model_content->load ( Yii::$app->request->post () );
			
			// check if menu item needs to be saved
			$model_content->language = $model_menu->language;
			if ($loadSuccess_content) {
				if ($model_content->created_datetime == null) {
					$model_content->created_datetime = new Expression ( 'NOW()' );
					$model_content->createdby_userid = Yii::$app->user->id;
				}
				if ($model_content->validate () && $model_content->save ()) {
					// link new item to menu if needed
					if (! $page_content_id_set) {
						$model_menu->page_content_id = $model_content->id;
						if ($model_menu->save ()) {
							$message .= '<span class="success">' . Yii::t ( 'simplecms', 'Content linked to Menu language version' ) . '</span>';
						} else {
							$message .= '<span class="error">' . Yii::t ( 'simplecms', 'Error: content saved, yet it could not be linked to the menu language version!' ) . implode ( $model_content->getFirstErrors (), ' ' ) . '</span>';
						}
					}
					$message .= '<span class="success">' . Yii::t ( 'simplecms', 'Content saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'simplecms', 'Error: saving content failed!' ) . implode ( $model_content->getFirstErrors (), ' ' ) . '</span>';
				}
			}
			
			// update menu item
			// $menu_item_new_name = new CmsMenuItem();
			if ($model_menu->load ( Yii::$app->request->post () )) {
				if ($model_menu->save ()) {
					$message .= '<span class="success">' . Yii::t ( 'simplecms', 'Menu details saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'simplecms', 'Error: saving new menu name!' ) . '</span>';
				}
			}
			
			$model_wrapperform->contentType = MenuItemAndContentForm::CONTENT_TYPE_PAGE;
		} else if ($model_menu->document_id != null || $contentType == MenuItemAndContentForm::CONTENT_TYPE_DOCUMENT) {
			/**
			 * **** DOCUMENT FORM ****************
			 */
			if ($model_menu->document_id != null) {
				$model_document = CmsDocument::findOne ( $model_menu->document_id );
			} else {
				$model_document = new CmsDocument ();
				$model_document->created_datetime = new Expression ( 'NOW()' );
				$model_document->createdby_userid = Yii::$app->user->id;
			}
			
			$loadSuccess_document = $model_document->load ( Yii::$app->request->post () );
			// set fixed values
			$model_document->language = $model_menu->language;
			
			if ($loadSuccess_document) {
				if ($model_document->save ()) {
					// link new item to menu if needed
					if ($model_menu->document_id == null) {
						$model_menu->document_id = $model_document->id;
						$model_menu->save ();
					}
					$message .= '<span class="success">' . Yii::t ( 'simplecms', 'Document saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'simplecms', 'Error: saving document failed!' ) . '</span>';
				}
			}
			// check if menu item name has been altered
			if ($model_menu->load ( Yii::$app->request->post () )) {
				if ($model_menu->save ()) {
					$message .= '<span class="success">' . Yii::t ( 'simplecms', 'Menu details saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'simplecms', 'Error: saving new menu name!' ) . '</span>';
				}
			}
			
			$model_wrapperform->contentType = MenuItemAndContentForm::CONTENT_TYPE_DOCUMENT;
		} else if ($model_menu->direct_url != null || $contentType == MenuItemAndContentForm::CONTENT_TYPE_URL) {
			/**
			 * **** DIRECT URL FORM ****************
			 */
			if ($model_menu->load ( Yii::$app->request->post () )) {
				if ($model_menu->save ()) {
					$message .= '<span class="success">' . Yii::t ( 'simplecms', 'Menu details saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'simplecms', 'Error: saving new menu name!' ) . '</span>';
				}
			}
			$model_wrapperform->contentType = MenuItemAndContentForm::CONTENT_TYPE_URL;
		} else {
			// illegal state, no content found for item, menu might not have been created properly or not completed, display selector for new content type
			$model_wrapperform->contentType = MenuItemAndContentForm::CONTENT_TYPE_UNDEFINED;
		}
		
		// get post parameters
		// TODO
		
		// set default values
		
		return $this->render ( 'editMenuAndContent', [ 
			'model_wrapperform' => $model_wrapperform,
			'model_content' => $model_content,
			'hierarchy_item' => $hierarchyItem,
			'languageCode' => $languageCode,
			// optional to be set, could be null also, depending on content type
			'model_document' => $model_document,
			'model_menu' => $model_menu,
			'message' => $message 
		] );
	}
	/**
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 */
	public function actionCreateMenuLanguageVersion($hierarchyItemId, $languageId) {
		$message = null;
		$hierarchyItemId = intval ( $hierarchyItemId );
		$hierarchyItem = CmsHierarchyItem::findOne ( $hierarchyItemId );
		$languageId = intval ( $languageId );
		$languageCode = $this->module->getLanguageManager ()->getMappingForIdResolveAlias ( $languageId );
		
		$model_menu = null;
		
		$model_wrapperform = new MenuItemAndContentForm ();
		$model_wrapperform->scenario = 'createMenuLanguageVersion';
		if ($model_wrapperform->load ( Yii::$app->request->post () )) {
			$model_menu = new CmsMenuItem ();
			$model_menu->name = $model_wrapperform->newMenuName;
			$model_menu->language = $languageId;
			$model_menu->cms_hierarchy_item_id = $hierarchyItem->id;
			$model_menu->created_datetime = new Expression ( 'NOW()' );
			$model_menu->createdby_userid = Yii::$app->user->id;
			if ($model_menu->save ()) {
				$message .= 'Menu for language created successfully<br/>'; // FIXME: this is useless st the moment, since not beeing displayed. Make it display somewhere
				$redirectUrl = Url::toRoute ( [ 
					'edit-menu-language-version',
					'menuItemId' => $model_menu->id,
					'useget' => true,
					'MenuItemAndContentForm' => $model_wrapperform 
				] );
				return $this->redirect ( $redirectUrl );
			} else {
				$message .= '<span class="error">' . Yii::t ( 'simplecms', 'Error while trying to save new menu item: ' ) . implode ( $model_menu->getFirstErrors (), ' ' ) . '</span>';
			}
		}
		
		return $this->render ( 'createMenuAndContent', [ 
			'model_wrapperform' => $model_wrapperform,
			'hierarchy_item' => $hierarchyItem,
			'languageCode' => $languageCode,
			'message' => $message 
		] );
	}
	
	/**
	 * get the complete menu / hierarchy tree for the cms navigation structure
	 *
	 * @functionalRight cmsBackendRead
	 *
	 * @param
	 *        	language integer the language id to get the cms menu items for
	 * @param
	 *        	expandLevel integer the level depth, until which the folders should be marked as expanded ($expanded = true)
	 * @param
	 *        	hideMissingLanguages boolean hide or show hierarchy items that do not have a translation (cms menu item) in the requested language
	 * @param
	 *        	filterDisplayState array an array if integers indicating which displayStates should be filtered from the results (@see CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION)
	 * @return SimpleHierarchyItem an SimpleHierarchyItem instance, containing all subnodes in a node-tree structure (children property holds children of each node)
	 */
	public static function getMenuTree($language, $expandLevel, $hideMissingLanguages, $removeHierarchyItemsWithNoContent = false, $filterDisplayState = []) {
		$language = intval ( $language );
		$expandLevel = intval ( $expandLevel );
		
		// query all hierarchy items (including hidden, etc.)
		$hierarchyQuery = CmsHierarchyItem::find ()->orderby ( 'parent_id ASC, position DESC' )->asarray ( true ); // ****** IMPORTANT ******* do not modify the order by clause, since it is required by the recursive subroutine to work properly
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
			// check if menu is available in current language, skip get fallback language if not
			if (isset ( $menuItemIndex [$hierarchyItem ['id']] ) || ! $removeHierarchyItemsWithNoContent) {
				if (isset ( $menuItemIndex [$hierarchyItem ['id']] [$language] )) {
					$hierarchyItem ['menu_item'] = $menuItemIndex [$hierarchyItem ['id']] [$language];
				} else if (! $hideMissingLanguages) {
					// get fallback language as menu item if primary requested language not available (and missing items should not be hidden)
					if (isset ( $menuItemIndex [$hierarchyItem ['id']] ) && $menuItemIndex [$hierarchyItem ['id']] != null){
						$hierarchyItem ['menu_item'] = reset ( $menuItemIndex [$hierarchyItem ['id']] ); // return first language item found
					}
					$hierarchyItem ['displaying_fallback_language'] = true;
				}
				if (isset ( $menuItemIndex [$hierarchyItem ['id']] ) && $menuItemIndex [$hierarchyItem ['id']] != null) {
					$hierarchyItem ['available_menu_items_all_languages'] = $menuItemIndex [$hierarchyItem ['id']];
				} else {
					$hierarchyItem ['available_menu_items_all_languages'] = [ ];
				}
				
				if (isset ( $hierarchyItem ['menu_item'] ) || ! $removeHierarchyItemsWithNoContent){
					if($hideMissingLanguages && !isset($hierarchyItem['menu_item']) ){
						
					} else 
						$itemIndex [$hierarchyItem ['id']] = $hierarchyItem;
				}
			}
		}
		
		if(isset($itemIndex [DefaultController::$ROOT_HIERARCHY_ITEM_ID])){
			$rootItem = new SimpleHierarchyItem ( $itemIndex [DefaultController::$ROOT_HIERARCHY_ITEM_ID], ($expandLevel > 0), 0 );
			unset ( $itemIndex [DefaultController::$ROOT_HIERARCHY_ITEM_ID] );
			DefaultController::populateChildrenRecursive ( $rootItem, $itemIndex, $expandLevel, 1 );
			return $rootItem;
		} else {
			throw new \Exception('Missing root hierarchy item (with id = '.DefaultController::$ROOT_HIERARCHY_ITEM_ID.'). Can not build page tree.');
		}
		return null;
	}
	
	/**
	 * private helper function go into recursion while building hierarchy tree
	 */
	private static function populateChildrenRecursive($parent, $allItems, $expandLevel, $levelDepth) {
		$foundChild = false;
		foreach ( $allItems as $itemId => $item ) {
			DefaultController::$iterationCounter ++;
			if ($item ['parent_id'] == $parent->id) {
				$foundChild = true;
				$child = new SimpleHierarchyItem ( $item, ($expandLevel > $levelDepth), $levelDepth );
				// save iteration steps in child iterations by removing current index
				unset ( $allItems [$itemId] );
				
				DefaultController::populateChildrenRecursive ( $child, $allItems, $expandLevel, $levelDepth + 1 );
				$parent->addChild ( $child );
			} else if ($foundChild) {
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
	 * @param integer $hierarchyItemId
	 *        	the item id of the hierarchy item position to set the new position value for
	 * @param integer $newParentHierarchyItemId
	 *        	the item id of the new parent hierarchy item to append the item below
	 * @param integer $newPosition
	 *        	the position value. Values must equal or larger than 1 (0 is not allowed)
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
	 * change position of an hierarchy item and reset following sibling positions accordingly
	 * @menuLabel __HIDDEN__
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 * @functionalRight cmsBackendWrite
	 *
	 * @param integer $hierarchyItemId
	 *        	the item id of the hierarchy item position to set the new position value for
	 * @param integer $newPosition
	 *        	the position value. Values must equal or larger than 1 (0 is not allowed)
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
	 * @param integer $parentHierarchyItemId
	 *        	the parent item id to create the new element below
	 * @param string $newMenuName
	 *        	the language sepcific display name of the menu item
	 * @param integer $position
	 *        	the position (for specifiying the display order amongst all siblings) within the other siblings of the newly created item
	 * @param integer $language
	 *        	the language id of the menu to create
	 * @see CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION
	 * @see CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_HIDDEN_IN_NAVIGATION
	 * @see CmsHierarchyItem::DISPLAYSTATE_UNPUBLISHED
	 * @return string
	 */
	public function actionCreateHierarchyItemJson($parentHierarchyItemId = -1, $newMenuName = null, $position = -1, $language = -1, $jsonEncode = true) {
		$parentHierarchyItemId = intval ( $parentHierarchyItemId );
		$position = intval ( $position );
		$language = intval ( $language );
		
		$result = [ ];
		$result ['message'] = '';
		$result ['success'] = false;
		if ($parentHierarchyItemId < 0 || $newMenuName == '' || $position <= 0 || $language < 1 ) {
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
				SettingsAndMaintenanceController::fixItemPositionsForChildren ( $parentHierarchyItemId, $updateCounter, $failedUpdateCounter, $checkedItems, false );
			} else {
				$result ['result'] = 'failed';
				$result ['success'] = false;
				$result ['message'] .= 'error while trying to validate and save the hierarchy item: ' . implode ( $newHierarchyItem->getFirstErrors () );
			}
		}
		if ($jsonEncode) {
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
	 * @param
	 *        	integer language the language id for which to display the page tree (as primary language)
	 * @param
	 *        	integer expandLevel the level depth, until which the folders should be marked as expanded ($expanded = true)
	 */
	public function actionPageTreeJson($language, $hideMissingLanguages = false, $expandLevel = 9999) {
		$rootItem = DefaultController::getMenuTree ( $language, $expandLevel, $hideMissingLanguages, [ ] );
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
					if( $cmsHierarchyItem->id == DefaultController::$ROOT_HIERARCHY_ITEM_ID ) {
						$result ['result'] = 'failed';
						$result ['message'] = 'the display state of the root node cannot be changed';
					} else {
					$cmsHierarchyItem->display_state = $displayState;
					if ($cmsHierarchyItem->save ()) {
						$result ['result'] = 'success';
						$result ['newDisplayState'] = $displayState;
						$result ['hierarchyItemId'] = $cmsHierarchyItem->id;
					} else {
						$result ['result'] = 'failed';
						$result ['message'] = 'error while trying to update hierarchy node';
					}
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
?>
