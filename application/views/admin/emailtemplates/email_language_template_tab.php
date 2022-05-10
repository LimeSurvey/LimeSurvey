
<?php
$script = array();
?>

<div id='<?php echo "tab-".CHtml::encode($grouplang)."-".CHtml::encode($tab); ?>' class="tab-pane fade in <?=CHtml::encode($active); ?>">
    <div class="row">
        <div class='form-group col-sm-12'>
            <label class=' control-label' for='email_<?php echo $tab; ?>_subj_<?php echo $grouplang; ?>'><?php echo $details['subject'] ?></label>
            <div class=''>
                <?php
                $sSubjectField=$details['field']['subject'];
                echo CHtml::textField("email_{$tab}_subj_{$grouplang}",$esrow->$sSubjectField,array('class' => 'form-control', 'maxlength'=>255)); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class='form-group col-sm-12'>
            <label class=' control-label' for='email_<?php echo $tab; ?>_<?php echo $grouplang; ?>'><?php echo $details['body']; ?></label>
            <?php if(getEmailFormat($surveyid) != 'html') { ?>
                <div class="">
            <?php }else{ ?>
                <div class="htmleditor input-group">
            <?php } ?>
            <?php
                $sBodyField=$details['field']['body'];
                echo CHtml::textArea("email_".$tab."_".$grouplang,$esrow->$sBodyField,array('cols'=>80,'rows'=>20, 'class'=>'form-control')); ?>
                <?php echo getEditor("email_".$tab."_".$grouplang, "email_".$tab."_".$grouplang, $details['body'].'('.$grouplang.')',$surveyid,'','','editemailtemplates'); ?>
            </div>
            <div class=''></div>
        </div>
    </div>
    <div class="row">
        <div class='form-group col-sm-12'>
            <label class=' control-label'><?php et('Actions:');?></label>
            <div class=''>
                <a class='btn btn-default' id="validate_expression_<?=$grouplang?>_<?=$tab?>" data-parent-element="#in_survey_common" data-target="modal" data-remote-link="<?=App()->createUrl('admin/validate',['sa'=>'email','sid'=>$surveyid,'lang'=>$grouplang,'type'=>$tab])?>" data-footer="false" data-modal-title="<?=$details['title']?>" > 
                    <?=gT("Validate expressions")?> 
                </a> 
                <?php
                $details['default']['body']=($tab=='admin_detailed_notification') ? $details['default']['body'] : conditionalNewlineToBreak($details['default']['body'],$ishtml) ;
                echo CHtml::button(gT("Reset this template"),array( 'id'=>'reset_template_'.$grouplang.'_'.$tab, 'class'=>'fillin btn btn-default selector__reset_template','data-target'=>"email_{$tab}_{$grouplang}",'data-value'=>$details['default']['body']));
                ?>
            </div>
        </div>
    </div>
    <div class="row">
        <br/>
    </div>
    <?php
    if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update'))
    { ?>
    <div class="row">
            <label class='control-label col-xs-12' for="attachments_<?php echo "{$grouplang}-{$tab}"; ?>"><?php echo $details['attachments']; ?></label>
            <div class="col-xs-12">
                <button class="add-attachment btn btn-default" data-target="#attachments-<?php echo $grouplang; ?>-<?php echo $tab ?>" data-ck-target="<?="email_{$tab}_{$grouplang}"?>" id="add-attachment-<?php echo "{$grouplang}-{$tab}"; ?>"><?php eT("Add file"); ?></button> &nbsp;
            </div>
    </div>


    <?php } ?>

    <?php
    $hideAttacehemtTable = true;
    if (isset($esrow->attachments[$tab])) {
        foreach ($esrow->attachments[$tab] as $attachment) {
            $script[] = sprintf("prepEmailTemplates.addAttachment($('#attachments-%s-%s'), %s, %s, %s );", $grouplang, $tab, json_encode($attachment['url']), json_encode($attachment['relevance']), json_encode($attachment['size']));
        }
        $hideAttacehemtTable = false;
    }
    ?>

    <div class="row selector__table-container <?=($hideAttacehemtTable===true ? 'hidden' : '')?>">
        <div class='form-group col-sm-12'>
            <div class='form-group'>
                <div class=' '>
                    <table data-template="[<?php echo $grouplang; ?>][<?php echo $tab ?>]"  data-target="#attachments-<?php echo $grouplang; ?>-<?php echo $tab ?>" data-ck-target="<?="email_{$tab}_{$grouplang}"?>" id ="attachments-<?php echo $grouplang; ?>-<?php echo $tab ?>" class="attachments table table-striped" style="width: 100%;">
                        <thead>
                            <tr>
                                <th><?php eT("Action"); ?></th>
                                <th><?php eT("File name"); ?></th>
                                <th><?php eT("Size"); ?></th>
                                <th><?php eT("Relevance"); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<table id="rowTemplate" class="hidden">
    <tr>
        <td>
            <button class="btn btn-xs btn-danger btnattachmentremove" title="<?php eT('Remove attachment')?>" data-toggle="tooltip" data-placement="bottom">
                <i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only"><?php eT('Remove attachment')?></span>
            </button>
        </td>
        <td>
            <span class="filename"></span>
            <input class="filename" type="hidden">
        </td>
        <td>
            <span class="filesize"></span>
        </td>
        <td>
            <span class="relevance"></span>
            <button class="btn btn-xs btn-default edit-relevance-equation" title="<?php eT('Edit relevance equation') ?>" data-toggle="tooltip" data-placement="bottom">
                <i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only"><?php eT('Edit relevance equation')?></span>
            </button>
            <input class="relevance" type="hidden">
        </td>
    </tr>
</table>

<?php

App()->getClientScript()->registerScript("ScriptEmailTemplateLanguageTemplate_<?=$grouplang?>_<?=$tab?>", "
    var prepEmailTemplates = PrepEmailTemplates();\n
    prepEmailTemplates.init();\n
    prepEmailTemplates.bindActions({validate: '#validate_expression_".$grouplang."_".$tab."', reset: '#reset_template_".$grouplang."_".$tab."'}, 
    {close: '".gT('Close')."', save: '".gT('Save')."'}, '".App()->getController()->createUrl('admin/emailtemplates/getTemplateOfType', array('type' => $tab, 'language' => $grouplang, 'survey' => $surveyid ))."');\n
    ".implode("\n", $script), LSYii_ClientScript::POS_POSTSCRIPT);
?>
