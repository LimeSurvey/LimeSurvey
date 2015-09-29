<?php
/**
 * Right accordion, integration pannel
 * Use jqGrid, needs surveysettings.js
 */
    $yii = Yii::app();
    $controller = $yii->getController();
?>

<!-- jQgrid data -->
<script type="text/javascript">
    var jsonUrl = "<?php echo App()->createUrl('admin/survey', array('sa' => 'getUrlParamsJson', 'surveyid' => $surveyid))?>";
    var imageUrl = "<?php echo $yii->getConfig("adminimageurl");?>";
    var sAction = "<?php  eT('Action','js');?>";
    var sParameter = "<?php  eT('Parameter','js');?>";
    var sTargetQuestion = "<?php  eT('Target question','js');?>";
    var sURLParameters = "<?php  eT('URL parameters','js');?>";
    var sNoParametersDefined = "<?php  eT('No parameters defined','js');?>";
    var sSureDelete = "<?php  eT('Are you sure you want to delete this URL parameter?','js');?>";
    var sEnterValidParam = "<?php  eT('You have to enter a valid parameter name.','js');?>";
    var sAddParam = "<?php  eT('Add URL parameter','js');?>";
    var sEditParam = "<?php  eT('Edit URL parameter','js');?>";
</script>

<!-- jQgrid container -->
<div id='panelintegration' class=" tab-pane fade in">
    <table id="urlparams" style='margin:0 auto;'><tr><td>&nbsp;</td></tr></table>
    <div id="pagerurlparams"></div>
    <input type='hidden' id='allurlparams' name='allurlparams' value='' />
</div>

<!-- Modal box to add a parameter -->
<div data-copy="submitsurveybutton"></div>
<div id='dlgEditParameter'>
    <div id='dlgForm' class='form30'>
        <ul>
            <li>
                <label for='paramname'><?php eT('Parameter name:'); ?></label><input name='paramname' id='paramname' type='text' size='20' />
            </li>
            <li>
                <label for='targetquestion'><?php eT('Target (sub-)question:'); ?></label><select name='targetquestion' id='targetquestion' size='1'>
                    <option value=''><?php eT('(No target question)'); ?></option>
                    <?php foreach ($questions as $question){?>
                        <option value='<?php echo $question['qid'].'-'.$question['sqid'];?>'><?php echo $question['title'].': '.ellipsize(flattenText($question['question'],true,true),43,.70);
                            if ($question['sqquestion']!='')
                            {
                                echo ' - '.ellipsize(flattenText($question['sqquestion'],true,true),30,.75);
                            }
                        ?></option> <?php
                    }?>
                </select>
            </li>
        </ul>
    </div>
    <p><button id='btnSaveParams'><?php eT('Save'); ?></button> <button id='btnCancelParams'><?php eT('Cancel'); ?></button> </p>
</div>