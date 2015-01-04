<?php
namespace schallschlucker\simplecms\models;

use Yii;
use yii\helpers\Url;
/**
 * helper class for less memory consuming representation of media category with capability of representing a tree structure (parent/child relations)
 * @author Paul Kerspe
 *
 */
class SimpleMediaCategory {
	public $children = [];
	/**
	 * the display name if this category
	 * @var String
	 */
	public $title;
	/**
	 * The category id
	 * @var integer
	 */
	public $key;
	/**
	 * the parent category id
	 * @var integer
	 */
	public $parent_id;
	
	public $folder = true;
	public $expanded = true;
	
	/**
	 * init this instance from a db query result field array
	 * @param array $dbFieldArray
	 */
	public function initFromArray($dbFieldArray){
		$this->key = $dbFieldArray['id'];
		$this->parent_id = $dbFieldArray['parent_id'];
		$this->title = $dbFieldArray['displayname'];
	}
	
	/**
	 * 
	 * @param SimpleMediaCategory $impleMediaCategory
	 */
	public function addChild($impleMediaCategory){
		$this->children[] = $impleMediaCategory;
	}
	
	public function removeChild($itemId){
		foreach($this->children as $key => $simpleMediaCategory){
			if($simpleMediaCategory->key == $itemId){
				unset($this->children[$key]);
				break;
			}	
		}
	}
	
	/**
	 * get all children form the current category
	 * @return SimpleMediaCategory[]
	 */
	public function getChildren(){
		return $this->children;
	}
}