<?php

    /**
     * @var bool            $canImport
     * @var string          $importErrorMessage
     * @var string          $importModal
     * @var string          $importTemplate
     * @var string          $themeType
     */
if (Permission::model()->hasGlobalPermission('templates', 'import')) {
     $this->renderPartial(
         './import_modal',
         [
            'importModal' => $importModal,
            'importTemplate' => $importTemplate,
            'themeType' => $themeType
         ]
     );
}
