<?php
/**
 * @var $aTabTitles
 * @var $aTabContents
 * @var $has_permissions
 * @var $surveyid
 * @var $surveyls_language
 */
if(isset($data)){
    extract($data);
}
 $count=0;
 if(isset($scripts))
    echo $scripts;

?>
<div class="container-center">
    <div id="advancedTextEditor"><lsnexttexteditor/></div>
    <div id="textEditLoader" class="ls-flex ls-flex-column align-content-center align-items-center">
        <div class="ls-flex align-content-center align-items-center">
            <div class="loader-advancedquestionsettings text-center">
                <div class="contain-pulse animate-pulse">
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                    <div class="square"></div>
                </div>
            </div>
        </div>
    </div>
</div>

