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
        "detailpage" => "<p>".gT("This export will dump all the groups, questions, answers and conditions for your survey into a .LSS file"
        ."(which is basically an XML file). This dump file can be used with the 'Import survey' feature when creating a new survey.")."</p>"
        ."<p>".gT("A survey which uses a custom theme will import fine, but the template it refers to will not exist on the new server."
        ." In that case the system will use the global default theme.")."</p>"
        ."<p><b>".gT("Please note: This file does not contain any collected responses.")."</b></p>",
        "href" => $this->createUrl("admin/export/sa/survey/action/exportstructurexml/surveyid/".$oSurvey->sid),
        "download" => true
    ];
    if(($respstatsread && $surveyexport)) {
        $aExportItemsArray["surveyarchive"] = ($oSurvey->isActive) 
        ? [
            "key" => "surveyarchive",
            "description" => "".gT("Survey archive (.lsa)"),
            "detailpage" => "
            <p>".gT("This export is intended to create a complete backup of an active survey for archival purposes.")."</p>
            <p>".gT("It will include the following data in a ZIP file ending with '.lsa'.")."</p>
            <ul>
                <li>".gT("Survey structure")."</li>
                <li>".gT("Response data (Files uploaded in a file upload question have to exported separately)")."</li>
                <li>".gT("Token data (if activated)")."</li>
                <li>".gT("Timings (if activated)")."</li>
            </ul>
            ",
            "href" => $this->createUrl("admin/export/sa/survey/action/exportarchive/surveyid/".$oSurvey->sid),
            "download" => true                              
        ]
        : [
            "key" => "surveyarchive",
            "description" => "".gT("Survey archive - only in active surveys"),
            "detailpage" => "",
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
        "detailpage" => "
        <p>".gT("queXML is an XML description of a questionnaire.")."</p>
        <p>".gT("It is not suitable for backing up a LimeSurvey survey as it cannot export conditions, and isn't able to export all question types.")."</p>
        <p>".gT("Exporting a questionnaire to queXML allows you to create PDF documents that can be printed, filled then scanned and processed using queXF software.")."</p>
        <p>".gT("To get to know more about queXML check this page:")." <a href=\"https://quexml.acspri.org.au/\" target=\"_blank\">quexml.acspri.org.au <i class=\"fa fa-external-link\"></i></a>.</p>",
        "href" => $this->createUrl("admin/export/sa/survey/action/exportstructurequexml/surveyid/".$oSurvey->sid),
        "download" => true                            
    ];

    $aExportItemsArray["quexmlpdf"] =  [
        "key" => "quexmlpdf",
        "description" => "".gT("queXML PDF export"),
        "detailpage" => "
        <p>".gT("queXML is an XML description of a questionnaire.")."</p>
        <p>".gT("On the following page you will be able to create a pdf that can be printed filled out and scanned again.")."</p>
        <p>".gT("It is not suitable for backing up a LimeSurvey survey as it cannot export conditions, and isn't able to export all question types.")."</p>
        <p>".gT("To get to know more about queXML check this page:")." <a href=\"https://quexml.acspri.org.au/\" target=\"_blank\">quexml.acspri.org.au <i class=\"fa fa-external-link\"></i></a>.</p>",
        "href" => $this->createUrl("admin/export/sa/quexml/surveyid/".$oSurvey->sid),
        "download" => false
    ];

    $aExportItemsArray["tabseperated"] =  [
        "key" => "tabseperated",
        "description" => "".gT("Tab-separated-values format (*.txt)"),
        "detailpage" => "
        <p>".gT("This feature is designed to make it easy to use Excel to author and edit surveys.")."</p>
        <p>".gT("It completely eliminates the dependence upon SGQA codes.")."</p>
        <p>".gT("It also makes it easy to do bulk editing of your survey, such as find-replace, bulk-reordering, looping (repeating groups), "
        ."and testing (such as temporarily disabling mandatory or validation criteria).")."</p>
        <p><a href=\"https://manual.limesurvey.org/Excel_Survey_Structure\" target=\"_blank\" >".gT("Check out the dedicated documentation for this format.")." <i class=\"fa fa-external-link\"></i></a></p>
        ",
        "href" => $this->createUrl("admin/export/sa/survey/action/exportstructuretsv/surveyid/".$oSurvey->sid),
        "download" => true
    ];

    $aExportItemsArray["printablesurveyhtml"] = [
        "key" => "printablesurveyhtml",
        "description" => gT("Printable survey (*.html)"),
        "detailpage" => "
        <p>".gT("This will download a .zip file containing the survey in all languages.")."</p>
        <p>".gT("It will also contain the necessary stylesheets to put it up on any HTML-ready devices or browsers.")."</p>
        <p>".gT("It will not contain any logic or EM-functionality, you'll have to take that into account yourself.")."</p>
        ",
        "href" => $this->createUrl("admin/export/sa/survey/action/exportprintables/surveyid/".$oSurvey->sid),
        "download" => true,
        "downloadFilename" => $oSurvey->sid.'_'.strtolower(preg_replace('([^\w\s\d\-_~,;\[\]\(\).])','',$oSurvey->currentLanguageSettings->surveyls_title)).'.html'
    ];
    if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'read')) {
        if ($onelanguage) {
            $aExportItemsArray["printablesurvey"] = [
                "key" => "printablesurvey",
                "description" => gT("Printable survey"),
                "detailpage" => "
                <p>".gT("This will open the survey as a printable page in new window.")."</p>
                <p>".gT("All necessary styles will be loaded, to print it just press Ctrl/Cmd+p or select print from your browser menu.")."</p>
                <p>".gT("It will not contain any logic or EM-functionality, you'll have to take that into account yourself.")."</p>
                ",
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
                    "detailpage" => "
                    <p>".sprintf(gT("This will open the survey in %s as a printable page in new window."), getLanguageNameFromCode($tmp_lang, false))."</p>
                    <p>".gT("All necessary styles will be loaded, to print it just press Ctrl/Cmd+p or select print from your browsers menu.")."</p>
                    <p>".gT("It will not contain any logic or EM-functionality, you'll have to take that into account yourself.")."</p>
                    ",
                    "href" => $this->createUrl("admin/printablesurvey/sa/index/surveyid/".$oSurvey->sid."/lang/".$tmp_lang),
                    "external" => true,
                    "downloadFilename" => $oSurvey->sid.'_'.strtolower(preg_replace('([^\w\s\d\-_~,;\[\]\(\).])','',$oSurvey->currentLanguageSettings->surveyls_title)).'.html'
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
                        var link = document.createElement('a');
                        link.setAttribute('href', loadUrl);
                        link.setAttribute('download', itemData.downloadFilename ? itemData.downloadFilename : value);
                        link.setAttribute('target', '_blank');
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        $(document).trigger('pjax:load', {url: loadUrl});
                    }
                }
                $('#selector__exportTypeSelector--buttonText').html('".gT("Display/Export")."');
                "
            ],
        ]
    ));
?>
<div class="btn-group hidden-xs">
    <?=$oExportSelector->getModal(); ?>
    <?=$oExportSelector->getButtonOrSelect(); ?>
</div>
<?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
