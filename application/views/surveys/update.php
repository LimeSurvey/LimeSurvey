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
                    'content' => "@todo",

                ], [
                    'label' => gT('Presentation & Navigation'),
                    'content' => "@todo",

                ], [
                    'label' => gT('Notification & data management'),
                    'content' => "@todo",

                ], [
                    'label' => gT('Tokens'),
                    'content' => "@todo",
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