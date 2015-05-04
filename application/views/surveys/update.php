<div class="row">
    <div class="col-md-12">
        <?php
        $this->widget('TbTabs', [
            'tabs' => [
                [
                    'label' => 'Properties',
                    'content' => $this->renderPartial('view/properties', ['survey' => $survey], true),
                    'active' => true
                ], [
                    'label' => gT('General'),
                    'content' => $this->renderPartial('view/general', ['survey' => $survey], true),
                ], [
                    'label' => gT('Languages'),
                    'content' => $this->renderPartial('view/languages', ['survey' => $survey], true),
                ], [
                    'label' => gT('Presentation & Navigation'),
                    'content' => $this->renderPartial('view/presentation', ['survey' => $survey], true),

                ], [
                    'label' => gT('Notification & data management'),
                    'content' => $this->renderPartial('view/data', ['survey' => $survey], true),

                ], [
                    'label' => gT('Tokens'),
                    'content' => $this->renderPartial('view/tokens', ['survey' => $survey], true),
                    'visible' => $survey->bool_usetokens

                ], [
                    'label' => gT('Panel integration'),
                    'content' => "@todo",

                ], [
                    'label' => gT('Resources'),
                    'content' => "@todo",

                ], [
                    'label' => 'Optional features',
                    'content' => $this->renderPartial('view/features', ['survey' => $survey], true),
//                    'active' => true
                ], [
                    'label' => 'Access control',
                    'content' => $this->renderPartial('view/access', ['survey' => $survey], true),

                ]

            ]
        ]);

        ?>
    </div>


</div>