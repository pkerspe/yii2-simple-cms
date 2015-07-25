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
use yii\base\View;
use schallschlucker\simplecms\controllers\backend\DefaultController;

/**
 * The default controller of the CMS frontend to provide actions for displaying content/documents
 * Default action is actionIndex, which renders an all in one administration mask for creating and maintaining the page tree.
 *
 * @menuLabel CMS Frontend Controller
 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
 */
class ShowController extends Controller {
	
	public $defaultAction = 'homepage';
	
	/**
	 * @menuLabel display root content page
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 */
	public function actionHomepage() {
		$currentLanguageId = Yii::$app->controller->module->getLanguageManager()->getLanguageIdForString(Yii::$app->language);
		$pageContent = null;
		$isFallback = false;
		
		$menuItemForRoot = CmsMenuItem::findOne(['cms_hierarchy_item_id' => DefaultController::$ROOT_HIERARCHY_ITEM_ID , 'language' => $currentLanguageId]);
		if($menuItemForRoot == null){
			$menuItemForRoot = CmsMenuItem::findOne(['cms_hierarchy_item_id' => DefaultController::$ROOT_HIERARCHY_ITEM_ID]);
			$isFallback = true;
		}
		
		if($menuItemForRoot != null)
			$pageContent = CmsPageContent::findOne(['id' => $menuItemForRoot->page_content_id]);
		
		if($pageContent == null)
			throw new NotFoundHttpException('No content could be found for the given id',404);
		
		return $this->render ( 'page', [ 
			'pageContentModel' => $pageContent,
			'isfallbacklanguage' => $isFallback,
            'renderTopMenuNavbar' => Yii::$app->controller->module->renderTopMenuNavbar,
		] );
	}
	
	/**
	 * show the page for a given cms menu item id
	 * @menuLabel __HIDDEN__
	 * @param integer $menuItemId
	 * @throws NotFoundHttpException an exception when the menu with the given id does not exists or does not have a linked page content 
	 * @return View
	 */
	public function actionPage($menuItemId) {
		$menuItemId = intval($menuItemId);
		$menuItem = CmsMenuItem::find()->where(['id' => $menuItemId])->with('pageContent')->with('cmsHierarchyItem')->one();
		return $this->renderPage($menuItem);
	}
	
	/**
	 * show the page for the given alias name
	 * @menuLabel __HIDDEN__
	 * @param String $menuItemAlias
	 * @return View
	 */
	public function actionAlias($menuItemAlias){
		$menuItemAlias = preg_replace('/[^a-zA-Z0-9_\-]/', '', $menuItemAlias);
		
		$menuItem = CmsMenuItem::find()->where(['alias' => $menuItemAlias, ])->with('pageContent')->with('cmsHierarchyItem')->one();
		return $this->renderPage($menuItem);
	}

	/**
	 * 
	 * @param CmsMenuItem $menuItem
	 * @menuLabel __HIDDEN__
	 * @throws NotFoundHttpException
	 * @return View
	 */
	private function renderPage($menuItem){
		$isFallback = false;
		if($menuItem == null){
			throw new NotFoundHttpException('No menu could be found for the given menu id',404);
		}
		if($menuItem->cmsHierarchyItem->display_state === CmsHierarchyItem::DISPLAYSTATE_UNPUBLISHED){
			throw new NotFoundHttpException('You dont have the rights to access this item',403);
		}
		
		$pageContent = $menuItem->pageContent;
		if($pageContent == null){
			throw new NotFoundHttpException('No page content could be found for the menu id',404);
		}
		//set meta tags if needed
		if($pageContent->meta_keywords != null && trim($pageContent->meta_keywords) != '')
			$this->view->registerMetaTag(['name' => 'keywords','content' => $pageContent->meta_keywords]);
		if($pageContent->description != null && trim($pageContent->description) != '')
			$this->view->registerMetaTag(['name' => 'description','content' => $pageContent->description]);
		
		//set title tag either by htmlTitle Attribute if any or using the name of the menu item
		$pageTitle = Yii::$app->controller->module->htmlTitlePrefix;
		$pageTitle .= (isset($pageContent->html_title) && $pageContent->html_title != "") ? $pageContent->html_title : $menuItem->name;
	    $pageTitle .= Yii::$app->controller->module->htmlTitleSuffix;
		$this->view->title = $pageTitle;
		
		return $this->render ( 'page', [
			'pageContentModel' => $pageContent,
			'isfallbacklanguage' => $isFallback,
            'renderTopMenuNavbar' => Yii::$app->controller->module->renderTopMenuNavbar,
		] );
	}
	
	/**
	 * show the page for a given page content id 
	 * @menuLabel __HIDDEN__
	 * @param integer $pageContentId the cmsPageContent item id to be displayed
	 * @throws NotFoundHttpException an exception when the page content with the given id does not exists or does not have a linked page content
	 * @return View
	 */
	public function actionContent($pageContentId){
		$isFallback = false;
		$pageContentId = intval($pageContentId);
		$pageContent = CmsPageContent::findOne(['id' => $pageContentId]);
		if($pageContent == null){
			throw new NotFoundHttpException('No page content could be found for the menu id',404);
		}
		
		return $this->render ( 'page', [
			'pageContentModel' => $pageContent,
			'isfallbacklanguage' => $isFallback,
		] );
	}
	
	/**
	 * send data of document to browser directly 
	 * 
	 * (either by sending redirect url to let apache handle the request 
	 * [if the file is located in the web directory or a folder below the web directory], 
	 * or by sending file via php [if the file is not located within the web directory folder structure])
	 * @menuLabel __HIDDEN__
	 * @param unknown $documentId
	 * @throws NotFoundHttpException
	 */
	public function actionFileData($documentId){
		$isWebaccessableFile = false;
		$documentModel = CmsDocument::findOne($documentId);
		if($documentModel == null){
			throw new NotFoundHttpException('Document could not be found',404);
		}
		
		$filePath = FileHelper::normalizePath($documentModel->file_path.DIRECTORY_SEPARATOR.$documentModel->file_name);
		$webRootFolder = Yii::getAlias('@webroot');
		$isWebaccessableFile = (stripos($filePath, $webRootFolder) === 0);

		//check if item is in web folder, then redirect, or send file instead if not
		if($isWebaccessableFile){
			$webPath = substr($filePath, strlen($webRootFolder));
			Yii::$app->response->redirect($webPath);
		} else {
			Yii::$app->response->sendFile($filePath,$documentModel->file_name,['mimeType' => $documentModel->mime_type, 'inline' => true])->send();
		}
	}
	
	/**
	 * display the document in the chosen presentation style (Inline, Window or send as download response)
	 * @menuLabel __HIDDEN__
	 * @param unknown $documentId
	 * @throws NotFoundHttpException
	 * @return void|string
	 */
	public function actionDocument($documentId){
		$documentId = intval($documentId);
		$documentModel = CmsDocument::findOne($documentId);
		if($documentModel == null){
			throw new NotFoundHttpException('Document could not be found',404);
		}
		$filePath = $documentModel->file_path.DIRECTORY_SEPARATOR.$documentModel->file_name;
		if(file_exists($filePath)){
			
			switch($documentModel->presentation_style){
				case CmsDocument::PRESENTATION_STYLE_DOWNLOAD:
					Yii::$app->response->sendFile($filePath,$documentModel->file_name,['mimeType' => $documentModel->mime_type, 'inline' => false])->send();
					return;
	
				case CmsDocument::PRESENTATION_STYLE_WINDOW:
					Yii::$app->response->sendFile($filePath,$documentModel->file_name,['mimeType' => $documentModel->mime_type, 'inline' => true])->send();
					return;
					
				case CmsDocument::PRESENTATION_STYLE_EMBEDED:
					return $this->render ( 'document', [
						'documentModel' => $documentModel
					] );
					break;
					
				default:
					break;
			}
		} else {
			throw new NotFoundHttpException('Document could not be found, invalid path',404);
		}
	}
}
