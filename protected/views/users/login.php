<div class="row" style="">
    <div class="col-md-6 col-md-offset-3" style="margin-top: 50px;">
        <?php
            /** @var $this CController */
            /**
             * @var $loginForms []
             * @var $id string
             * @var $config []
             */
        foreach ($loginForms as $id => $config) {
                $settings = $config['settings'];
                $settings['_logintype'] = [
                    'type' => 'hidden',
                    'current' => $id
                ];
                $tabs[] = [
                    'active' => !isset($tabs),
                    'label' => $config['label'],
                    'content' => $this->widget(SettingsWidget::class, [
                        'settings' => $settings,
                        'buttons' => [
                            gT('Log in') => [
                                'type' => 'submit',
                                'class' => ['btn-primary']
                            ]
                        ]
                    ], true)
                ];
            }
            if (count($tabs) > 1) {
                $this->widget(TbTabs::class, [
                    'type' => TbHtml::NAV_TYPE_PILLS,
                    'tabs' => $tabs
                ]);
            } else {
                echo $tabs[0]['content'];
            }
            
        ?>
    </div>
</div>
