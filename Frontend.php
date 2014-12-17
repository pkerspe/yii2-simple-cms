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
	const VERSION = '0.1';
	public $controllerNamespace = 'schallschlucker\simplecms\controllers\frontend';
	
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
}