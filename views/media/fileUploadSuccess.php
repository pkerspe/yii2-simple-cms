<?php
use schallschlucker\simplecms\models\MediaBrowserImageUpload;
use schallschlucker\simplecms\models\CmsContentMedia;

/* @VAR $uploadedCmsContentMediaArray array */

foreach($uploadedCmsContentMediaArray as $cmsContentMedia){
	/* @var $cmsContentMedia CmsContentMedia */
?>
<div>
	File Uploaded:
	<div>Name: <?php echo $cmsContentMedia->file_name ?></div>
	<div>Size: <?php echo $cmsContentMedia->filesize_bytes ?></div>
	<div>Media Type: <?php echo $cmsContentMedia->media_type ?></div>
	<div>MimeType: <?php echo $cmsContentMedia->mime_type ?></div>
</div>
<?php 
}
?>
