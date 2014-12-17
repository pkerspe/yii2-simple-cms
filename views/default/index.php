<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use schallschlucker\simplecms\models\CmsHierarchyItem;
use schallschlucker\simplecms\models\MenuItemAndContentForm;

use schallschlucker\simplecms\assets\CmsAsset;

CmsAsset::register ( $this );
$this->title = Yii::t ( 'app/cms', 'CMS Administration' );
$this->params ['breadcrumbs'] [] = $this->title;
?>
<div class="pn_cms-default-index">
	<h1><?= $this->title ?></h1>
	<p></p>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="cms-administration-main-tree-view-form">
				<?php $form = ActiveForm::begin(['options' => ['class'=>'form-inline']]); ?>

				<?= $field = $form->field($model, 'treeDisplayLanguageId')->dropDownList($this->context->module->getConfiguredIdLanguagesMappingTranslated(\Yii::$app->language),['class'=>'input-small']); ?>

				<?= $form->field($model, 'expandFolderDepth')->dropDownList([9999=>'all',1=>Yii::t('app/cms','expand folders until {0,ordinal} level',1),2=>Yii::t('app/cms', 'expand folders until {0,ordinal} level',2),3=>Yii::t('app/cms', 'expand folders until {0,ordinal} level',3),4=>Yii::t('app/cms', 'expand folders until {0,ordinal} level',4),5=>Yii::t('app/cms', 'expand folders until {0,ordinal} level',5)],['class'=>'input-small'])?>

				<?= $field = $form->field($model, 'hideItemsWithMissingLanguage')->checkbox(); ?>
				
				<div class="form-group">
					<?= Html::submitButton(Yii::t('app/cms', 'Refresh') , ['class' => 'btn btn-success btn-xs'])?>
				</div>

				<?php ActiveForm::end(); ?>
			</div>
		</div>

		<table id="menuHierarchyTree"
			class="table table-condensed table-hover">
			<colgroup>
				<col width="400px">
				<col width="15px">
				<col width="50px">
				<col width="200px">
				<col width="300px">
			</colgroup>

			<thead>
				<tr>
					<th><?= Yii::t('app/cms', 'menu item name') ?></th>
					<th><?= Yii::t('app/cms', 'type') ?></th>
					<th><?= Yii::t('app/cms', 'displayed' ) ?></th>
					<th><?= Yii::t('app/cms', 'item languages') ?></th>
					<th><?= Yii::t('app/cms', 'status') ?></th>
				</tr>
			</thead>

			<tbody>
			</tbody>
		</table>
	</div>
</div>

<div id="dialog-confirm" class="cms_menu_dialog"
	title="Create new Menu Item">
	<div id="nodeDetails"></div>
<?php
$form = ActiveForm::begin ( [ 
		'id' => 'newMenuDialogForm' 
] );
echo Yii::t ( 'app/cms', 'Please enter the name for the new language version of this menu item' );
?>
	<input type="input" id="newMenuName" name="newMenuName" value="" /> <input
		type="hidden" id="position" name="position" value="" /> <input
		type="hidden" id="language" name="language" value="" /> <input
		type="hidden" id="parentHierarchyItemId" name="parentHierarchyItemId"
		value="" />
<?php
echo Yii::t ( 'app/cms', 'Please select the content type:' );
echo $form->field ( $model_wrapperform, 'contentType' )->radioList ( [ 
		MenuItemAndContentForm::CONTENT_TYPE_PAGE => 'content page',
		MenuItemAndContentForm::CONTENT_TYPE_DOCUMENT => 'linked document',
		MenuItemAndContentForm::CONTENT_TYPE_URL => 'URL' 
] )->label ( false );
?>
	<div class="form-group">
		<?= Html::a(Yii::t('app/cms', 'Continue'), ['#'], ['class' => 'btn btn-primary','onclick' => 'performAjaxCallForNewMenu();'])?>
		<?= Html::a(Yii::t('app/cms', 'Cancel'), ['#'], ['class' => 'btn btn-warning','onclick' => '$(\'#newMenuDialogForm\').trigger("reset");dialog.dialog("close");'])?>
	</div>
<?php
ActiveForm::end ();
?>
</div>
<?php
$jsonLangMapping = json_encode ( $this->context->module->getConfiguredLanguageIdToCodeMapping () );
$jsonTreeSourceUrl = Url::to ( [ 
		'cms-hierarchy/page-tree-json',
		'language' => $model->treeDisplayLanguageId,
		'expandLevel' => $model->expandFolderDepth,
		'hideMissingLanguages' => $model->hideItemsWithMissingLanguage 
] );
$updateSiblingsPositionUrl = Url::to ( [ 
		'cms-hierarchy/set-item-position-within-siblings-json' 
] );
$ajaxUpdateParentAndPositionUrl = Url::to ( [ 
		'cms-hierarchy/set-item-parent-and-position-json' 
] );
$ajaxCreateNewHierarchyItemAndMenuUrl = Url::to ( [ 
		'cms-hierarchy/create-hierarchy-item-json' 
] );
$updateDisplayStateUrl = Url::to ( [ 
		'cms-hierarchy/set-display-state-json' 
] );

$translation1 = Yii::t ( 'app/cms', 'Create new entry for language code: ' );
$translation2 = Yii::t ( 'app/cms', 'Edit language version for language code: ' );
$translation3 = Yii::t ( 'app/cms', 'Item not available in requested language, displaying a fallback language instead.' );

$editMenuItemUrlWithReplace = Url::toRoute ( [ 
		'default/edit-menu-language-version',
		'menuItemId' => '_ID_' 
] );
$createMenuItemUrlWithReplace = Url::toRoute ( [ 
		'default/create-menu-language-version',
		'hierarchyItemId' => '_HIERARCHY_ITEM_ID_',
		'languageId' => '_LANGUAGE_ID_' 
] );

$displayStatusHidden = CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_HIDDEN_IN_NAVIGATION;
$displayStatusVisible = CmsHierarchyItem::DISPLAYSTATE_PUBLISHED_VISIBLE_IN_NAVIGATION;
$displayStatusUnpublished = CmsHierarchyItem::DISPLAYSTATE_UNPUBLISHED;

$languageIdMappingFunction = <<<JS
var jsonTreeSourceUrl = '$jsonTreeSourceUrl';
var translation_create_new_for_lang = '$translation1';
var translation_edit_language_version = '$translation2';
var translation_displaying_fallback_language = '$translation3';
var editMenuLink = '$editMenuItemUrlWithReplace';
var createMenuLink = '$createMenuItemUrlWithReplace';
var displayStatusHidden = '$displayStatusHidden';
var displayStatusVisible = '$displayStatusVisible';
var displayStatusUnpublished = '$displayStatusUnpublished';
var ajaxUpdateDisplayStateUrl = '$updateDisplayStateUrl';
var ajaxUpdateSiblingsPositionUrl = '$updateSiblingsPositionUrl';
var ajaxUpdateParentAndPositionUrl = '$ajaxUpdateParentAndPositionUrl';
var ajaxCreateNewHierarchyItemAndMenuUrl = '$ajaxCreateNewHierarchyItemAndMenuUrl';
function getAllConfiguredLanguages(){
	var langMapping = $jsonLangMapping;
	return langMapping;
}
JS;

$this->registerJs ( $languageIdMappingFunction, View::POS_END, 'cmsTreeViewSettingsAndHelper' );

$pageTreeScript = <<<'JS'
var dialog;
$(function() {
	dialog = $( "#dialog-confirm" ).dialog({
		autoOpen: false,
		resizable: true,
		height:280,
		width: 530,
		modal: true,
	});
});

function fillNewMenuFormAnd(parentNode,newItemPosition){
	dialog.dialog("open");
	$("#parentHierarchyItemId").val(parentNode.data.id);
	$("#language").val(parentNode.data.languageId);
	$("#position").val(newItemPosition);
}

function performAjaxCallForNewMenu(){
	form = $("#newMenuDialogForm");
	
	parentHierarchyItemId = $("#parentHierarchyItemId").val();
	newMenuName = $("#newMenuName").val();
	position = $("#position").val();
	language = $("#language").val();
	contentType = $( "#newMenuDialogForm input:radio:checked" ).val();
	
	//alert("sending ajax request for values: parent="+parentHierarchyItemId+", name="+newMenuName+", position= "+position+", lang="+language+", content="+contentType);
	
	jQuery.ajax({
		url: ajaxCreateNewHierarchyItemAndMenuUrl,
		data : {
			parentHierarchyItemId : parentHierarchyItemId,
			newMenuName : newMenuName,
			position : position,
			language : language,
			contentType : contentType
		},
		dataType: 'json',
		success: function(result){
			console.log(result);
			if(result.result == 'success'){
				//add child
				fancyTreeInstance = $("#menuHierarchyTree").fancytree("getTree");
				node = fancyTreeInstance.getNodeByKey(''+result.item.parent_id);
				refNode = node.addChildren({
					title: result.item.title,
					data: result.item,
					isNew: true
				});
			} else {
				alert(result.message);
			}
		}
	});
	form.trigger('reset');
//add sibling
//refNode = node.getParent().addChildren({
//	title: "New node",
//	isNew: true
//}, node.getNextSibling());
	dialog.dialog("close");
}

function setPosition(treeNodeKey,direction){
	fancyTreeInstance = $("#menuHierarchyTree").fancytree("getTree");
	itemNode = fancyTreeInstance.getNodeByKey(''+treeNodeKey);
	
	if(direction == 'up'){
		newPosition = itemNode.getIndex(); //index is 0 based
	} else if(direction == 'down'){
		newPosition = itemNode.getIndex()+2; //index is 0 based
	} else {
		alert('invalid direction value');
		return;
	}
	
	jQuery.ajax({
		url: ajaxUpdateSiblingsPositionUrl,
		data : {
			hierarchyItemId : treeNodeKey,
			newPosition : newPosition,
		},
		dataType: 'json',
		success: function(result){
			if(result.result == 'success'){
				$('#moveDownLink'+itemNode.key).removeClass('invisible');
				$('#moveUpLink'+itemNode.key).removeClass('invisible');
				
				if(newPosition < itemNode.data.position){
					//move item up and set visibility of arrow icons of sibling
					newLowerItem = itemNode.getPrevSibling();
					//Reset all values of visibility for icons
					$('#moveDownLink'+newLowerItem.key).removeClass('invisible');
					$('#moveUpLink'+newLowerItem.key).removeClass('invisible');
					
					itemNode.moveTo(newLowerItem,"before");
					//update attached data as well
					itemNode.data.position = Number(itemNode.data.position)-1;
					newLowerItem.data.position = Number(itemNode.data.position)+1;
					
					if(newLowerItem.getNextSibling() == null){
						$('#moveDownLink'+newLowerItem.key).addClass('invisible');
					}
				} else {
					//move item down and set visibility of arrow icons of sibling
					newPreviousItem = itemNode.getNextSibling();
					//Reset all values of visibility for icons
					$('#moveDownLink'+newPreviousItem.key).removeClass('invisible');
					$('#moveUpLink'+newPreviousItem.key).removeClass('invisible');
					
					itemNode.moveTo(newPreviousItem,"after");
					//update attached data as well
					itemNode.data.position = Number(itemNode.data.position)+1;
					newPreviousItem.data.position = Number(itemNode.data.position)-1;
					
					//newPreviousItem = fancyTreeInstance.getNodeByKey(newPreviousItem.key);
					if(newPreviousItem.getPrevSibling() == null){
						$('#moveUpLink'+newPreviousItem.key).addClass(' invisible');
					}
				}
				//update item nodes icons
				itemNode = fancyTreeInstance.getNodeByKey(''+treeNodeKey); //reload node with updates values
				if(itemNode.getPrevSibling() == null) $('#moveUpLink'+treeNodeKey).addClass('invisible');
				if(itemNode.getNextSibling() == null) $('#moveDownLink'+treeNodeKey).addClass('invisible');
			} else {
				alert(result.message);
			}
		}
	});
}


function updateHierarchyItemDisplayState(itemId,newState){
	//alert('updating item display state of item '+itemId+' to '+newState);
	if(isNaN(itemId)){
		alert('number required as first parameter of updateHierarchyItemDisplayState(...)');
	}
	
	jQuery.ajax({
		url: ajaxUpdateDisplayStateUrl,
		data : {
			hierarchyItemId : itemId,
			displayState : newState,
		},
		dataType: 'json',
		success: function(result){
			if(result.result == 'success'){
				$('#adminSetVisibleLink'+itemId).removeClass('icon-inactive');
				$('#adminSetHiddenLink'+itemId).removeClass('icon-inactive');
				$('#adminSetUnpublishedLink'+itemId).removeClass('icon-inactive');
				
				if(newState == displayStatusVisible){
					$('#adminSetHiddenLink'+itemId).addClass('icon-inactive');
					$('#adminSetUnpublishedLink'+itemId).addClass('icon-inactive');
				} else if(newState == displayStatusHidden){
					$('#adminSetVisibleLink'+itemId).addClass('icon-inactive');
					$('#adminSetUnpublishedLink'+itemId).addClass('icon-inactive');
				} else if(newState == displayStatusUnpublished){
					$('#adminSetVisibleLink'+itemId).addClass('icon-inactive');
					$('#adminSetHiddenLink'+itemId).addClass('icon-inactive');
				}
			} else {
				alert(result.message);
			}
		}
	});
}

$(function(){

  $("#menuHierarchyTree").fancytree({
    checkbox: false,
    titlesTabbable: true,     // Add all node titles to TAB chain
    quicksearch: true,
	source: {
		url: jsonTreeSourceUrl
	},

    extensions: ["table", "gridnav", "dnd"], //"childcounter" using childcounter leads to the problem, that on each click of a child, the parent is re-rendered, so the dynamic setings done with jequery are reset (e.g. the admin icons for visibilty are being reset to original state) so we cannot use this extension :-(
    table: {
		indentation: 20,
		nodeColumnIdx: 0,
		//checkboxColumnIdx: 0
    },
    gridnav: {
		autofocusInput: false,
		handleCursorKeys: true
    },
	
	dnd: {
		preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
		preventRecursiveMoves: true, // Prevent dropping nodes on own descendants
		autoExpandMS: 400,
		draggable: {
			//zIndex: 1000,
			// appendTo: "body",
			// helper: "clone",
			scroll: false,
			revert: "invalid"
		},
		dragStart: function(node, data) {
			//if( data.originalEvent.shiftKey ){
			//	console.log("dragStart with SHIFT");
			//}
			// allow dragging `node` unless key is 0 (root node):
			if(node.key == 0) return false;
			return true;
		},
		dragEnter: function(node, data) {
			//node = drop/drag target
			//data = the node that is beeing dragged

			//do not allow anything to be dragged before or after root, but only below
		   if(node.key == 0){
				return ["over"];
		   }
		   return true;
		},
		dragDrop: function(targetNode, data) {
			hierarchyItemId = data.otherNode.key;
			if(data.hitMode == "over")
				newParentHierarhcyItemId = targetNode.key;
			else 
				newParentHierarhcyItemId = targetNode.parent.key;

			data.otherNode.moveTo(targetNode, data.hitMode);
			newPosition = data.otherNode.getIndex()+1;
			jQuery.ajax({
				url: ajaxUpdateParentAndPositionUrl,
				data : {
					hierarchyItemId : hierarchyItemId,
					newPosition : newPosition,
					newParentHierarchyItemId: newParentHierarhcyItemId,
				},
				dataType: 'json',
				success: function(result){
					if(result.result == 'success'){

					} else {
						alert(result.message);
					}
				}
			});
		}
	},

    renderColumns: function(event, data) {
		var node = data.node,
		$select = $("<select />"),
		$tdList = $(node.tr).find(">td");
		$trList = $(node.tr);
	
		if(data.node.data.isFallbackLanguage){
			$trList.addClass('fallbackLanguage warning');
		}

		//column 0 is handled by table setting nodeColumnIdx: 0 to display nodes
		//$tdList.eq(1).html("<input type='input' value='" + node.key + "'>");
		
		contentTypeHtml = '<span class="glyphicon glyphicon-question-sign" title="no content type set yet"></span>';
		if(data.node.data.content_id != null){
			contentTypeHtml = '<span class="glyphicon glyphicon-font" title="content page"></span>';
		} else if(data.node.data.document_id != null){
			contentTypeHtml = '<span class="glyphicon glyphicon-folder-open" title="document"></span>';
		} else if(data.node.data.direct_url != null){
			contentTypeHtml = '<span class="glyphicon glyphicon-share-alt" title="link"></span>';
		}
		$tdList.eq(1).html(contentTypeHtml);
		
		var htmlAvailLang = '<span class="flag flag_'+data.node.data.languageCode+'"><a href="'+editMenuLink.replace('_ID_',data.node.data.menu_id)+'" title="'+translation_edit_language_version+data.node.data.languageCode+'"><span class="languageText">'+data.node.data.languageCode+'</span></a></span>';
		if(data.node.data.isFallbackLanguage){
			htmlAvailLang += '<span class="glyphicon glyphicon-warning-sign" style="font-style:normal;color:red !important;" title="'+translation_displaying_fallback_language+'"></span>';
		}
		$tdList.eq(2).html(htmlAvailLang);

		var languagesHtml = '';
		$.each(data.node.data.allLanguagesWithMarker, function( index, details ) {
			if(!details.available){
				languagesHtml += '<span class="flag flag_'+details.code+'"><a href="'+createMenuLink.replace('_LANGUAGE_ID_',details.language_id).replace('_HIERARCHY_ITEM_ID_',data.node.data.id)+'" title="'+translation_create_new_for_lang+details.code+'"><span class="lang_not_available_overlay"><span class="languageText">'+details.code+'</span></span></a></span>';
			} else {
				languagesHtml += '<span class="flag flag_'+details.code+'"><a href="'+editMenuLink.replace('_ID_',details.menu_item_id)+'" title="'+translation_edit_language_version+details.code+'"><span class="languageText">'+details.code+'</span></a></span>';
			}
		});
		$tdList.eq(3).html(languagesHtml);
	  
		//assemble status details for object
		var statusHtml = '<span class="cms-admin-menu-icons" id="adminMenu'+data.node.data.id+'">'+
			'<a id="adminSetVisibleLink'+data.node.data.id+'" 		onclick="updateHierarchyItemDisplayState('+data.node.data.id+',displayStatusVisible); return false;" 		class="'+ ((data.node.data.displayState == displayStatusVisible)? '' : 'icon-inactive') +' glyphicon glyphicon-eye-open" 		title="display-state: visible"></a>'+
			'<a id="adminSetHiddenLink'+data.node.data.id+'" 		onclick="updateHierarchyItemDisplayState('+data.node.data.id+',displayStatusHidden);; return false;" 		class="'+ ((data.node.data.displayState == displayStatusHidden)? '' : 'icon-inactive') +' glyphicon glyphicon-search" 			title="display-state: hidden in navigation only (visible e.g. in search results)"></a>'+
			'<a id="adminSetUnpublishedLink'+data.node.data.id+'" 	onclick="updateHierarchyItemDisplayState('+data.node.data.id+',displayStatusUnpublished);; return false;" 	class="'+ ((data.node.data.displayState == displayStatusUnpublished)? '' : 'icon-inactive') +' glyphicon glyphicon-eye-close" 	title="display-state: unpublished" id="adminSetUnpublishedLink'+data.node.data.id+'"></a>';
		
		//move item up (reduce position value by 1)
		if(data.node.data.id != 0 && !data.node.data.firstSibling && !(data.node.data.lastSibling && data.node.data.firstSibling)){
			hiddenClass = ''
		} else {
			hiddenClass = 'invisible'
		}
		statusHtml += '<a id="moveUpLink'+data.node.key+'" onclick="setPosition( '+data.node.key +' , \'up\')" class="glyphicon glyphicon-arrow-up '+hiddenClass+'" title="move item up"></a>';

		//move item down (increase position value by 1)
		if(data.node.data.id != 0 && !data.node.data.lastSibling && !(data.node.data.lastSibling && data.node.data.firstSibling)){
			hiddenClass = ''
		} else {
			hiddenClass = 'invisible'
		}
		statusHtml += '<a id="moveDownLink'+data.node.key+'" onclick="setPosition( '+data.node.key +' , \'down\' )" class="glyphicon glyphicon-arrow-down '+hiddenClass+'" title="move item down"></a>';
		statusHtml += '</span>'
		statusHtml += 'Hierarchy Item ID = '+data.node.key;

		$tdList.eq(4).html(statusHtml);
	  
	  //console.log(data.node.data);
    }
  }).on("nodeCommand", function(event, data){
    // Custom event handler that is triggered by keydown-handler and
    // context menu:
    var refNode, moveMode,
	tree = $(this).fancytree("getTree"),
	node = tree.getActiveNode();

    switch( data.cmd ) {
		case "addChild":
			//node.editCreateNode("child", "New node");
			fillNewMenuFormAnd(node,(node.children == null)? 1 : node.children.length+1);
			break;
		case "addSibling":
			//node.editCreateNode("after", "New node");
			fillNewMenuFormAnd(node,node.getIndex()+2);
			break;
		default:
			alert("Unhandled command: " + data.cmd);
			return;
    }

  }).on("keydown", function(e){
    var c = String.fromCharCode(e.which), cmd = null;

    if( e.which === $.ui.keyCode.DOWN && e.ctrlKey ) {
      cmd = "addChild";
    } else if( e.which === $.ui.keyCode.SPACE && e.ctrlKey ) {
      cmd = "addSibling";
    } else if( e.which === $.ui.keyCode.DELETE ) {
      cmd = "remove";
    } else if( e.which === $.ui.keyCode.F2 ) {
      cmd = "rename";
    }
    if( cmd ){
      $(this).trigger("nodeCommand", {cmd: cmd});
      return false;
    }
  });
  
  
  $("#menuHierarchyTree").contextmenu({
      delegate: "span.fancytree-title",
//      menu: "#options",
      menu: [
          {title: "new page below", cmd: "addChild", uiIcon: "ui-icon-pencil", disabled: false},
          {title: "new page after", cmd: "addSibling", uiIcon: "ui-icon-pencil", disabled: false},
          //{title: "----"},
          {title: "set visibilty to...", children: [
			{title: "visible", cmd: "display_state_visible", uiIcon: "ui-icon-unlocked", },
            {title: "hidden&nbsp;(still&nbsp;searchable)", cmd: "display_state_hidden", uiIcon: "ui-icon-search", },
            {title: "deactivated", cmd: "display_state_deactivated", uiIcon: "ui-icon-locked", }
            ]}
          ],
      beforeOpen: function(event, ui) {
        var node = $.ui.fancytree.getNode(ui.target);
        node.setActive();
      },
      select: function(event, ui) {
        var node = $.ui.fancytree.getNode(ui.target);
		if(ui.cmd){
			console.log(node.data.id);
			if(ui.cmd == 'display_state_visible'){
				updateHierarchyItemDisplayState(Number(node.data.id),displayStatusVisible);
			} else if(ui.cmd == 'display_state_hidden'){
				updateHierarchyItemDisplayState(Number(node.data.id),displayStatusHidden);
			} else if(ui.cmd == 'display_state_deactivated'){
				updateHierarchyItemDisplayState(Number(node.data.id),displayStatusUnpublished);
			} else if(ui.cmd == 'addChild'){
				$(this).trigger("nodeCommand", {cmd: ui.cmd});
			} else if(ui.cmd == 'addSibling'){
				$(this).trigger("nodeCommand", {cmd: ui.cmd});
			}
		}
      }
    });
});
JS;

$this->registerJs ( $pageTreeScript, View::POS_END, 'cmsTreeViewScriptAdmin' );
?>