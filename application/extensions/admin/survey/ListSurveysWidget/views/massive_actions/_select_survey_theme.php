<?php
/** @var AdminController $this */

?>

<?php $form = $this->beginWidget('CActiveForm', array('id'=>'survey-theme',)); ?>

<div id='select-theme-modal' >
    <label class="form-label" for='theme'><?php  eT("Survey theme:"); ?></label>
    <div class="mb-3">
        <select id='theme' class="form-select custom-data"  name='theme' >
            <?php
                $athemeList = Template::getTemplateList();
                foreach ($athemeList as $themeName => $folder) {
                    if (Permission::model()->hasGlobalPermission('themes','read') || Permission::model()->hasTemplatePermission($themeName) ) { ?>
                        <option value='<?php echo $themeName; ?>'>
                            <?php echo $themeName; ?>
                        </option>
                        <?php }
                    }
            ?>
        </select>
    </div>
    <p><?= gT('This will update the survey theme for all selected active surveys.').' '.gT('Continue?'); ?></p>
</div>
<?php $this->endWidget(); ?>
