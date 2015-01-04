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
	
	public static $ROOT_MEDIA_CATEGORY_ID = 0;
	public static $MEDIA_IMAGE_BASE_CATEGORY_ID = 1;
	public static $MEDIA_VIDEO_BASE_CATEGORY_ID = 2;
	public static $MEDIA_AUDIO_BASE_CATEGORY_ID = 3;
	
	public static $MEDIA_UPLOAD_REPOSITORY_PATH = '/var/www/virtualhosts/www.einzelpflegefachkraft.de/curassist-app/backend/web/images/media-repositoy';
	public static $MEDIA_THUMBNAIL_REPOSITORY_PATH = '/var/www/virtualhosts/www.einzelpflegefachkraft.de/curassist-app/backend/web/images/thumbnail_repository';
	public static $MEDIA_THUMBNAIL_WIDTH = 100;
	public static $MEDIA_THUMBNAIL_HEIGHT = 100;
	public $defaultAction = 'mediabrowser';
	
	/**
	 * @menuLabel display root page of cms
	 * @menuIcon <span class="glyphicon glyphicon-list-alt"></span>
	 */
	public function actionMediabrowser() {
		$simpleMediaCategoryRoot = $this->getCategoryTree ();
		return $this->renderPartial ( 'mediaBrowser', [ 
			'categories' => $simpleMediaCategoryRoot 
		] );
	}
	
	/**
	 *
	 * @return View
	 */
	public function actionCategoryTreeJson() {
		$rootItem = $this->getCategoryTree ();
		
		Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
		$headers = Yii::$app->response->headers;
		$headers->add ( 'Content-Type', 'application/json; charset=utf-8' );
		Yii::$app->response->charset = 'UTF-8';
		return json_encode ( [ 
			$rootItem 
		], JSON_PRETTY_PRINT );
	}
	
	/**
	 * 
	 * @param unknown $categoryId
	 * @return string
	 */
	public function actionMediaForCategory($categoryId) {
		$categoryId = intval ( $categoryId );
		$category = CmsContentCategory::findOne ( $categoryId );
		$cmsContentMediaArray = CmsContentMedia::find ()->where ( [ 
			'content_category_id' => $categoryId 
		] )->with ( 'cmsContentMediaVariations' )->all ();
		return $this->renderPartial ( 'mediaForCategory', [ 
			'contentMediaArray' => $cmsContentMediaArray,
			'category' => $category 
		] );
	}
	
	/**
	 * send image data to browser
	 * 
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
	 * generate the abosolute path for a uploaded media file where to store the file.
	 * The folder structure is not the same as the virtual structure that is shown in the media browser. Instead for a new uploaded file a folder following the pattern '.../YYYY/MM/DD' will be created.
	 * Uses the value of  @see MediaController::$MEDIA_UPLOAD_REPOSITORY_PATH 
	 * @param UploadedFile $file
	 * @return string the full path to the storage folder for this file (note: the folder itself will NOT only be created automatically if the optional parameter createFolder is set to true!)
	 */
	private function getFullUploadPathForFile($file,$createFolder = false){
		$currentDate = new \DateTime();
		$formatedPath = $currentDate->format('Y/m/d');
		$targetFolder = BaseFileHelper::normalizePath(MediaController::$MEDIA_UPLOAD_REPOSITORY_PATH.DIRECTORY_SEPARATOR.$formatedPath).DIRECTORY_SEPARATOR;
		$fullFilePath = $targetFolder.$file->name;
		
		//check if folder exists, create if not
		if(!file_exists($targetFolder)){
			if(!mkdir($targetFolder,0755,true)){
				throw new Exception('Could not create target folder: '.$targetFolder);
			}
		}
		
		$conflictCounter = 1;
		while(file_exists($fullFilePath)){
			$fullFilePath = $targetFolder.$file->baseName.'_'.$conflictCounter.'.'.$file->extension;
			$conflictCounter++;
		}
		return $fullFilePath;
	}
	
	/**
	 * upload new media files and link to a given content category id
	 * @return string
	 */
	public function actionUpload($targetCategoryId) {
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
								$msg .= 'Unable to detec image dimensions for image '.$content->file_name;
							}
						}
						$content->created_datetime = new Expression( 'NOW()' );
						$content->createdby_userid = Yii::$app->user->id;
						if(!$content->insert(true)){
							$this->layout = 'modalLayout';
							return $this->render ( 'fileUpload', [
								'model' => $model,
								'errors' => $content->errors,
								'msg' => $msg
							] );
							//throw new Exception('The upload of one or more files failed. Most likely validation of properties failed');
						}
						$cmsMediaContentItemsArray[] = $content;
					} else {
						throw new Exception('The upload of one or more files failed.');
					}
				}
				$this->layout = 'modalLayout';
				return $this->render ( 'fileUploadSuccess', [
					'model' => $model,
					'uploadedCmsContentMediaArray' => $cmsMediaContentItemsArray,
					'msg' => $msg
				] );
			}
		}
		$this->layout = 'modalLayout';
		return $this->render ( 'fileUpload', [ 
			'model' => $model,
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
		return $cmsContentMedia->id . '_thumb.jpg';
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
		$targetThumbnailPath = MediaController::$MEDIA_THUMBNAIL_REPOSITORY_PATH . DIRECTORY_SEPARATOR . $this->getThumbnailFileName ( $cmsContentMedia );
		if (imagejpeg ( $tmp_img, $targetThumbnailPath )) {
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
		// check if thumbnail exists
		$filePath = FileHelper::normalizePath ( MediaController::$MEDIA_THUMBNAIL_REPOSITORY_PATH . DIRECTORY_SEPARATOR . $this->getThumbnailFileName ( $cmsContentMedia ) );
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
		if (! isset ( $associativeArray [0] ))
			throw new \Exception ( "category root item could not be found for id = 0. Cannot build category tree" );
		
		$simpleMediaCategoryRoot = $associativeArray [0];
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
