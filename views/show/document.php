<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $documentModel schallschlucker\simplecms\models\CmsDocument */
$filePath = $documentModel->file_path.DIRECTORY_SEPARATOR.$documentModel->file_name;
$mimeType = $documentModel->mime_type;

switch($mimeType){
	case 'text/plain':
?>
<textarea style="width:100%;">
<?php include($filePath); ?>
</textarea>
<?php 
		break;

	case 'text/html':
	case 'application/xhtml+xml':
		include($filePath);
		break;
		
	case 'image/jpeg':
	case 'image/pjpeg':
	case 'image/gif':
	case 'image/png':
		echo '<img src="'.Url::toRoute(['show/file-data','documentId' => $documentModel->id]).'" title="'.$documentModel->meta_description.'" />';
		break;
		
	default:
?>
<p>could not embed content, due to unsopported mimetype</p>
<p>content Details:</p>
<p>File name: <?= $documentModel->file_name; ?></p>
<p>Mime type: <?= $documentModel->mime_type; ?></p>
<p>Description: <?= $documentModel->meta_description; ?></p>
<p>Keywords: <?= $documentModel->meta_keywords; ?></p>
<?php 
		break;
}
?>
