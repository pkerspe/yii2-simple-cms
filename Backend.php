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
use schallschlucker\simplecms\controllers\backend\MediaController;

/**
 * The simple cms backend module
 *
 * @author Paul Kerspe
 */
class Backend extends \yii\base\Module {
	const VERSION = '0.1';
	
	public $controllerNamespace = 'schallschlucker\simplecms\controllers\backend';
	public $languageManager;
	public $cache;
	public $mimetypeMediaTypeMapping = [
		'IMAGE' => ['image/jpeg','image/gif','image/png'],
		'AUDIO' => ['audio/wav','audio/mpeg3','audio/x-mpeg-3','audio/x-mpequrl'],
		'VIDEO' => ['video/mpeg','video/quicktime','video/vdo','application/x-troff-msvideo','video/msvideo'],
	];
	
	/**
	 *
	 * @var array The rules to be used in URL management.
	 */
	public $urlRules = [ 
			'default/index' 
	];
	
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
		parent::__construct ( $id, $parent, $config );
	}
	
	/**
	 * Returns module components.
	 *
	 * @return array
	 */
	protected function getModuleComponents() {
		return [ 
				'languageManager' => [ 
						'class' => 'schallschlucker\simplecms\LanguageManager' 
				] 
		];
	}
	
	public function getLanguageManager(){
		if($this->languageManager == null || $this->languageManager == '' ){
			throw new InvalidConfigException("Module is not condfigured correctly, need to provide name of a configured languageManager compoenent");
		}
		$configuredLangManager = $this->languageManager;
		return Yii::$app->$configuredLangManager;
	}
	
	/**
	 * store a value to the cache, if the cache is configured. Just ignores value if cache is not configured
	 * @param unknown $cacheKey
	 * @param unknown $value
	 * @param unknown $caheLivetime
	 */
	public function setCacheValue($cacheKey, $value, $cacheLivetime){
		if($this->cache != null){
			Yii::$app->get($this->cache,true)->set($cacheKey, $value, $cacheLivetime);
		}
	}
	
	/**
	 * retrieve a value with the given cacheKey from the cache if a cache is configured at all and if the value could be found.
	 * If no cache is configured the fallbackValue is returned (by default this value is "false" if no parameter is given) 
	 * @param unknown $cacheKey
	 * @param string $fallbackValue
	 * @return unknown|string
	 */
	public function getCachedValue($cacheKey,$fallbackValue = false){
		if($this->cache != null){
			$value = Yii::$app->get($this->cache,true)->get($cacheKey);
			if($value !== false){
				return $value;
			}
		}	
		return $fallbackValue;
	}
	
	
	public function getMediaTypeForMimeType($mimeTypeString){
		foreach($this->mimetypeMediaTypeMapping as $mediaType => $mimetypes){
			/* @var $mimetypes array */
			if(in_array($mimeTypeString,$mimetypes)) return $mediaType;
		}
		return MediaController::$MEDIA_TYPE_UNKNOWN;
	}
	
	public function getMediarepositoryBasePath(){
		return Yii::getAlias('@webroot').DIRECTORY_SEPARATOR.'mediarepository';
	}
	
	public function getThumbnailRepostoryPath(){
		return Yii::getAlias('@webroot').DIRECTORY_SEPARATOR.'mediarepository'.DIRECTORY_SEPARATOR.'thumbnail_repository';
	}
}