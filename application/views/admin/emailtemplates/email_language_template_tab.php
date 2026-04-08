
<?php
    $script = [];
    $hideAttacehemtTable = true;
    $attachmentsHaveErrors = false;
    if (isset($esrow->attachments[$tab])) {
        foreach ($esrow->attachments[$tab] as $attachment) {
            $script[] = sprintf(
                "prepEmailTemplates.addAttachment($('#attachments-%s-%s'), %s, %s, %s, %s);",
                $grouplang,
                $tab,
                json_encode($attachment['url']),
                json_encode($attachment['relevance']),
                json_encode($attachment['size']),
                json_encode($attachment['error'] ?? '')
            );
            if (!empty($attachment['error'])) {
                $attachmentsHaveErrors = true;
            }
        }
        $hideAttacehemtTable = false;
    }
?>

<div id='<?php echo "tab-".CHtml::encode($grouplang)."-".CHtml::encode($tab); ?>' class="tab-pane fade in <?=CHtml::encode($active); ?>">
    <?php if ($attachmentsHaveErrors): ?>
        <div class="row">
            <div class='col-sm-12'>
                <div class="alert alert-danger"><?= gT("There are errors with this template's attachments. Please check them below.") ?></div>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class='mb-3 col-md-12'>
            <label class=' form-label' for='email_<?php echo $tab; ?>_subj_<?php echo $grouplang; ?>'><?php echo $details['subject'] ?></label>
            <div class=''>
                <?php
                $sSubjectField=$details['field']['subject'];
                echo CHtml::textField("email_{$tab}_subj_{$grouplang}",$esrow->$sSubjectField,array('class' => 'form-control', 'maxlength'=>255)); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class='mb-3 col-md-12'>
            <label class=' form-label' for='email_<?php echo $tab; ?>_<?php echo $grouplang; ?>'><?php echo $details['body']; ?></label>
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
        <div class='mb-3 col-md-12'>
            <label class=' form-label'><?php et('Actions:');?></label>
            <div class=''>
                <a class='btn btn-outline-secondary' 
                   id="validate_expression_<?=$grouplang?>_<?=$tab?>" 
                   data-parent-element="#in_survey_common" 
                   data-bs-target="modal" 
                   data-remote-link="<?=App()->createUrl('admin/validate',['sa'=>'email','sid'=>$surveyid,'lang'=>$grouplang,'type'=>$tab])?>" 
                   data-footer="false" 
                   data-modal-title="<?=$details['title']?>" > 
                    <?=gT("Validate ExpressionScript")?> 
                </a> 
                <?php
                $details['default']['body']=($tab=='admin_detailed_notification') ? $details['default']['body'] : conditionalNewlineToBreak($details['default']['body'],$ishtml) ;
                echo CHtml::button(gT("Reset this template"),array( 'id'=>'reset_template_'.$grouplang.'_'.$tab, 'class'=>'fillin btn btn-outline-secondary selector__reset_template','data-target'=>"email_{$tab}_{$grouplang}",'data-value'=>$details['default']['body']));
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
            <label class='form-label col-12' for="attachments_<?php echo "{$grouplang}-{$tab}"; ?>"><?php echo $details['attachments']; ?></label>
            <div class="col-12">
                <button class="add-attachment btn btn-outline-secondary" data-target="#attachments-<?php echo $grouplang; ?>-<?php echo $tab ?>" data-ck-target="<?="email_{$tab}_{$grouplang}"?>" id="add-attachment-<?php echo "{$grouplang}-{$tab}"; ?>"><?php eT("Add file"); ?></button> &nbsp;
            </div>
    </div>


    <?php } ?>

    <div class="row selector__table-container <?=($hideAttacehemtTable===true ? 'd-none' : '')?>">
        <div class='mb-3 col-12'>
            <div class='mb-3'>
                <div class=' '>
                    <table data-template="[<?php echo $grouplang; ?>][<?php echo $tab ?>]"  data-bs-target="#attachments-<?php echo $grouplang; ?>-<?php echo $tab ?>" data-ck-target="<?="email_{$tab}_{$grouplang}"?>" id ="attachments-<?php echo $grouplang; ?>-<?php echo $tab ?>" class="attachments table table-striped" style="width: 100%;">
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



<table id="rowTemplate" class="d-none">
    <tr>
        <td>
            <button class="btn btn-outline-secondary btn-xs btnattachmentremove" title="<?php eT('Remove attachment') ?>" data-bs-toggle="tooltip" data-bs-placement="bottom">
                <i class="ri-delete-bin-fill text-danger" aria-hidden="true"></i><span class="visually-hidden"><?php eT('Remove attachment') ?></span>
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
            <button class="btn btn-xs btn-outline-secondary edit-relevance-equation" 
            	    title="<?php eT('Edit condition') ?>" 
            	    data-bs-toggle="tooltip" 
            	    data-bs-placement="bottom">
                <i class="ri-pencil-fill" aria-hidden="true"></i><span class="visually-hidden"><?php eT('Edit condition')?></span>
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
