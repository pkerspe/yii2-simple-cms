<?php

/*
 * This file is part of the simple cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace schallschlucker\simplecms;

use Yii;
use yii\base\InvalidConfigException;

/**
 * The simple cms media manager to deliver iamages to backend and frontend clients
 *
 * @author Paul Kerspe
 */
class MediaManager extends \yii\base\Module {
	const VERSION = '0.2';
	public $controllerNamespace = 'schallschlucker\simplecms\controllers\mediacontroller';
	public $defaultRoute='media';
	public $mediarepositoryPath;
	public $mimetypeMediaTypeMapping = [
	    'IMAGE' => ['image/jpeg','image/gif','image/png'],
	    'AUDIO' => ['audio/wav','audio/mpeg3','audio/x-mpeg-3','audio/x-mpequrl'],
	    'VIDEO' => ['video/mpeg','video/quicktime','video/vdo','application/x-troff-msvideo','video/msvideo'],
	];
	
	/**
	 * @var string The prefix for the frontend module URL.
	 * @See [[GroupUrlRule::prefix]]
	 */
	public $urlPrefix = 'media'; //for the url in the forntend to be called
	public $routePrefix = 'media_manager'; //to map to the module id given in the config
	
	/**
	 * Returns module components.
	 *
	 * @return array
	 */
	protected function getModuleComponents() {
	    return [
	    ];
	}
	
	/**
	 * @inheritdoc
	 */
	public function __construct($id, $parent = null, $config = []) {
		foreach ( $this->getModuleComponents () as $name => $component ) {
			if (! isset ( $config ['components'] [$name] )) {
				$config ['components'] [$name] = $component;
			} elseif (is_array ( $config ['components'] [$name] ) && ! isset ( $config ['components'] [$name] ['class'] )) {
				$config ['components'] [$name] ['class'] = $component ['class'];
			}
		}
		if( empty($this->mediarepositoryPath) ){
		    $this->mediarepositoryPath = Yii::getAlias('@webroot').DIRECTORY_SEPARATOR.'mediarepository';
		}
		parent::__construct ( $id, $parent, $config );
	}

	public function getMediaTypeForMimeType($mimeTypeString){
	    foreach($this->mimetypeMediaTypeMapping as $mediaType => $mimetypes){
	        /* @var $mimetypes array */
	        if(in_array($mimeTypeString,$mimetypes)) return $mediaType;
	    }
	    return MediaController::$MEDIA_TYPE_UNKNOWN;
	}
	
	public function getMediarepositoryBasePath(){
	    //use FileHelper::normalizePath () here maybe, to make sure path is somewhat cleaned
	    return $this->mediarepositoryPath;
	    //return Yii::getAlias('@webroot').DIRECTORY_SEPARATOR.'mediarepository';
	}
	
	public function getThumbnailRepostoryPath(){
	    return $this->mediarepositoryPath.DIRECTORY_SEPARATOR.'thumbnail_repository';
	    //return Yii::getAlias('@webroot').DIRECTORY_SEPARATOR.'mediarepository'.DIRECTORY_SEPARATOR.'thumbnail_repository';
	}
}