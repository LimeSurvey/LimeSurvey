<div class="row">
    <?php
        echo TbHtml::tag('h1', [], "Group {$group->displayLabel}");
    ?>
    <div class="col-md-12">
        <?php
        // This is an update view so we use PUT.
        // We specify layout per tab.
        echo TbHtml::beginForm(['groups/update', 'id' => $group->primaryKey], 'put', []);

        $this->widget('TbTabs', [
            'tabs' => [
                [
                    'label' => 'Texts',
                    'content' => $this->renderPartial('update/texts', ['group' => $group], true),
                    'active' => true
                ], [
                    'label' => gT('Routing'),
                    'content' => $this->renderPartial('update/routing', ['group' => $group], true),
                ],
            ]
        ]);
        echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
        echo TbHtml::submitButton('Save settings', [
            'color' => 'primary',
//            'class' => 'ajaxSubmit'
        ]);
        echo TbHtml::closeTag('div');
        echo TbHtml::endForm();
        ?>
    </div>


</div>