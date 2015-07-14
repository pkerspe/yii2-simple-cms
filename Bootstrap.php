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

use yii\base\BootstrapInterface;
use yii\web\GroupUrlRule;

/**
 * Bootstrap class registers module and url rules which will be applied
 * when UrlManager.enablePrettyUrl is enabled.
 *
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
//         if (!$app->hasModule('simplecms_frontend')) {
//             $app->setModule('simplecms_frontend', [
//                 'class' => 'schallschlucker\simplecms\Frontend'
//             ]);
//         }

        /** @var $module Module */
         $module = $app->getModule('simplecms_frontend');
		 

         if (!$app instanceof \yii\console\Application) {
             $configUrlRule = [
                 'prefix' => $module->urlPrefix,
				 'routePrefix' => $module->routePrefix,
                 'rules'  => $module->urlRules
             ];
             $app->get('urlManager')->rules[] = new GroupUrlRule($configUrlRule);
         }
        $app->get('i18n')->translations['simplecms*'] = [
        	'class'    => 'yii\i18n\PhpMessageSource',
        	'basePath' => __DIR__ . '/messages',
        ];
    }
}