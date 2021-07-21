<?php
    /**
     * @var AdminController $this
     * @var bool            $canImport
     * @var string          $importErrorMessage
     * @var string          $importModal
     * @var string          $importTemplate
     * @var string          $themeType
     */
?>
<?php if(Permission::model()->hasGlobalPermission('templates','import')):?>
    <?php $this->renderPartial('./import_modal',['importModal' => $importModal, 'importTemplate' => $importTemplate, 'themeType' => $themeType]); ?>
<?php endif;?>
