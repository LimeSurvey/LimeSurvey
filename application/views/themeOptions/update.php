<?php
/* @var ThemeOptionsController $this */
/* @var TemplateConfiguration $model */
/* @var array $aOptionAttributes */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyTemplateOptionsUpdate');

?>
<?php if (empty($model->sid)) : ?>
<div class="">
<?php else : ?>
    <div class="col-12 side-body ls-settings-wrapper" id="theme-option-sidebody">
<?php endif; ?>

    <!-- Using bootstrap tabs to differ between just hte options and advanced direct settings -->
    <div class="row">
        <div class="col-12">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs" id="theme-options-tabs" role="tablist">
                <?php if ($aOptionAttributes['optionsPage'] === 'core') : ?>
                    <?php foreach ($aOptionAttributes['categories'] as $key => $category) : ?>
                        <li role="presentation" class="nav-item">
                            <button class="nav-link <?php echo $key == 0 ? 'active' : 'tab_action_hide_on_inherit'; ?>" data-bs-target="#category-<?php echo $key; ?>"
                                    aria-controls="category-<?php echo $key; ?>" role="tab" data-bs-toggle="tab" aria-selected="<?php echo $key == 0 ? 'true' : 'false'; ?>">
                                <?php et($category); ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li role="presentation" class="nav-item">
                        <button class="nav-link active" data-bs-target="#simple" aria-controls="home" role="tab" data-bs-toggle="tab" aria-selected="true">
                            <?php eT('Simple options') ?>
                        </button>
                    </li>
                <?php endif; ?>
                <li role="presentation" class="nav-item">
                    <button class="nav-link <?php echo Yii::app()->getConfig('debug') > 1 ? '' : 'd-none'; ?>" data-bs-target="#advanced" aria-controls="profile" role="tab" data-bs-toggle="tab" aria-selected="false">
                        <?php eT('Advanced options') ?>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row" id="trigger-save-button">
        <div class="col-12" >
            <?php $form = $this->beginWidget('TbActiveForm', [
                                'id'                   => 'template-options-form',
                                'enableAjaxValidation' => false,
                                'htmlOptions'          => ['class' => 'form action_update_options_string_form'],
                                'action'               => $actionUrl
                            ]
                        ); ?>
                <?php echo TbHtml::submitButton($model->isNewRecord ? gT('Create') : gT('Save'), ['id' => 'theme-options--submit', 'class' => 'd-none action_update_options_string_button']); ?>
            <!-- Tab panes -->
                <div class="tab-content">
            <?php /* Begin theme option form */ ?>
                    <?php
                    /*
                     * Here we render just the options as a simple form.
                     * On save, the options are parsed to a JSON string and put into the relevant field in the "real" form
                     * before saving that to database.
                     */

                    //First convert options to json and check if it is valid
                    $oOptions = json_decode((string) $model->options);
                    $jsonError = json_last_error();
                    //if it is not valid, render message
                    if ($jsonError !== JSON_ERROR_NONE && $model->options !== 'inherit') {
                        //return
                        echo "<div class='ls-flex-column fill'><h4>" . gT('There are no simple options in this survey theme.') . "</h4></div>";
                    } else {
                        //if however there is no error in the parsing of the json string go forth and render the form
                        /*
                         * The form element needs to hold the class "action_update_options_string_form" to be correctly bound
                         * To be able to change the value in the "real" form, the input needs to now what to change.
                         * So the name attribute should contain the object key we want to change
                         */

                        if ($aOptionAttributes['optionsPage'] == 'core') {
                            $this->renderPartial(
                                './options_core',
                                [
                                    'aOptionAttributes'      => $aOptionAttributes,
                                    'aTemplateConfiguration' => $aTemplateConfiguration,
                                    'oParentOptions'         => $oParentOptions,
                                    'sPackagesToLoad'        => $sPackagesToLoad
                                ]
                            );
                        } else {
                            echo '<div role="tabpanel" class="tab-pane active" id="simple">';
                            echo $templateOptionPage;
                            echo '</div>';
                        }
                    }
                    ?>


                <?php echo $form->hiddenField($model, 'template_name'); ?>
                <?php echo $form->hiddenField($model, 'sid'); ?>
                <?php echo $form->hiddenField($model, 'gsid'); ?>
                <?php echo $form->hiddenField($model, 'uid'); ?>

                <?php echo CHtml::hiddenField('optionInheritedValues', json_encode($optionInheritedValues)); ?>
                <?php echo CHtml::hiddenField('optionCssFiles', $optionCssFiles); ?>
                <?php echo CHtml::hiddenField('optionCssFramework', json_encode($optionCssFramework)); ?>
                <?php echo CHtml::hiddenField('translationInheritedValue', gT("Inherited value:") . ' '); ?>

                <?php $this->renderPartial(
                    '/themeOptions/advanced',
                    [
                        'model' => $model,
                        'form' => $form,
                        'optionInheritedValues' => $optionInheritedValues,
                        'optionCssFiles' => $optionCssFiles,
                        'optionCssFramework' => $optionCssFramework
                    ]
                ); ?>
            </div>
            <!-- End form tag -->
             <?php $this->endWidget(); ?>
        </div>
    </div>
<?php $this->renderPartial('/surveyAdministration/_inherit_sub_footer'); ?>

</div>

<!-- Form for image file upload -->
<div class="d-none">
    <?php echo TbHtml::form(['admin/themes/sa/upload'], 'post', ['id' => 'upload_frontend', 'name' => 'upload_frontend', 'enctype' => 'multipart/form-data']); ?>
    <?php if (isset($aTemplateConfiguration['sid']) && !empty($aTemplateConfiguration['sid'])) : ?>
        <input type='hidden' name='surveyid' value='<?= $aTemplateConfiguration['sid'] ?>'/>
    <?php endif; ?>
    <input type='hidden' name='templatename' value='<?php echo $aTemplateConfiguration['template_name']; ?>'/>
    <input type='hidden' name='templateconfig' value='<?php echo $aTemplateConfiguration['id']; ?>'/>
    <input type='hidden' name='action' value='templateuploadimagefile'/>
    <?php echo TbHtml::endForm() ?>
</div>