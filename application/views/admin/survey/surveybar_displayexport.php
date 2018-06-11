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

    $aExportItemsArray = [
        'surveyexports' => [
            "title" => gT('Survey exports'),
            "groupItems" => [
                "surveystructure" => [
                    "key" => "surveystructure",
                    "description" => "".gT("Survey structure (.lss)"),
                    "detailpage" => "<p>Export only the structure of your survey.</p>"
                                    ."<p>By exporting only the structure, you may pass your survey to a fellow coworker on another instance, or just save it to reuse it later. Also all LimeSurvey Versions are compatible to this kind of export.</p>",
                    "href" => $this->createUrl("admin/export/sa/survey/action/exportstructurexml/surveyid/$oSurvey->sid"),
                    "download" => true
                ],
                "surveyarchive" => ($respstatsread && $surveyexport) ? (
                    ($oSurvey->isActive) ? 
                     [
                            "key" => "surveyarchive",
                            "description" => "".gT("Survey archive (.lsa)"),
                            "detailpage" => "<p>Export the structure of your survey together with all collected responses.</p>"
                                            ."<p>The archive contains as well the structure of your survey, as also the collected responses. If you import this into another LimeSurvey instance you will be able to work on the same statistics as in this instance.</p>",
                            "href" => $this->createUrl("admin/export/sa/survey/action/exportarchive/surveyid/$oSurvey->sid"),
                            "download" => true                              
                        ]
                    : [
                        "key" => "surveyarchive",
                        "description" => "".gT("Survey archive (.lsa)"),
                        "detailpage" => "<p>This is only available in an activated survey.</p>",
                        "href" => '#',
                        "download" => false
                    ]
                    ) : null
            ]
        ],
        'quexmlexports' => [
            "title" => gT('queXML exports'),
            "groupItems" => [
                "quexml" => [
                    "key" => "quexml",
                    "description" => "".gT("queXML format (*.xml)"),
                    "detailpage" => "<p>Export the survey in the queXML format.</p>"
                                    ."<p>To get to know more about queXML check this page: <a href=\"https://quexml.acspri.org.au/\" target=\"_blank\">quexml.acspri.org.au<i class=\"fa fa-link-external\"></i></a>.</p>",
                    "href" => $this->createUrl("admin/export/sa/survey/action/exportstructurequexml/surveyid/".$oSurvey->sid),
                    "download" => true                            
                ],
                "quexmlpdf" => [
                    "key" => "quexmlpdf",
                    "description" => "".gT("queXML PDF export"),
                    "detailpage" => "<p>Export the survey in the queXML format as a pdf.</p>"
                                    ."<p>To get to know more about queXML check this page: <a href=\"https://quexml.acspri.org.au/\" target=\"_blank\">quexml.acspri.org.au<i class=\"fa fa-link-external\"></i></a>.</p>",
                    "href" => $this->createUrl("admin/export/sa/quexml/surveyid/".$oSurvey->sid),
                    "download" => false
                ]
            ]
        ]
    ];

    $oExportSelector = $this->beginWidget('ext.admin.PreviewModalWidget.PreviewModalWidget', array(
        'widgetsJsName' => "exportTypeSelector",
        'renderType' =>  "group-modal",
        'selectButton' => gT("Export"),
        'modalTitle' => gT("Display/Export"),
        'currentSelected' => gT("Display/Export"),
        'groupTitleKey' => "title",
        'groupItemsKey' => "groupItems",
        'debugKeyCheck' => "Export Type ",
        'previewWindowTitle' => "Type of Export",
        'groupStructureArray' => $aExportItemsArray,
        'value' => '',
        'debug' => YII_DEBUG,
        'optionArray' => [
            'onUpdate' => [
                'value',
                "
                var itemData = $('[data-key='+value+']').data('item-value');
                var loadUrl = itemData.itemArray.href;
                if(itemData.itemArray.download == true) {
                    window.location.href=loadUrl;
                } else {
                    $(document).trigger('pjax:load', {url: loadUrl});
                }
                $('#selector__exportTypeSelector--buttonText').html('".gT("Display/Export")."');
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

        <!-- Survey printable version  -->
        <li>
          <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportprintables/surveyid/$oSurvey->sid"); ?>' >
              <span class="icon-export" ></span>
              <?php eT("Printable survey (*.html)"); ?>
          </a>
        </li>

        <?php if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'read')): ?>
            <?php if ($onelanguage):?>
                <!-- Printable version -->
                <li>
                    <a target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/sa/index/surveyid/$oSurvey->sid"); ?>' >
                        <span class="fa fa-print"></span>
                        <?php eT("Printable survey"); ?>
                    </a>
                </li>
            <?php else: ?>
                <li role="separator" class="divider"></li>
                <!-- Printable version multilangue -->
                <li class="dropdown-header"><?php eT("Printable version"); ?></li>
                <?php foreach ($oSurvey->allLanguages as $tmp_lang): ?>
                    <li>
                        <a accesskey='d' target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/sa/index/surveyid/$oSurvey->sid/lang/$tmp_lang"); ?>'>
                            <span class="fa fa-print"></span>
                            <?php echo getLanguageNameFromCode($tmp_lang, false); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</div>
*/ ?>