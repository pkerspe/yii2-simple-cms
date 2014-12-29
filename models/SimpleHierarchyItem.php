<?php
namespace schallschlucker\simplecms\models;

use Yii;
use yii\helpers\Url;
/**
 * helper class for less memory consuming representation of an hierarhcy item with capability of representing a tree structure (parent/child relations)
 * @author Paul Kerspe
 *
 */
class SimpleHierarchyItem {
	public $children = [ ];
	public $parent_id;
	public $id;
	public $menu_id;
	public $content_id;
	public $document_id;
	public $direct_url;
	public $linkTarget;
	public $linkCssClass;
	public $languageId;
	public $languageCode;
	public $availableLanguageCodes = [ ];
	public $key;
	public $title;
	public $alias;
	public $expanded;
	public $position;
	public $displayState;
	public $isFallbackLanguage = false; // if current language is not available and fallback language is returned instead
	public $levelDepth;
	public $allLanguagesWithMarker = [];
	public $firstSibling = false;
	public $lastSibling = false;

	/**
	 * get an xml document from the current node and its children. the root node is called "menuStructure" which wraps all other nodes.
	 * @param string $isRootNodeToRender
	 * @return string
	 */
	public function getAsXmlString($isRootNodeToRender = true){
		$xmlString = "";
		if($isRootNodeToRender){
			$xmlString .= '<menuStructure>';
		}
		//go into recursion if needed
		$xmlString .= $this->getCurrentNodeAsXmlStringRecursive();

		if($isRootNodeToRender){
			$xmlString .= '</menuStructure>';
		}
		return $xmlString;
	}

	/**
	 * get the page tree from the current node as xml string
	 * @return string
	 */
	public function getCurrentNodeAsXmlStringRecursive(){
		$xmlStringToAppendTo  = '<hierarchyItem id="'.$this->id.'" displayState="'.$this->displayState.'" position="'.$this->position.'" >';
		$xmlStringToAppendTo .= '<menuItem id="'.$this->menu_id.'" pageId="'.$this->content_id.'" documentId="'.$this->document_id.'" languageId="'.$this->languageId.'" languageCode="'.$this->languageCode.'">';
		$xmlStringToAppendTo .= '<menuName><![CDATA['.$this->title.']]></menuName>';

		if($this->direct_url != null)
			$xmlStringToAppendTo .= '<directUrl><![CDATA['.$this->direct_url.']]></directUrl>';

		$xmlStringToAppendTo .= '</menuItem>';

		if(count ( $this->children ) > 0){
			$xmlStringToAppendTo .= '<children childcount="'.count ( $this->children ).'">';
			foreach($this->children as $child){
				$xmlStringToAppendTo .= $child->getCurrentNodeAsXmlStringRecursive();
			}
			$xmlStringToAppendTo .= '</children>';
		}
		$xmlStringToAppendTo .= '</hierarchyItem>';
		return $xmlStringToAppendTo;
	}

	function __construct($cmsHierarchyItemDetailsArray, $displayExpanded, $levelDepth) {
		$this->levelDepth = $levelDepth;
		$this->id = $cmsHierarchyItemDetailsArray ['id'];
		$this->key = $cmsHierarchyItemDetailsArray ['id'];
		$this->parent_id = $cmsHierarchyItemDetailsArray ['parent_id'];
		$this->position = $cmsHierarchyItemDetailsArray ['position'];
		if(isset($cmsHierarchyItemDetailsArray ['menu_item']) && $cmsHierarchyItemDetailsArray ['menu_item'] != null){
			$this->title = $cmsHierarchyItemDetailsArray ['menu_item'] ['name'];
			$this->alias = $cmsHierarchyItemDetailsArray ['menu_item'] ['alias'];
			$this->menu_id = $cmsHierarchyItemDetailsArray ['menu_item'] ['id'];
			$this->linkTarget = $cmsHierarchyItemDetailsArray ['menu_item'] ['link_target'];
			$this->linkCssClass = $cmsHierarchyItemDetailsArray ['menu_item'] ['link_css_class'];
			$this->content_id = $cmsHierarchyItemDetailsArray ['menu_item'] ['page_content_id'];
			$this->document_id = $cmsHierarchyItemDetailsArray ['menu_item'] ['document_id'];
			$this->direct_url = $cmsHierarchyItemDetailsArray ['menu_item'] ['direct_url'];
			$this->languageId = $cmsHierarchyItemDetailsArray ['menu_item'] ['language'];
			$this->languageCode = Yii::$app->controller->module->getLanguageManager()->getMappingForIdResolveAlias ( $cmsHierarchyItemDetailsArray ['menu_item'] ['language'] )['code'];
		}
		$this->expanded = $displayExpanded;
		$this->displayState = $cmsHierarchyItemDetailsArray ['display_state'];
		$this->isFallbackLanguage = (isset ( $cmsHierarchyItemDetailsArray ['displaying_fallback_language'] ) && $cmsHierarchyItemDetailsArray ['displaying_fallback_language']);

		foreach ( $cmsHierarchyItemDetailsArray ['available_menu_items_all_languages'] as $menuItem ) {
			$this->addAvailableLanguageCodes ( Yii::$app->controller->module->getLanguageManager()->getMappingForIdResolveAlias ( $menuItem ['language'] )['code'], $menuItem ['id'] );
		}

		// create an array with all languages, where the available languages are marked explicitly (this is used to display the existing and non existing language versions in the frontend
		foreach ( Yii::$app->controller->module->getLanguageManager()->getAllConfiguredLanguageCodes () as $languageId => $languageCode ) {
			$this->allLanguagesWithMarker [] = [
				'code' => $languageCode,
				'language_id' => $languageId,
				'available' => array_key_exists ( $languageCode, $this->availableLanguageCodes ),
				'menu_item_id' => (isset ( $this->availableLanguageCodes [$languageCode] )) ? $this->availableLanguageCodes [$languageCode] : ''
			];
		}
	}
	public function addAvailableLanguageCodes($languageCode, $menuItemId) {
		$this->availableLanguageCodes [$languageCode] = $menuItemId;
	}
	public function addChild($simpleHierarchyItem) {
		if ($simpleHierarchyItem instanceof SimpleHierarchyItem) {
			$this->children [] = $simpleHierarchyItem;
		} else {
			throw new Exception ( 'Wrong object type given as child element.' );
		}
	}
	/**
	 * set final valies and order children by position for beforeoutput as json encoded string to client
	 */
	public function finalizeForOutput() {
		if (count ( $this->children ) > 0) {
			// sort children by position
			usort ( $this->children, array (get_class($this->children [0]) ,"compare") );
			foreach ( $this->children as $child ) {
				$child->finalizeForOutput ();
			}
			$this->children [0]->firstSibling = true;
			end ($this->children)->lastSibling = true;
		}
	}
	
	public function getFormattedUrl(){
		if($this->content_id != null){
			if($this->alias != null && $this->alias != ''){
				return Url::toRoute(['show/alias','menuItemAlias' => $this->alias]);
			} else {
				return Url::toRoute(['show/page','menuItemId' => $this->menu_id]);
			}
		}
		else if($this->document_id)
			return Url::toRoute(['show/document','documentId' => $this->document_id]);
		else
			return $this->direct_url;
	}
	
	public function getLinkTag($cssClasses = '', $attributeString = '', $prependLinkText = ''){
		$cssCode = ($cssClasses != '' || $this->linkCssClass != '') ? ' class="'.$cssClasses.' '.$this->linkCssClass.'" ' : '';
		$linkTarget = ($this->linkTarget != null && $this->linkTarget != '') ? ' target="'.$this->linkTarget.'" ' : '';
		return '<a href="'.$this->getFormattedUrl().'"'.$cssCode.''.$linkTarget.' '.$attributeString.'>'.$this->title.$prependLinkText.'</a>';
	}
	
	/**
	 * 
	 * @return SimpleHierarchyItem[]:
	 */
	public function getAllChildren() {
		return $this->children;
	}

	/*
	 * static comparing function for sorting items depending on their position
	 */
	static function compare($a, $b) {
		$al = $a->position;
		$bl = $b->position;
		if ($al == $bl) {
			return 0;
		}
		return ($al > $bl) ? + 1 : - 1;
	}
}
?>