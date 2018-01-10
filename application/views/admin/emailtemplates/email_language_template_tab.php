
<?php
$script = array();
?>

<div id='<?php echo "tab-$grouplang-$tab"; ?>' class="tab-pane fade in <?php echo $active; ?>">
    <div class="row">
        <div class='form-group col-sm-12'>
            <label class=' control-label' for='email_<?php echo $tab; ?>_subj_<?php echo $grouplang; ?>'><?php echo $details['subject'] ?></label>
            <div class=''>
                <?php
                $sSubjectField=$details['field']['subject'];
                echo CHtml::textField("email_{$tab}_subj_{$grouplang}",$esrow->$sSubjectField,array('class' => 'form-control', 'size'=>80)); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class='form-group col-sm-12'>
            <label class=' control-label' for='email_<?php echo $tab; ?>_<?php echo $grouplang; ?>'><?php echo $details['body']; ?></label>
            <div class=''>
                <?php
                $sBodyField=$details['field']['body'];
                echo CHtml::textArea("email_{$tab}_{$grouplang}",$esrow->$sBodyField,array('cols'=>80,'rows'=>20, 'class'=>'form-control')); ?>
                <?php echo getEditor("email-$tab","email_{$tab}_$grouplang", $details['body'].'('.$grouplang.')',$surveyid,'','','editemailtemplates'); ?>
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
                echo CHtml::button(gT("Reset this template"),array('class'=>'fillin btn btn-default','data-target'=>"email_{$tab}_{$grouplang}",'data-value'=>$details['default']['body']));
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
            <label class='control-label col-md-1 col-sm-2 col-xs-6' for="attachments_<?php echo "{$grouplang}-{$tab}"; ?>"><?php echo $details['attachments']; ?></label>&nbsp;
            <button class="add-attachment btn btn-default col-md-1 col-sm-2 col-xs-6" data-target="#attachments-<?php echo $grouplang; ?>-<?php echo $tab ?>" data-ck-target="<?="email_{$tab}_{$grouplang}"?>" id="add-attachment-<?php echo "{$grouplang}-{$tab}"; ?>"><?php eT("Add file"); ?></button> &nbsp;
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
                    <table data-template="[<?php echo $grouplang; ?>][<?php echo $tab ?>]" id ="attachments-<?php echo $grouplang; ?>-<?php echo $tab ?>" class="attachments table table-striped" style="width: 100%;">
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
            <button class="btn btn-xs btn-danger" title="<?php eT('Remove attachment')?>" class="btnattachmentremove text-warning" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Remove attachment')?>">
                <i class="fa fa-trash"></i>
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
            <button class="btn btn-xs btn-default" title="<?php eT('Edit relevance equation') ?>" class="edit-relevance-equation" data-toggle="tooltip" data-placement="bottom" title="<?php eT('Edit relevance equation') ?>">
                <i class="fa fa-pencil"></i>
            </button>
            <input class="relevance" type="hidden">
        </td>
    </tr>
</table>

<?php                 

App()->getClientScript()->registerScript("ScriptEmailTemplateLanguageTemplate_<?=$grouplang?>_<?=$tab?>", "
    $('#validate_expression_".$grouplang."_".$tab."').remoteModal();\n\n
    var prepEmailTemplates = PrepEmailTemplates();\n
    prepEmailTemplates.init();\n
    ".implode("\n", $script), LSYii_ClientScript::POS_POSTSCRIPT);

?>
