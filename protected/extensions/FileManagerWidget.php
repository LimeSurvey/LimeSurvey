<?php


class FileManagerWidget extends CWidget{
    public $context;
    public $dialog = false;
    public $key;
    public $callback;
    public $htmlOptions = [];
    public $clientOptions = [
        'ui' => [
            ['toolbar', 'places', 'tree', 'path', 'stat']
        ],
        'uiOptions' => [
            'cwd' => [
                'listView' => [
                    'columns' => ['name', 'perm', 'date', 'size', 'kind'],
                ],
                'oldSchool' => false
            ]
        ]
    ];
    /**
     * All these options are copies from elFinder.options.js.
     *
     */
    public $toolbar = [
        ['back', 'forward'],
        ['netmount'],
        ['reload'], ['home', 'up'],
        ['mkdir', 'mkfile', 'upload'],
        ['open', 'download', 'getfile'],
        ['info', 'chmod'],
        ['quicklook'],
        ['copy', 'cut', 'paste'],
        ['rm'],
        ['duplicate', 'rename', 'edit', 'resize'],
        ['extract', 'archive'],
        ['search'],
        ['view', 'sort'],
        ['help']
    ];

    public $commands = [
        'open', 'reload', 'home', 'up', 'back', 'forward', 'getfile', 'quicklook',
        'download', 'rm', 'duplicate', 'rename', 'mkdir', 'mkfile', 'upload', 'copy',
        'cut', 'paste', 'edit', 'extract', 'archive', 'search', 'info', 'view', 'help',
        'resize', 'sort'
    ];
    public $disabledCommands = [
        'archive',
        'download',
        'quicklook',
        'open',
        'edit'
    ];

    public $contextMenu = [
		// navbarfolder menu
		'navbar' => ['open', '|', 'upload', '|', 'copy', 'cut', 'paste', 'duplicate', '|', 'rm', '|', 'rename', '|', 'places', 'info', 'chmod', 'netunmount'],
		// current directory menu
		'cwd'    => ['reload', 'back', '|', 'upload', 'mkdir', 'mkfile', 'paste', '|', 'sort', '|', 'info'],
		// current directory file menu
		'files' => ['getfile', '|','open', 'quicklook', '|', 'download', 'upload', '|', 'copy', 'cut', 'paste', 'duplicate', '|', 'rm', '|', 'edit', 'rename', 'resize', '|', 'archive', 'extract', '|', 'places', 'info', 'chmod']
	];
    protected function createContextMenu() {
        $result = [];
        foreach($this->contextMenu as $name => $buttonSet) {
            $set = [];
            foreach($buttonSet as $button) {
                if (!in_array($button, $this->disabledCommands)) {
                    $set[] = $button;
                }
            }
            if (!empty($set)) {
                $result[$name] = $set;
            }
        }
        return $result;
    }
    protected function createToolbar() {
        $result = [];
        foreach($this->toolbar as $buttonSet) {
            $set = [];
            foreach($buttonSet as $button) {
                if (!in_array($button, $this->disabledCommands)) {
                    $set[] = $button;
                }
            }
            if (!empty($set)) {
                $result[] = $set;
            }
        }
        return $result;
    }
    /**
     * Executes the widget.
     * This method is called by {@link CBaseController::endWidget}.
     */
    public function run()
    {
        parent::run();
        $clientOptions = \TbArray::merge($this->clientOptions, [
            'url' => App()->createUrl('files/browse', ['context' => $this->context, 'key' => $this->key]),
            'lang' => App()->language,
//            'resizable' => !$dialog,
            'loadTmbs' => 100,
            'notifyDialog' => [
                'position' => null
            ],
            'uiOptions' => [
                'toolbar' => $this->createToolbar()
            ],
            'contextmenu' => $this->createContextMenu(),
            'commands' => array_values(array_diff($this->commands, $this->disabledCommands)),
            'commandsOptions' => [
//                'edit' => [
//                    'editors' => [
//                        [
//                            'mimes' => ['text/html'],
//                            'load' => new CJavaScriptExpression('function(textarea) {
//                                $(textarea).ace({
//                                    "mode" : "html",
//                                    "toolbarCallback" : function createToolbar(element, editor) {
//                                        element.css("background-color", "#F0F0F0");
//                                        element.css("padding", "5px");
//                                        element.css("text-align", "center");
//                                        var action = function(e, elem) { editor.commands.exec($(elem).attr("data-action"), editor); };
//                                        $("<button/>").text("Undo (ctrl + Z)").attr("data-action", "undo").appendTo(element).on("click", action);
//                                        $("<button/>").text("Redo (ctrl + Y)").attr("data-action", "redo").appendTo(element).on("", action);
//                                        $("<button/>").text("Find (ctrl + F)").attr("data-action", "search").appendTo(element).on("click", action);
//                                        $("<button/>").text("Replace (ctrl + H)").attr("data-action", "replace").appendTo(element).on("click", action);
//                                    }
//                                });
//                            }'),
//                            'save' => new CJavaScriptExpression('function(textarea) {
//                                $(document).trigger("saveFile", [textarea]);
//                            }'),
//                            'close' => new CJavaScriptExpression('function(textarea) {
//
//                            }')
//                        ]
//                    ]
//                ]
            ],
//            'handlers' => [
//                'open' => new CJavaScriptExpression('function() { debugger; }')
//            ]
        ]);
        if ($this->dialog) {
            $clientOptions['getFileCallback'] = new \CJavaScriptExpression('function(file) {
            top.tinymce.activeEditor.windowManager.getParams().callback(file.url, {
                alt: file.name,
                width: file.width,
                height: file.height

            });
            top.tinymce.activeEditor.windowManager.close();

        }');
        } elseif (isset($this->callback)) {
            $clientOptions['getFileCallback'] = $this->callback;
        };
        $r = new ReflectionClass(elFinderConnector::class);

        $dir = dirname(dirname($r->getFileName()));
        $cs = App()->clientScript;
        $url = App()->assetManager->publish($dir);
        $cs->registerScriptFile($url . '/js/elFinder.js');
        $cs->registerPackage('jqueryui');
        $cs->registerPackage('jquery-ace');
        $cs->registerCssFile($url . '/jquery/ui-themes/smoothness/jquery-ui-1.10.1.custom.min.css');

        foreach (CFileHelper::findFiles($dir . '/js', [
            'fileTypes' => ['js'],
            'absolutePaths' => false
        ]) as $file) {
            $cs->registerScriptFile($url . '/js/' . $file);
        }

        foreach (CFileHelper::findFiles($dir . '/css', [
            'fileTypes' => ['css'],
            'absolutePaths' => false
        ]) as $file) {
            $cs->registerCssFile($url . '/css/' . $file);
        }


        $htmlOptions = $this->htmlOptions;
        $htmlOptions['id'] = $this->getId();

        $cs->registerScript('init',
            "$('#{$this->getId()}').elfinder(" . \CJavaScript::encode($clientOptions) . ");",
            \CClientScript::POS_READY);
        echo CHtml::tag('div', $htmlOptions, '');
    }


}