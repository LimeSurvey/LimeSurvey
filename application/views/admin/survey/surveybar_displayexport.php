<?php
/**
 * Subview of surveybar_view.
 * @param $respstatsread
 * @param $surveyexport
 * @param $oSurvey
 * @param $onelanguage
 */

?>

<?php

    $aExportItemsArray = [];

    $aExportItemsArray["surveystructure"] = [
        "key" => "surveystructure",
        "description" => "".gT("Survey structure (.lss)"),
        "detailpage" => "<p>Export only the structure of your survey.</p>"
                        ."<p>By exporting only the structure, you may pass your survey to a fellow coworker on another instance, or just save it to reuse it later. Also all LimeSurvey Versions are compatible to this kind of export.</p>",
        "href" => $this->createUrl("admin/export/sa/survey/action/exportstructurexml/surveyid/".$oSurvey->sid),
        "download" => true
    ];
    if(($respstatsread && $surveyexport)) {
        $aExportItemsArray["surveyarchive"] = ($oSurvey->isActive) 
        ? [
            "key" => "surveyarchive",
            "description" => "".gT("Survey archive (.lsa)"),
            "detailpage" => "<p>Export the structure of your survey together with all collected responses.</p>"
                            ."<p>The archive contains as well the structure of your survey, as also the collected responses. If you import this into another LimeSurvey instance you will be able to work on the same statistics as in this instance.</p>",
            "href" => $this->createUrl("admin/export/sa/survey/action/exportarchive/surveyid/".$oSurvey->sid),
            "download" => true                              
        ]
        : [
            "key" => "surveyarchive",
            "description" => "".gT("Survey archive (.lsa)"),
            "detailpage" => "<p>This is only available in an activated survey.</p>",
            "href" => '#',
            "htmlclasses" => 'disabled',
            "extraAttributes" => 'disabled="disabled" onclick="return false;"',
            "download" => false,
            "deactivated" => true
        ];
    }

    $aExportItemsArray["quexml"] = [
        "key" => "quexml",
        "description" => "".gT("queXML format (*.xml)"),
        "detailpage" => "<p>Export the survey in the queXML format.</p>"
                        ."<p>To get to know more about queXML check this page: <a href=\"https://quexml.acspri.org.au/\" target=\"_blank\">quexml.acspri.org.au<i class=\"fa fa-link-external\"></i></a>.</p>",
        "href" => $this->createUrl("admin/export/sa/survey/action/exportstructurequexml/surveyid/".$oSurvey->sid),
        "download" => true                            
    ];

    $aExportItemsArray["quexmlpdf"] =  [
        "key" => "quexmlpdf",
        "description" => "".gT("queXML PDF export"),
        "detailpage" => "<p>Export the survey in the queXML format as a pdf.</p>"
                        ."<p>To get to know more about queXML check this page: <a href=\"https://quexml.acspri.org.au/\" target=\"_blank\">quexml.acspri.org.au<i class=\"fa fa-link-external\"></i></a>.</p>",
        "href" => $this->createUrl("admin/export/sa/quexml/surveyid/".$oSurvey->sid),
        "download" => false
    ];

    $aExportItemsArray["tabseperated"] =  [
        "key" => "tabseperated",
        "description" => "".gT("Tab-separated-values format (*.txt)"),
        "detailpage" => "<p>Export the survey structure in a tab-seperated value format.</p>
                        .<p>This format is recognized by LimeSurvey and some third-party systems. We would recommend to export the survey as .lss to transfer it to another LimeSurvey instance.</p>",
        "href" => $this->createUrl("admin/export/sa/survey/action/exportstructuretsv/surveyid/".$oSurvey->sid),
        "download" => true
    ];

    $aExportItemsArray["printablesurveyhtml"] = [
        "key" => "printablesurveyhtml",
        "description" => gT("Printable survey (*.html)"),
        "detailpage" => "<p>Download the survey as a HTML-file to be printed out, or to be shown on a seperate device.</p>",
        "href" => $this->createUrl("admin/export/sa/survey/action/exportprintables/surveyid/".$oSurvey->sid),
        "download" => true,
        "external" => true
    ];
    if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'read')) {
        if ($onelanguage) {
            $aExportItemsArray["printablesurvey"] = [
                "key" => "printablesurvey",
                "description" => gT("Printable survey"),
                "detailpage" => "<p>Open the survey to be printed out, or to be shown on a seperate device.</p>",
                "href" => $this->createUrl("admin/printablesurvey/sa/index/surveyid/$oSurvey->sid"),
                "download" => false,
                "external" => true
            ];
        } else {
            $aExportItemsArray["spacer"] = [
                "key" => "spacer",
                "description" => '---',
                "detailpage" => "",
                "href" => '#',
                "htmlclasses" => 'disabled',
                "extraAttributes" => 'disabled="disabled" onclick="return false;"',            
                "download" => false,
                "deactivated" => true
            ];

            foreach ($oSurvey->allLanguages as $tmp_lang) {
                $aExportItemsArray["printablesurvey_".$tmp_lang] = [
                    "key" => "printablesurvey_".$tmp_lang,
                    "description" => gT("Printable survey").' ('.getLanguageNameFromCode($tmp_lang, false).')',
                    "detailpage" => "<p>Open the survey to be printed out, or to be shown on a seperate device.</p>"
                                    ."<p>The opened survey will be in ".getLanguageNameFromCode($tmp_lang, false)."</p>",
                    "href" => $this->createUrl("admin/printablesurvey/sa/index/surveyid/".$oSurvey->sid."/lang/".$tmp_lang),
                    "download" => false,
                    "external" => true
                ];
            }
        }
    }


    $oExportSelector = $this->beginWidget('ext.admin.PreviewModalWidget.PreviewModalWidget', array(
        'widgetsJsName' => "exportTypeSelector",
        'renderType' =>  "modal",
        'selectButton' => gT("Export"),
        'modalTitle' => gT("Display/Export"),
        'currentSelected' => gT("Display/Export"),
        'debugKeyCheck' => "Export Type ",
        'previewWindowTitle' => "Type of Export",
        'itemsArray' => $aExportItemsArray,
        'value' => '',
        'debug' => YII_DEBUG,
        'optionArray' => [
            'onModalClose' => [
                "
                $('#selector__exportTypeSelector--buttonText').html('".gT("Display/Export")."');
                "
            ],
            'onUpdate' => [
                'value',
                "
                var itemData = $('[data-key='+value+']').data('item-value');
                var loadUrl = itemData.itemArray.href;
                if(itemData.itemArray.external == true) {
                    window.open(loadUrl, '_blank');
                } else {
                    if(itemData.itemArray.download == true) {
                        window.location.href=loadUrl;
                    } else {
                        $(document).trigger('pjax:load', {url: loadUrl});
                    }
                }
                $('#selector__exportTypeSelector--buttonText').html('".gT("Display/Export")."');
                "
            ],
            "onGetDetails" => [
                "curDetailPage",
                "itemData",
                "if(itemData.itemArray.deactivated == true){
                    return '';
                }
                return curDetailPage;
                "
            ]
        ]
    ));
?>
<div class="btn-group hidden-xs">
    <?=$oExportSelector->getModal(); ?>
    <?=$oExportSelector->getButtonOrSelect(); ?>
</div>
<?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
<?php /*
    <!-- Main dropdown -->
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="icon-display_export" ></span>
      <?php eT("Display / Export"); ?> <span class="caret"></span>
    </button>

    <!-- dropdown -->
    <ul class="dropdown-menu">

        <!-- Export -->
        <li class="dropdown-header"> <?php eT("Export..."); ?></li>

        <!-- Survey structure -->
        <li>
            <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportstructurexml/surveyid/$oSurvey->sid"); ?>' >
                <span class="icon-export" ></span>
                <?php eT("Survey structure (.lss)"); ?>
            </a>
        </li>

        <?php if ($respstatsread && $surveyexport): ?>
            <?php if ($oSurvey->isActive):?>

                <!-- Survey archive -->
                <li>
                    <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportarchive/surveyid/$oSurvey->sid"); ?>' >
                        <span class="icon-export" ></span>
                        <?php eT("Survey archive (.lsa)"); ?>
                    </a>
                </li>
            <?php else: ?>
                <!-- Survey archive unactivated -->
                <li>
                    <a href="#" onclick="alert('<?php eT("You can only archive active surveys.", "js"); ?>');" >
                        <span class="icon-export" ></span>
                        <?php eT("Survey archive (.lsa)"); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <!-- queXML -->
        <li>
          <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportstructurequexml/surveyid/$oSurvey->sid"); ?>' >
              <span class="icon-export" ></span>
              <?php eT("queXML format (*.xml)"); ?>
          </a>
        </li>

        <!-- queXMLPDF -->
        <li>
          <a href='<?php echo $this->createUrl("admin/export/sa/quexml/surveyid/$oSurvey->sid"); ?>' >
              <span class="icon-export" ></span>
              <?php eT("queXML PDF export"); ?>
          </a>
        </li>


        <!-- Tab-separated-values -->
        <li>
          <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportstructuretsv/surveyid/$oSurvey->sid"); ?>' >
              <span class="icon-export" ></span>
              <?php eT("Tab-separated-values format (*.txt)"); ?>
          </a>
        </li>

    </ul>
</div>
*/ ?>