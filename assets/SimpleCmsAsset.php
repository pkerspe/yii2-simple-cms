<?php

/*
 * This file is part of the simple cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace schallschlucker\simplecms\assets;

use yii\web\AssetBundle;

class SimpleCmsAsset extends AssetBundle {
	public $css = ['css/cms.css'];
	public $js = [ ];
	
	// remove this in production once forum development is done
	public $publishOptions = ['forceCopy' => true];

	public $depends = [
		'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
	];
	
	public function init() {
		$this->sourcePath = ( __DIR__ . '/simplecms' );
		parent::init ();
	}
}
?>