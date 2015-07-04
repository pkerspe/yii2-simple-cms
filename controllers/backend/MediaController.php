<?php
namespace schallschlucker\simplecms\controllers\backend;

/*
 * This file is part of the simple cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use schallschlucker\simplecms\controllers\backend\MediaController;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use schallschlucker\simplecms\models\SimpleMediaCategory;
use schallschlucker\simplecms\models\CmsContentCategory;
use schallschlucker\simplecms\models\CmsContentMedia;
use yii\web\NotFoundHttpException;
use yii\helpers\FileHelper;
use yii\base\NotSupportedException;
use yii\base\Exception;
use schallschlucker\simplecms\models\CmsContentMediaVariation;
use schallschlucker\simplecms\models\MediaBrowserImageUpload;
use yii\web\UploadedFile;
use yii\base\InvalidValueException;
use yii\helpers\BaseFileHelper;
use yii\db\Expression;
use yii\base\InvalidParamException;
use yii\base\UserException;
use schallschlucker\simplecms\models\MediaVariationManagerUpload;

/**
 * The controller for media elements in the cms (images/videos/audio files).
 * Default action is actionMediabrowser, which renders an all in one administration mask for creating and maintaining media elements.
 *
 * @menuLabel CMS Administration
 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
 */
class MediaController extends Controller {
	public static $MEDIA_TYPE_AUDIO = 'AUDIO';
	public static $MEDIA_TYPE_VIDEO = 'VIDEO';
	public static $MEDIA_TYPE_IMAGE = 'IMAGE';
	public static $MEDIA_TYPE_UNKNOWN = 'UNKOWN';
	
	public static $ROOT_MEDIA_CATEGORY_ID = 1;
	public static $MEDIA_IMAGE_BASE_CATEGORY_ID = 2;
	public static $MEDIA_VIDEO_BASE_CATEGORY_ID = 3;
	public static $MEDIA_AUDIO_BASE_CATEGORY_ID = 4;
	
	public static $MEDIA_THUMBNAIL_WIDTH = 100;
	public static $MEDIA_THUMBNAIL_HEIGHT = 100;
	
	public $defaultAction = 'mediabrowser';
	
	/**
	 * @menuLabel display media browser
	 * @menuIcon <span class="glyphicon glyphicon-camera"></span>
	 */
	public function actionMediabrowser($mediatype = null, $activeCategoryId = null) {
		return $this->renderPartial ( 'mediaBrowser', [ 
			'mediatype' => $mediatype,
			'activeCategoryId' => $activeCategoryId
		] );
	}
	
	
	/**
	 * create a new media category item and return the result in json formated way
	 * @menuLabel __HIDDEN__
	 * @return View
	 */
	public function actionCreateCategoryItemJson($parentCategoryId, $name) {
		$result = [];
		$result['message'] = '';
		$result['success'] = true;
	
		$parentCategoryId = intval($parentCategoryId);
		/* @var $parentContentMediaCategory CmsContentCategory */
		$parentContentMediaCategory = CmsContentCategory::findOne($parentCategoryId);
		if($parentContentMediaCategory == null){
			$result['success'] = false;
			$result['message'] .= 'The parent category id seems to be invalid (id = '.$parentCategoryId.' could not be found)';
		} else {
			$newCmsContentCategoryItem = new CmsContentCategory();
			$newCmsContentCategoryItem->displayname = $name;
			$newCmsContentCategoryItem->parent_id = $parentCategoryId;
			if($newCmsContentCategoryItem->insert()){
				$result['success'] = true;
				$result['message'] .= 'Category has been created';
				$result['newid'] = $newCmsContentCategoryItem->id;
			} else {
				$result['success'] = false;
				$result['message'] .= 'failed to create new category item';
				$result['errors'] = $newCmsContentCategoryItem->errors;
			}
		}
	
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( [
			$result
		], JSON_PRETTY_PRINT );
	}
	
	/**
	 * delete a media item and return the result in json formated way
	 * @menuLabel __HIDDEN__
	 * @return View
	 */
	public function actionDeleteMediaItemJson($mediaItemId) {
		$result = [];
		$result['message'] = '';
		$result['success'] = true;
		
		$mediaItemId = intval($mediaItemId);
		/* @var $contentMedia CmsContentMedia */
		$contentMedia = CmsContentMedia::findOne($mediaItemId);
		if($contentMedia == null){
			$result['success'] = false;
			$result['message'] = 'The media item id seems to be invalid (id = '.$mediaItemId.' could not be found)';			
		} else {
			$variations = $contentMedia->getCmsContentMediaVariations()->all();
			$deleteVariationsSuccess = true;
			foreach($variations as $variation){
				/* @var $variation CmsContentMediaVariation */
				$filePath = $variation->file_path.DIRECTORY_SEPARATOR.$variation->file_name;
				if(!$variation->delete()){
					$deleteVariationsSuccess = false;
					$result['success'] = false;
					$result['message'] = $result['message'].' Failed to delete variation (id = '.$variation->id.') of media item with id '.$mediaItemId;
				} else {
					//delete file from disk
					if(!unlink($filePath)){
						$deleteVariationsSuccess = false;
						$result['success'] = false;
						$result['message'] = $result['message'].' Database entry deleted, but failed to delete file in filesystem';
					}
				}
			}
			if($deleteVariationsSuccess) {
				if($contentMedia->delete()) {
					$filePath = $contentMedia->file_path.DIRECTORY_SEPARATOR.$contentMedia->file_name;
					$result['success'] = true;
					$result['message'] = $result['message'].' Item with id '.$mediaItemId. ' has been deleted.';
					if(!unlink($filePath)){
						$result['success'] = false;
						$result['message'] = $result['message'].' Database entry deleted, but failed to delete file in filesystem.';
					}
				} else {
					$result['success'] = false;
					$result['message'] = $result['message'].' Database entry could not be deleted.';
					$result['errors'] = $contentMedia->errors;
				}
			}
		}
		
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( [
			$result
		], JSON_PRETTY_PRINT );
	}
	
	/**
	 * move a media item to another category (=folder)
	 * @menuLabel __HIDDEN__
	 * @param integer $mediaItemId
	 * @param integer $targetCategoryId
	 */
	public function actionMoveMediaItemJson($mediaItemId,$targetCategoryId){
		$result = [];
		$result['message'] = '';
		$result['success'] = true;
		
		$mediaItemId = intval($mediaItemId);
		/* @var $contentMedia CmsContentMedia */
		$contentMedia = CmsContentMedia::findOne($mediaItemId);
		
		$targetCategoryId = intval($targetCategoryId);
		/* @var $contentCatgeory CmsContentCategory */
		$contentCatgeory = CmsContentCategory::findOne($targetCategoryId);

		if($contentMedia == null){
			$result['success'] = false;
			$result['message'] = 'The media item id seems to be invalid (id = '.$mediaItemId.' could not be found)';
		} else if($contentCatgeory == null){
			$result['success'] = false;
			$result['message'] = 'The category item id seems to be invalid (id = '.$contentCatgeory.' could not be found)';
		} else {
			$contentMedia->content_category_id = $contentCatgeory->id;
			if($contentMedia->update()){
				$result['success'] = true;
				$result['message'] = 'The media item has been moved to the category with the id '.$contentCatgeory->id.' successfully';
			} else {
				$result['success'] = false;
				$result['message'] = 'An error occured while trying to update the media item category id.)';
				$result['errors'] = $contentMedia->errors;
			}
		}		
		
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( [
			$result
		], JSON_PRETTY_PRINT );
	}
	
	/**
	 * @menuLabel __HIDDEN__
	 * @param unknown $categoryItemId
	 * @param unknown $newName the new name for the category (only character, numbers, spaces and _ - allowed. All other cahracters will be filtered out)
	 * @return View
	 */
	public function actionRenameContentCategoryJson($categoryItemId,$newName) {
		$result = [];
		$result['message'] = '';
		$result['success'] = true;
	
		$categoryItemId = intval($categoryItemId);
		$cleanedName = preg_replace('/[^a-zA-z\s0-9_\-]/','',$newName);
		if($cleanedName == ''){
			$result['success'] = false;
			$result['message'] = 'The category name seems to be invalid or empty';
		} else {
			/* @var $contentCategory CmsContentCategory */
			$contentCategory = CmsContentCategory::findOne($categoryItemId);
			if($contentCategory == null){
				$result['success'] = false;
				$result['message'] = 'The category id seems to be invalid (id = '.$categoryItemId.' could not be found)';
			} else {
				$contentCategory->displayname = $cleanedName;
				if($contentCategory->update()){
					$result['success'] = true;
					$result['message'] = 'The category has been renamed to '.$cleanedName;
				} else {
					$result['success'] = false;
					$result['message'] = 'Updating the database entry for the  category (id = '.$categoryItemId.') failed';
				}
			}
		}
	
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( [
			$result
		], JSON_PRETTY_PRINT );
	}
	
	
	/**
	 * delete a media item variation and return the result in json formated way
	 * @menuLabel __HIDDEN__
	 * @return View
	 */
	public function actionDeleteMediaVariationItemJson($mediaVariationItemId) {
		$result = [];
		$result['message'] = '';
		$result['success'] = true;
	
		$mediaVariationItemId = intval($mediaVariationItemId);
		/* @var $contentMediaVariation CmsContentMediaVariation */
		$contentMediaVariation = CmsContentMediaVariation::findOne($mediaVariationItemId);
		if($contentMedia == null){
			$result['success'] = false;
			$result['message'] .= 'The media item variation id seems to be invalid (id = '.$mediaVariationItemId.' could not be found)';
		} else {
			$filePath = $contentMediaVariation->file_path.DIRECTORY_SEPARATOR.$contentMediaVariation->file_name;
			if(!$contentMediaVariation->delete()){
				$result['success'] = true;
				$result['message'] .= 'Media variation item with id '.$mediaVariationItemId. ' has been deleted';
				if(!unlink($filePath)){
					$result['success'] = false;
					$result['message'] .= 'Database entry deleted, but failed to delete file in filesystem';
				}
			}
		}
	
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( [
			$result
		], JSON_PRETTY_PRINT );
	}
	
	/**
	 * delete a media category if it is empty and has no child items and return the result in json formated way
	 * @menuLabel __HIDDEN__
	 * @return View
	 */
	public function actionDeleteContentCategoryItemJson($contentCategoryId) {
		$result = [];
		$result['message'] = '';
		$result['success'] = true;
	
		$contentCategoryId = intval($contentCategoryId);
		/* @var $category CmsContentCategory */
		$category = CmsContentCategory::find()->where(['id' => $contentCategoryId])->with('cmsContentCategories')->one();
		if($category == null){
			$result['success'] = false;
			$result['message'] = 'The category item id seems to be invalid (id = '.$contentCategoryId.' could not be found)';
		} else {
			//check if item has children 
			if(count($category->cmsContentCategories) > 0){
				$result['success'] = false;
				$result['message'] = $result['message'].' Could not delete item, since it contains child elements. Delete the sub-items first.';
			} else {
				//check if media items are still linked to this category
				if(CmsContentMedia::find()->where(['content_category_id' => $category->id])->count() > 0){
					$result['success'] = false;
					$result['message'] = $result['message'].'Could not delete category, since it still contains media items. Delete the related media items first.';
				} else {
					if($category->delete()){
						$result['success'] = true;
						$result['message'] = $result['message'].' Item with id '.$category->id. ' has been deleted.';
					} else {
						$result['success'] = false;
						$result['message'] = $result['message'].'Failed to delete category item from database.';
						$result['errors'] = $category->errors;
					}
				}
			}
		}
	
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( [
			$result
		], JSON_PRETTY_PRINT );
	}
	
	/**
	 * Display the category tree in json format
	 * @menuLabel __HIDDEN__
	 * @return View
	 */
	public function actionCategoryTreeJson($mediaType = null) {
		$simpleMediaCategoryRoot = $this->getCategoryTree ();
		if($mediaType == null) $mediaType = '';
		$mediaType = strtoupper($mediaType);
		switch ($mediaType){
			case MediaController::$MEDIA_TYPE_IMAGE:
				/* @var $mediaArea SimpleMediaCategory */
				foreach($simpleMediaCategoryRoot->getChildren() as $mediaArea){
					if($mediaArea->key != MediaController::$MEDIA_IMAGE_BASE_CATEGORY_ID){
						$simpleMediaCategoryRoot->removeChild($mediaArea->key);
					}
				}
				break;
			case MediaController::$MEDIA_TYPE_VIDEO:
				foreach($simpleMediaCategoryRoot->getChildren() as $mediaArea){
					if($mediaArea->key != MediaController::$MEDIA_VIDEO_BASE_CATEGORY_ID){
						$simpleMediaCategoryRoot->removeChild($mediaArea->key);
					}
				}				
				break;
			case MediaController::$MEDIA_TYPE_AUDIO:
				foreach($simpleMediaCategoryRoot->getChildren() as $mediaArea){
					if($mediaArea->key != MediaController::$MEDIA_AUDIO_BASE_CATEGORY_ID){
						$simpleMediaCategoryRoot->removeChild($mediaArea->key);
					}
				}
				break;
			default:
				break;
		}
		
		
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( [ 
			$simpleMediaCategoryRoot 
		], JSON_PRETTY_PRINT );
	}
	
	/**
	 * Display all media for a given category id
	 * @menuLabel __HIDDEN__
	 * @param unknown $categoryId
	 * @return string
	 */
	public function actionMediaForCategory($categoryId) {
		$categoryId = intval ( $categoryId );
		$category = CmsContentCategory::findOne ( $categoryId );
		$cmsContentMediaArray = CmsContentMedia::find ()->where ( [ 
			'content_category_id' => $categoryId 
		] )->orderby('file_name ASC')->with ( 'cmsContentMediaVariations' )->all ();
		return $this->renderPartial ( 'mediaForCategory', [ 
			'contentMediaArray' => $cmsContentMediaArray,
			'category' => $category 
		] );
	}
	
	/**
	 * send image data to browser
	 * @menuLabel __HIDDEN__
	 * @param integer $mediaItemId
	 *        	the item id of the media item to be displayed
	 * @param integer $variationId
	 *        	optional parameter, for the variant to be displayed, or null if base media item should be displayed
	 * @throws NotFoundHttpException
	 */
	public function actionGetMedia($mediaItemId, $variationId = null) {
		$mediaItemId = intval ( $mediaItemId );
		
		$cmsContentMediaVariation = null;
		if ($variationId != null) {
			$variationId = intval ( $variationId );
			$cmsContentMediaVariation = CmsContentMediaVariation::findOne ( $variationId );
			if ($cmsContentMediaVariation == null) {
				throw new NotFoundHttpException ( 'Media item could not be found', 404 );
			}
		}
		$isWebaccessableFile = false;
		/* @var $mediaModel CmsContentMedia */
		$mediaModel = CmsContentMedia::findOne ( $mediaItemId );
		if ($mediaModel == null) {
			// TODO: do logging, maybe inform admin and display failure image instead
			throw new NotFoundHttpException ( 'Media item could not be found', 404 );
		}
		
		if ($cmsContentMediaVariation != null)
			$filePath = FileHelper::normalizePath ( $cmsContentMediaVariation->file_path . DIRECTORY_SEPARATOR . $cmsContentMediaVariation->file_name );
		else
			$filePath = FileHelper::normalizePath ( $mediaModel->file_path . DIRECTORY_SEPARATOR . $mediaModel->file_name );
		$webRootFolder = Yii::getAlias ( '@webroot' );
		$isWebaccessableFile = (stripos ( $filePath, $webRootFolder ) === 0);
		
		// check if item is in web folder, then redirect, or send file instead if not
		if ($isWebaccessableFile) {
			$webPath = substr ( $filePath, strlen ( $webRootFolder ) );
			Yii::$app->response->redirect ( $webPath );
		} else {
			if ($cmsContentMediaVariation != null)
				Yii::$app->response->sendFile ( $filePath, $cmsContentMediaVariation->file_name, [ 
					'mimeType' => $cmsContentMediaVariation->mime_type,
					'inline' => true 
				] )->send ();
			else
				Yii::$app->response->sendFile ( $filePath, $mediaModel->file_name, [ 
					'mimeType' => $mediaModel->mime_type,
					'inline' => true 
				] )->send ();
		}
	}
	
	/**
	 * display the media variation manager screen. Used in the media browser to display multiple version (e.g. Image formats) of a media item)
	 * @menuLabel __HIDDEN__
	 * @param unknown $mediaItemId
	 */
	public function actionMediaVarationManager($mediaItemId){
		$mediaItemId = intval ( $mediaItemId );
		
		$msg = '';
		/* @var $mediaItem CmsContentMedia */
		$mediaItem = CmsContentMedia::find()->where([ 'id' =>$mediaItemId ])->with('cmsContentMediaVariations')->one();
		if($mediaItem){
			$model = new MediaVariationManagerUpload();
			//check for upload
			if($model->load ( Yii::$app->request->post () ) && $model->validate()){
				$model->file = UploadedFile::getInstances ( $model, 'file' );
				if ($model->file != null) {
					foreach ( $model->file as $file ) {
						/* @VAR $file UploadedFile */
						$targetPath = $this->getFullUploadPathForFile($file);
						if($file->saveAs ( $targetPath )){
							$pathInfo = pathinfo($targetPath);
							$content = new CmsContentMediaVariation();
							$content->init();
							$content->mime_type = BaseFileHelper::getMimeType($targetPath);
							$content->file_name = $pathInfo['basename'];
							$content->file_path = $pathInfo['dirname'];
							$content->parent_content_media_id = $mediaItemId;
							$content->filesize_bytes = $file->size;
							if($mediaItem->media_type == MediaController::$MEDIA_TYPE_IMAGE){
								$dimensions = $this->getImageDimensions($targetPath);
								if($dimensions != null){
									$content->dimension_width = $dimensions['width']; 
									$content->dimension_height = $dimensions['height'];
								} else {
									$msg .= 'Unable to detect image dimensions for image '.$content->file_name;
								}
							}
							if(!$content->insert(true)){
								/**
								$this->layout = 'modalLayout';
								return $this->render ( 'fileUpload', [
									'model' => $model,
									'errors' => $content->errors,
									'mediaType' => $mediaType,
									'msg' => $msg
								] );
								*/
								throw new Exception('The upload of one or more files failed. Most likely validation of properties failed');
							}
						} else {
							throw new Exception('The upload of one or more files failed.');
						}
					}
				}
			} else {		
				$model->parentMediaId = $mediaItem->id;
				switch($mediaItem->media_type){
					case MediaController::$MEDIA_TYPE_AUDIO:
						$this->layout = 'modalLayout';
						return $this->render ( 'mediaVariationManager_audio', [
							'mediaItem' => $mediaItem,
							'model' => $model
						] );
						break;
					case MediaController::$MEDIA_TYPE_VIDEO:
						$this->layout = 'modalLayout';
						return $this->render ( 'mediaVariationManager_video', [
							'mediaItem' => $mediaItem,
							'model' => $model
						] );
						break;
					case MediaController::$MEDIA_TYPE_IMAGE:
						$this->layout = 'modalLayout';
						return $this->render ( 'mediaVariationManager_image', [
							'mediaItem' => $mediaItem,
							'model' => $model
						] );
						break;
					default:
						throw new UserException('The media item to manage variations for has an unknown media type');
						break;
				}
			}
		} else {
			throw new UserException('The media item could not be found for the given id');
		}
	}
	
	/**
	 * generate the abosolute path for a uploaded media file where to store the file.
	 * The folder structure is not the same as the virtual structure that is shown in the media browser. Instead for a new uploaded file a folder following the pattern '.../YYYY/MM/DD' will be created.
	 * Uses the value of  @see Frontend->getMediarepositoryBasePath() 
	 * @param UploadedFile $file
	 * @return string the full path to the storage folder for this file (note: the folder itself will NOT only be created automatically if the optional parameter createFolder is set to true!)
	 */
	private function getFullUploadPathForFile($file,$createFolder = false){
		$currentDate = new \DateTime();
		$cleanedFileName = str_replace(' ', '_', $file->name);
		$formatedPath = $currentDate->format('Y/m/d');
		$targetFolder = BaseFileHelper::normalizePath($this->module->getMediarepositoryBasePath().DIRECTORY_SEPARATOR.$formatedPath).DIRECTORY_SEPARATOR;
		$fullFilePath = $targetFolder.$cleanedFileName;
		
		//check if folder exists, create if not
		if(!file_exists($targetFolder)){
			if(!mkdir($targetFolder,0755,true)){
				throw new Exception('Could not create target folder: '.$targetFolder);
			}
		}
		
		$conflictCounter = 1;
		while(file_exists($fullFilePath)){
			$fullFilePath = $targetFolder.str_replace(' ', '_', $file->baseName).'_'.$conflictCounter.'.'.$file->extension;
			$conflictCounter++;
		}
		return $fullFilePath;
	}
	
	/**
	 * upload new media files and link to a given content category id
	 * @return string
	 */
	public function actionUpload($targetCategoryId,$mediaType = null) {
		if($mediaType != null && MediaController::$MEDIA_TYPE_AUDIO != $mediaType && MediaController::$MEDIA_TYPE_IMAGE != $mediaType && MediaController::$MEDIA_TYPE_VIDEO != $mediaType ){
			throw new InvalidParamException('The media type '.$mediaType.' is not known.');
		}
		
		$model = new MediaBrowserImageUpload();
		$targetCategoryId = intval($targetCategoryId);
		$model->targetCategoryId = $targetCategoryId; 
		$msg = "";
		
		if (Yii::$app->request->isPost) {
			$model->file = UploadedFile::getInstances ( $model, 'file' );
			//check if folder exists
			$cagtegory = CmsContentCategory::findOne($targetCategoryId);
			if($cagtegory == null){
				throw new InvalidValueException('the given catgeory id is not valid. Upload failed');
			}
			
			if ($model->file && $model->validate ()) {
				$cmsMediaContentItemsArray = [];
				foreach ( $model->file as $file ) {
					/* @VAR $file UploadedFile */
					$targetPath = $this->getFullUploadPathForFile($file);
					if($file->saveAs ( $targetPath )){
						$pathInfo = pathinfo($targetPath);
						$content = new CmsContentMedia();
						$content->init();
						$content->mime_type = BaseFileHelper::getMimeType($targetPath);
						$content->file_name = $pathInfo['basename'];
						$content->file_path = $pathInfo['dirname'];
						$content->filesize_bytes = $file->size;
						$content->content_category_id = $targetCategoryId;
						$content->media_type = $this->module->getMediaTypeForMimeType($content->mime_type);
						if($content->media_type == MediaController::$MEDIA_TYPE_IMAGE){
							$dimensions = $this->getImageDimensions($targetPath);
							if($dimensions != null){
								$content->dimension_width = $dimensions['width']; 
								$content->dimension_height = $dimensions['height'];
							} else {
								$msg .= 'Unable to detect image dimensions for image '.$content->file_name;
							}
						}
						$content->created_datetime = new Expression( 'NOW()' );
						$content->createdby_userid = Yii::$app->user->id;
						if(!$content->insert(true)){
							$this->layout = 'modalLayout';
							return $this->render ( 'fileUpload', [
								'model' => $model,
								'errors' => $content->errors,
								'mediaType' => $mediaType,
								'msg' => $msg
							] );
							//throw new Exception('The upload of one or more files failed. Most likely validation of properties failed');
						}
						$cmsMediaContentItemsArray[] = $content;
					} else {
						throw new Exception('The upload of one or more files failed.');
					}
				}
				//reload browser for current folder
				return $this->redirect(Url::toRoute(['media/mediabrowser','mediatype'=>$mediaType,'activeCategoryId' => $targetCategoryId]));
			}
		}
		$this->layout = 'modalLayout';
		return $this->render ( 'fileUpload', [ 
			'model' => $model,
			'mediaType' => $mediaType,
			'msg' => $msg
		] );
	}
	
	/**
	 * Show details page for a media itemd
	 * 
	 * @param integer $mediaItemId        	
	 */
	public function actionDetails($mediaItemId) {
		$mediaItemId = intval ( $mediaItemId );
		$cmsContentMedia = CmsContentMedia::find ()->where ( [ 
			'id' => $mediaItemId 
		] )->with ( 'cmsContentMediaVariations' )->one ();
		if ($cmsContentMedia == null) {
			throw new NotFoundHttpException ( 'Media item could not be found', 404 );
		}
		return $this->renderPartial ( 'mediaDetails', [ 
			'cmsContentMedia' => $cmsContentMedia 
		] );
	}
	
	/**
	 *
	 * @param CmsContentMedia $cmsContentMedia        	
	 */
	private function getThumbnailFileName($cmsContentMedia) {
		switch($cmsContentMedia->mime_type){
			case 'image/gif':
				return $cmsContentMedia->id . '_thumb.gif';
			default:
				return $cmsContentMedia->id . '_thumb.jpg';
		}
	}
	
	/**
	 *
	 * @param CmsContentMedia $cmsContentMedia        	
	 * @param unknown $thumbWidth        	
	 */
	private function createThumbnail($cmsContentMedia, $thumbMaxWidth, $thumbMaxHeight) {
		$filePath = $cmsContentMedia->file_path . DIRECTORY_SEPARATOR . $cmsContentMedia->file_name;
		switch ($cmsContentMedia->mime_type) {
			case 'image/jpeg' :
				$img = imagecreatefromjpeg ( $filePath );
				break;
			case 'image/png' :
				$img = imagecreatefrompng ( $filePath );
				break;
			case 'image/gif' :
				$img = imagecreatefromgif ( $filePath );
				break;
			default :
				throw new NotSupportedException ( 'The mime-type ' . $cmsContentMedia->mime_type . ' is not supported for thumbnail generation at the moment' );
				break;
		}
		$width = imagesx ( $img );
		$height = imagesy ( $img );
		
		// calculate thumbnail size
		if ($thumbMaxWidth >= $thumbMaxHeight) {
			$new_width = $thumbMaxWidth;
			$new_height = floor ( $height * ($thumbMaxWidth / $width) );
		} else {
			$new_height = $thumbMaxHeight;
			$new_width = floor ( $width * ($thumbMaxHeight / $height) );
		}
		
		// create a new temporary image
		$tmp_img = imagecreatetruecolor ( $new_width, $new_height );
		$bgc = ImageColorAllocate ( $tmp_img, 255, 255, 255 );
		ImageFilledRectangle ( $tmp_img, 0, 0, $new_width, $new_height, $bgc );
		
		// copy and resize old image into new image
		imagecopyresampled ( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
		
		// save thumbnail into a file
		$newThumbFilename = $this->getThumbnailFileName ( $cmsContentMedia );
		$targetThumbnailPath = $this->module->getThumbnailRepostoryPath() . DIRECTORY_SEPARATOR . $newThumbFilename;
		$success = false;
		if(stripos($newThumbFilename , '.gif') !== false){
			$success = imagegif($tmp_img, $targetThumbnailPath );
		} else {
			$success = imagejpeg( $tmp_img, $targetThumbnailPath );
		}
		if($success === true) {
			return $targetThumbnailPath;
		} else
			return false;
	}
	
	/**
	 * 
	 * @param unknown $file
	 */
	private function getImageDimensions($file){
		$imageDimensions = getimagesize($file);
		if(!$imageDimensions){
			return null;
		} else {
			$width = $imageDimensions[0];
			$height = $imageDimensions[1];
			return ['width' => $width, 'height' => $height];
		}
	}
	
	/**
	 * display a thumbnail version for the media browser.
	 * Generate it if none could be found in thumbnail repository
	 * 
	 * @param integer $mediaItemId        	
	 * @throws NotFoundHttpException
	 */
	public function actionThumbnail($mediaItemId) {
		$mediaItemId = intval ( $mediaItemId );
		$isWebaccessableFile = false;
		/* @var $mediaModel CmsContentMedia */
		$cmsContentMedia = CmsContentMedia::findOne ( $mediaItemId );
		if ($cmsContentMedia == null) {
			// TODO: do logging, maybe inform admin and display failure image instead
			throw new NotFoundHttpException ( 'Media item could not be found', 404 );
		}
		//check if thumbnail folder exists
		if(!is_dir($this->module->getThumbnailRepostoryPath())){
			if(!mkdir($this->module->getThumbnailRepostoryPath(),0777,true)){
				throw new Exception ( 'Thumbnail folder not found and automatic generation failed. Check access rights for base folder.' );
			}
		}
		// check if thumbnail exists
		$filePath = FileHelper::normalizePath ( $this->module->getThumbnailRepostoryPath() . DIRECTORY_SEPARATOR . $this->getThumbnailFileName ( $cmsContentMedia ) );
		if (! file_exists ( $filePath )) {
			// generate thumbnail
			$filePath = $this->createThumbnail ( $cmsContentMedia, MediaController::$MEDIA_THUMBNAIL_WIDTH, MediaController::$MEDIA_THUMBNAIL_HEIGHT );
			if (! $filePath) {
				throw new Exception ( 'Thumbnail not found, but automatic generation failed.' );
			}
		}
		$webRootFolder = Yii::getAlias ( '@webroot' );
		$isWebaccessableFile = (stripos ( $filePath, $webRootFolder ) === 0);
		
		// check if item is in web folder, then redirect, or send file instead if not
		if ($isWebaccessableFile) {
			$webPath = substr ( $filePath, strlen ( $webRootFolder ) );
			Yii::$app->response->redirect ( $webPath );
		} else {
			Yii::$app->response->sendFile ( $filePath, $cmsContentMedia->file_name, [ 
				'mimeType' => $cmsContentMedia->mime_type,
				'inline' => true 
			] )->send ();
		}
	}
	
	/**
	 * get the complete media category tree as SimpleMediaCategory instances tree
	 * 
	 * @return \schallschlucker\simplecms\models\SimpleMediaCategory
	 */
	public function getCategoryTree() {
		$allRowsArray = CmsContentCategory::find ()->orderBy ( 'id ASC' )->asArray ( true )->all ();
		$associativeArray = [ ];
		foreach ( $allRowsArray as $fieldArray ) {
			/* @var $fieldArray array */
			$simpleMediaCategoryRoot = new SimpleMediaCategory ();
			$simpleMediaCategoryRoot->initFromArray ( $fieldArray );
			$associativeArray [$fieldArray ['id']] = $simpleMediaCategoryRoot;
		}
		unset ( $allRowsArray );
		// init root item
		if (! isset ( $associativeArray [MediaController::$ROOT_MEDIA_CATEGORY_ID] ))
			throw new \Exception ( "category root item could not be found for id = ".MediaController::$ROOT_MEDIA_CATEGORY_ID.". Cannot build category tree" );
		
		$simpleMediaCategoryRoot = $associativeArray [MediaController::$ROOT_MEDIA_CATEGORY_ID];
		$this->fillCategoryTreeRecursive ( $simpleMediaCategoryRoot, $associativeArray );
		
		return $simpleMediaCategoryRoot;
	}
	
	/**
	 *
	 * @param SimpleMediaCategory $parent        	
	 * @param SimpleMediaCategory $allSimpleMediaCategoryItems        	
	 */
	public function fillCategoryTreeRecursive($parent, $allSimpleMediaCategoryItems) {
		foreach ( $allSimpleMediaCategoryItems as $id => $simpleMediaCategoryItem ) {
			/* @var $simpleMediaCategoryItem SimpleMediaCategory */
			if ($simpleMediaCategoryItem->parent_id != null && $parent->key == $simpleMediaCategoryItem->parent_id) {
				$parent->addChild ( $simpleMediaCategoryItem );
				unset ( $allSimpleMediaCategoryItems [$id] );
				$this->fillCategoryTreeRecursive ( $simpleMediaCategoryItem, $allSimpleMediaCategoryItems );
			}
		}
	}
}
?>
