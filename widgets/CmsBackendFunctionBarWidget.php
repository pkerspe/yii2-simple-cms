<?php

namespace schallschlucker\simplecms\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\rbac\Item;
use yii\caching\TagDependency;
use yii\web\Response;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use Exception;
use ReflectionClass;
use schallschlucker\simplecms\controllers\backend\CmsHierarchyController;
use schallschlucker\simplecms\controllers\backend\DefaultController;
use yii\helpers\Url;

class CmsBackendFunctionBarWidget extends Widget {
	public $controllers = [ 
		'schallschlucker\simplecms\controllers\backend\DefaultController',
		'schallschlucker\simplecms\controllers\backend\CmsHierarchyController' ,
		'schallschlucker\simplecms\controllers\backend\SettingsAndMaintenanceController'
	];
	
	public function init() {
		parent::init ();
	}
	
	public function run() {
		$index = Url::toRoute('default/index');
		$widgetHtml = <<<TEXT
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="$index">Simple CMS Backend</a>
    </div>

    <div class="collapse navbar-collapse" id="cms-navbar-collapse-1">
		<ul class="nav navbar-nav">
TEXT;
		$cmsBackendNavGroups = $this->groupActionsByLabel();
		
		foreach($cmsBackendNavGroups as $cmsBackendNavGroup){
			$widgetHtml .= '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'.$cmsBackendNavGroup->groupIcon.' '.$cmsBackendNavGroup->groupDisplayName.'<span class="caret"></span></a><ul class="dropdown-menu" role="menu">';
				
			foreach($cmsBackendNavGroup->navItems as $cmsBackendNavItem){
				$widgetHtml .= '<li><a href="'.Url::toRoute($cmsBackendNavItem->actionPath).'">'.$cmsBackendNavItem->displayIcon.' '.$cmsBackendNavItem->displayName.'</a></li>';
			}
			$widgetHtml .= '</ul></li>';
		}
		
		$widgetHtml .= <<<TEXT
      	</ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
TEXT;
		$widgetHtml .= '';
		return $widgetHtml;
	}
	
	public function groupActionsByLabel(){
		$result = $this->getAllVisibleActions('/simplecms_backend/');
		$cmsBackendNavGroups = [];
		foreach($result as $controllerName => $controllerDetails){
			$label = '';
			//get label from annotation if set
			if(isset($controllerDetails['annotations']) && isset($controllerDetails['annotations']['@menuLabel'])){
				$label = $controllerDetails['annotations']['@menuLabel']['value'];
			} else {
				$label = substr($controllerName, strrpos($controllerName,'\\'));
			}
			$icon = '';
			if(isset($controllerDetails['annotations']) && isset($controllerDetails['annotations']['@menuIcon']['value']))
				$icon = $controllerDetails['annotations']['@menuIcon']['value'];
			
			$cmsBackendNavGroup = null;
			if(isset($cmsBackendNavGroups[$label])){
				$cmsBackendNavGroup = $cmsBackendNavGroups[$label];
			} else {
				$cmsBackendNavGroup = new CmsBackendNavGroup($label,$icon);
			}
			foreach($controllerDetails['actions'] as $actionDetails){
				$cmsBackendNavItem = new CmsBackendNavItem();
				$cmsBackendNavItem->actionPath = $actionDetails['route'];
				if(isset($actionDetails['annotations']['@menuIcon']))
					$cmsBackendNavItem->displayIcon = $actionDetails['annotations']['@menuIcon']['value'];
					
				if(isset($actionDetails['annotations']['@menuLabel']))
					$cmsBackendNavItem->displayName = $actionDetails['annotations']['@menuLabel']['value'];
				else
					$cmsBackendNavItem->displayName = $actionDetails['route'];
				
				if(isset($actionDetails['annotations']['@functionalRight']))
					$cmsBackendNavItem->functionalRight = $actionDetails['annotations']['@functionalRight']['value'];
				if($cmsBackendNavItem->displayName != '__HIDDEN__'){
					$cmsBackendNavGroup->addNavItem($cmsBackendNavItem);
				}
			}
			$cmsBackendNavGroups[$label] = $cmsBackendNavGroup;
		}
		return $cmsBackendNavGroups;
	}
	
	public function getAllVisibleActions() {
		$result = [];
		foreach ( $this->controllers as $controller ) {
			$class = new \ReflectionClass ( $controller );
			//get class annotations
			$resultCount = preg_match_all ( '|.* (@.*)|', $class->getDocComment (), $matches );
			$annotations = [ ];
			if ($resultCount > 0) {
				$annotationStrings = $matches [1];
				foreach ( $annotationStrings as $annotationString ) {
					$annotationName = trim ( substr ( $annotationString, 0, strpos ( $annotationString, ' ' ) ) );
					$annotationValue = trim ( substr ( $annotationString, strpos ( $annotationString, ' ' ) ) );
					$annotations [$annotationName] = [
						'annotationName' => $annotationName,
						'value' => $annotationValue
					];
				}
			}
			$result [$controller]['annotations'] = $annotations;
			
			
			//check all methods of this class
			foreach ( $class->getMethods () as $method ) {
				$matches = [];
				$name = $method->getName ();
				if ($method->isPublic () && ! $method->isStatic () && strpos ( $name, 'action' ) === 0 && $name !== 'actions') {
					$resultCount = preg_match_all ( '|.* (@.*)|', $method->getDocComment (), $matches );
					$annotations = [ ];
					if ($resultCount > 0) {
						$annotationStrings = $matches [1];
						foreach ( $annotationStrings as $annotationString ) {
							$annotationName = trim ( substr ( $annotationString, 0, strpos ( $annotationString, ' ' ) ) );
							$annotationValue = trim ( substr ( $annotationString, strpos ( $annotationString, ' ' ) ) );
							$annotations [$annotationName] = [ 
								'annotationName' => $annotationName,
								'value' => $annotationValue 
							];
						}
					}
					$controllerName = substr($controller, strrpos($controller,'\\')+1);
					$controllerId = substr($controllerName, 0, strpos($controllerName,'Controller'));

					$result [$controller] ['actions'] [] = [ 
						'controller' => $controllerName,
						'action' => $name,
						'route' =>  Inflector::camel2id($controllerId).'/'.Inflector::camel2id ( substr ( $name, 6 ) ),
						'annotations' => $annotations 
					];
				}
			}
		}
		return $result;
	}
}

class CmsBackendNavGroup {
	public $navItems = [];
	public $groupDisplayName = '';
	public $groupIcon = '';
	
	public function __construct($name, $icon){
		$this->groupDisplayName = $name;
		$this->groupIcon = $icon;
	}
	
	public function addNavItem($cmsBackendNavItem){
		if($cmsBackendNavItem instanceof CmsBackendNavItem){
			$this->navItems[] = $cmsBackendNavItem;
		} else 
			throw new Exception('Invalid nav item type given.');
	}
}

class CmsBackendNavItem {
	public $displayName = '';
	public $actionPath = '';
	public $displayIcon = '';
	public $functionalRight = null;
}
?>