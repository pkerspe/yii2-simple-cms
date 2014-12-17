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
use yii\db\Expression;

/**
 * @menuLabel CMS Frontend
 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
 */
class DefaultController extends Controller {
	/**
	 * @menuLabel display root page of cms
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 */
	public function actionIndex() {
		$model = new CmsAdministrationMainTreeViewForm ();
		$model_wrapperform = new MenuItemAndContentForm ();
		if (! $model->load ( Yii::$app->request->post () )) {
			$model->treeDisplayLanguageId = $this->module->getLanguageManager()->getDefaultLanguageId ();
		}
		
		return $this->render ( 'index', [ 
			'model' => $model,
			'model_wrapperform' => $model_wrapperform 
		] );
	}
	public function actionEditMenuLanguageVersion($menuItemId, $useget = false) {
		$menuItemId = intval ( $menuItemId );
		$model_menu = CmsMenuItem::find ()->where ( [ 
			'id' => $menuItemId 
		] )->with ( 'cmsHierarchyItem' )->one ();
		
		$hierarchyItem = $model_menu->cmsHierarchyItem;
		$languageCode = $this->module->getLanguageManager()->getMappingForIdResolveAlias ( $model_menu->language );
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
							$message .= '<span class="success">' . Yii::t ( 'app/cms', 'Content linked to Menu language version' ) . '</span>';
						} else {
							$message .= '<span class="error">' . Yii::t ( 'app/cms', 'Error: content saved, yet it could not be linked to the menu language version!' ) . implode ( $model_content->getFirstErrors (), ' ' ) . '</span>';
						}
					}
					$message .= '<span class="success">' . Yii::t ( 'app/cms', 'Content saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'app/cms', 'Error: saving content failed!' ) . implode ( $model_content->getFirstErrors (), ' ' ) . '</span>';
				}
			}
			
			// update menu item
			// $menu_item_new_name = new CmsMenuItem();
			if ($model_menu->load ( Yii::$app->request->post () )) {
				if ($model_menu->save ()) {
					$message .= '<span class="success">' . Yii::t ( 'app/cms', 'Menu details saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'app/cms', 'Error: saving new menu name!' ) . '</span>';
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
					$message .= '<span class="success">' . Yii::t ( 'app/cms', 'Document saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'app/cms', 'Error: saving document failed!' ) . '</span>';
				}
			}
			// check if menu item name has been altered
			if ($model_menu->load ( Yii::$app->request->post () )) {
				if ($model_menu->save ()) {
					$message .= '<span class="success">' . Yii::t ( 'app/cms', 'Menu details saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'app/cms', 'Error: saving new menu name!' ) . '</span>';
				}
			}
			
			$model_wrapperform->contentType = MenuItemAndContentForm::CONTENT_TYPE_DOCUMENT;
		} else if ($model_menu->direct_url != null || $contentType == MenuItemAndContentForm::CONTENT_TYPE_URL) {
			/**
			 * **** DIRECT URL FORM ****************
			 */
			if ($model_menu->load ( Yii::$app->request->post () )) {
				if ($model_menu->save ()) {
					$message .= '<span class="success">' . Yii::t ( 'app/cms', 'Menu details saved successfully' ) . '</span>';
				} else {
					$message .= '<span class="error">' . Yii::t ( 'app/cms', 'Error: saving new menu name!' ) . '</span>';
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
	public function actionCreateMenuLanguageVersion($hierarchyItemId, $languageId) {
		$message = null;
		$hierarchyItemId = intval ( $hierarchyItemId );
		$hierarchyItem = CmsHierarchyItem::findOne ( $hierarchyItemId );
		$languageId = intval ( $languageId );
		$languageCode = $this->module->getLanguageManager()->getMappingForIdResolveAlias ( $languageId );
		
		$model_menu = null;
		$model_content = null;
		$model_document = null;
		
		$model_wrapperform = new MenuItemAndContentForm ();
		$model_wrapperform->scenario = 'createMenuLanguageVersion';
		if ($model_wrapperform->load ( Yii::$app->request->post () )) {
			$model_menu = new CmsMenuItem ();
			$model_menu->name = $model_wrapperform->newMenuName;
			$model_menu->language = $languageId;
			$model_menu->cms_hierarchy_item_id = $hierarchyItem->id;
			if ($model_menu->save ()) {
				$message .= 'Menu for language created successfully<br/>';
				$redirectUrl = Url::toRoute ( [ 
					'edit-menu-language-version',
					'menuItemId' => $model_menu->id,
					'useget' => true,
					'MenuItemAndContentForm' => $model_wrapperform 
				] );
				return $this->redirect ( $redirectUrl );
			}
		}
		
		return $this->render ( 'createMenuAndContent', [ 
			'model_wrapperform' => $model_wrapperform,
			'hierarchy_item' => $hierarchyItem,
			'languageCode' => $languageCode,
			'message' => $message 
		] );
	}
}
