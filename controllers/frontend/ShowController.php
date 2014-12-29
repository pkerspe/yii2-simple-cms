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
		
		$menuItemForRoot = CmsMenuItem::findOne(['cms_hierarchy_item_id' => 0 , 'language' => $currentLanguageId]);
		if($menuItemForRoot == null){
			$menuItemForRoot = CmsMenuItem::findOne(['cms_hierarchy_item_id' => 0]);
			$isFallback = true;
		}
		
		if($menuItemForRoot != null)
			$pageContent = CmsPageContent::findOne(['id' => $menuItemForRoot->page_content_id]);
		
		if($pageContent == null)
			throw new NotFoundHttpException('No content could be found for the given id',404);
		
		return $this->render ( 'page', [ 
			'pageContentModel' => $pageContent,
			'isfallbacklanguage' => $isFallback,
		] );
	}
	
	/**
	 * show the page for a given cms menu item id
	 * @param integer $menuItemId
	 * @throws NotFoundHttpException an exception when the menu with the given id does not exists or does not have a linked page content 
	 * @return View
	 */
	public function actionPage($menuItemId) {
		$isFallback = false;
		$menuItemId = intval($menuItemId);
		$menuItem = CmsMenuItem::find()->where(['id' => $menuItemId])->with('pageContent')->one();
		if($menuItem == null){
			throw new NotFoundHttpException('No menu could be found for the given menu id',404);
		}
		$pageContent = $menuItem->pageContent;
		if($pageContent == null){
			throw new NotFoundHttpException('No page content could be found for the menu id',404);
		}
		
		return $this->render ( 'page', [
			'pageContentModel' => $pageContent,
			'isfallbacklanguage' => $isFallback,
		] );
	}
	
	/**
	 * show the page for a given page content id 
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
	 * (either by sending redirect url to let apache handle the request 
	 * [if the file is located in the web directory or a folder below the web directory], 
	 * or by sending file via php [if the file is not located within the web directory folder structure])
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
