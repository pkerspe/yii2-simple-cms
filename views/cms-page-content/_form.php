<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\pn_cms\models\CmsPageContent */
/* @var $form ActiveForm */
?>
<div class="form">

    <?php if (!isset($embededForm)) $form = ActiveForm::begin(); ?>

    <?php if (!isset($embededForm)) { ?>
        <?= $form->field($model, 'language') ?>
        <?= $form->field($model, 'createdby_userid') ?>
        <?= $form->field($model, 'created_datetime') ?>
        <?= $form->field($model, 'modification_userid') ?>
        <?= $form->field($model, 'modification_datetime') ?>
    <?php } else { ?>
        <span class="pull-right"><?= $model->getAttributeLabel('createdby_userid') . ': ' . $model->createdby_userid ?>
            <br/>
            <?= $model->getAttributeLabel('created_datetime') . ': ' . $model->created_datetime ?></span>
        <div><?= $model->getAttributeLabel('modification_userid') . ': ' . $model->modification_userid ?></div>
        <div><?= $model->getAttributeLabel('modification_datetime') . ': ' . $model->modification_datetime ?></div>
    <?php } ?>
    <?= $form->field($model, 'content')->textarea(['class' => 'ckeditor']) ?>
    <?= $form->field($model, 'render_subpage_teasers')->checkbox() ?>

    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" href="#collapseTeaserDetails">Page Teaser settings (click to open)</a>
                </h4>
            </div>
            <div id="collapseTeaserDetails" class="panel-collapse collapse">
                <div class="panel-body">
                    <?= $form->field($model, 'teaser_name') ?>
                    <?= $form->field($model, 'teaser_text')->textarea(['maxlength' => 500]) ?>
                    <!-- TODO: render a  image selector button to open the media browser and display preview of current selected image if any -->
                    <?= $form->field($model, 'teaser_image_id') ?>
                    <?= $form->field($model, 'teaser_link') ?>
                </div>
                <div class="panel-footer">In this section you can specify the teaser details to be used whenever the page content should be teasered on another page. E.g. you can specify the name and text excerpt to be displayed if this page is displayed in the subpage teaser list of its parent page.</div>
            </div>
        </div>
    </div>

    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" href="#collapseMetaTags">Meta Tags for this page (click to open)</a>
                </h4>
            </div>
            <div id="collapseMetaTags" class="panel-collapse collapse">
                <div class="panel-body">
                    <?= $form->field($model, 'description')->textarea(['maxlength' => 500]) ?>
                    <?= $form->field($model, 'html_title') ?>
                    <?= $form->field($model, 'meta_keywords')->textarea(['maxlength' => 255]) ?>
                    <?= $form->field($model, 'metatags_general')->textarea(['maxlength' => 500]) ?>
                </div>
                <div class="panel-footer">Edit meta tags that are rendered in the html header in this area. All this tags are optional but can be used for search engine optimization</div>
            </div>
        </div>
    </div>

    <div class="panel-group">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" href="#collapseAdvanced">Advanced settings (click to open)</a>
                </h4>
            </div>
            <div id="collapseAdvanced" class="panel-collapse collapse">
                <div class="panel-body">
                    <?= $form->field($model, 'javascript')->textarea() ?>
                    <?= $form->field($model, 'css')->textarea() ?>
                </div>
                <div class="panel-footer">This section allows you to provide custom, page specific javascript and css code that will be rendered in the page</div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php if (!isset($embededForm)) ActiveForm::end(); ?>

</div>
<!-- _form -->
