<?php
/**
 * Important functionailites are set in core now, this is just to render the output
 */

 $gid = isset($gid) ? $gid : NULL;
 $qid = isset($qid) ? $qid : NULL;
?>

<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="container-center">
        <h3><?php eT("Survey logic view");?> </h3>
        <div class="row">
            <?=TbHtml::form(array('admin/expressions/sa/survey_logic_file'), 'post', array('id'=>'survey_logic_file_form', 'target' => '_blank'))?>
                <input name="sid" type="hidden" value="<?=$sid?>" />
                <?php if($gid!==NULL): ?> <input name="gid" type="hidden" value="<?=$gid?>" /> <?php endif; ?>
                <?php if($qid!==NULL): ?> <input name="qid" type="hidden" value="<?=$qid?>" /> <?php endif; ?>
                <?php if($lang!==NULL): ?> <input name="lang" type="hidden" value="<?=$lang?>" /> <?php endif; ?>
                
                <input name="printable" type="hidden" value="1" />
                <div class="form-group">
                    <input type="submit" name="printablesubmit" value="<?=gT("Open printable view")?>" class="btn btn-default" />
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-lg-12 content-right">
                <?php echo $result['html']; ?>
            </div>
        </div>
    </div>
</div>