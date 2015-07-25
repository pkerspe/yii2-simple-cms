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
 * The simple cms frontend module
 *
 * @author Paul Kerspe
 */
class Frontend extends \yii\base\Module {
	const VERSION = '0.2';
	public $controllerNamespace = 'schallschlucker\simplecms\controllers\frontend';
	public $defaultRoute='show';
	public $languageManager;
	public $renderTopMenuNavbar = true;
	public $cache;
	
	/**
	 * @var string The prefix for the frontend module URL.
	 * @See [[GroupUrlRule::prefix]]
	 */
	public $urlPrefix = 'cms'; //for the url in the forntend to be called
	public $routePrefix = 'simplecms_frontend'; //to map to the module id given in the config
	
	/**
	 *
	 * @var array The rules to be used in URL management.
	 */
	public $urlRules = [ 
		'home' => 'show/homepage',
		'page/<menuItemId:\d+>' => 'show/page',
		'c/<menuItemAlias:\w+>' => 'show/alias',
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
			throw new InvalidConfigException("Module is not configured correctly, need to provide name of a configured languageManager compoenent");
		}
		$configuredLangManager = $this->languageManager;
		return Yii::$app->$configuredLangManager;
	}
	
	public static function getLanguageManagerStatic(){
	    //FIXME: currently this function is used in backend and frontend, this is not very clean and should be changed
	    try {
	       return Yii::$app->getModule('simplecms_frontend')->getLanguageManager();
	    } catch (InvalidConfigException $e){
	        return Yii::$app->getModule('simplecms_backend')->getLanguageManager();
	    }
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
}