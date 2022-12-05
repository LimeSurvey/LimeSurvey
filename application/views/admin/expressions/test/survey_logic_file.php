<?php
/**
 * Important functionailites are set in core now, this is just to render the output
 */

 $gid = $gid ?? NULL;
 $qid = $qid ?? NULL;

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyLogicFile');
?>

<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="container-fluid">
        <h3><?php eT("Survey logic view");?> </h3>
        <div class="row">
            <?=TbHtml::form(array('admin/expressions/sa/survey_logic_file'), 'post', array('id'=>'survey_logic_file_form', 'target' => '_blank'))?>
                <input name="sid" type="hidden" value="<?=$sid?>" />
                <?php if($gid!==NULL): ?> <input name="gid" type="hidden" value="<?=$gid?>" /> <?php endif; ?>
                <?php if($qid!==NULL): ?> <input name="qid" type="hidden" value="<?=$qid?>" /> <?php endif; ?>
                <?php if($lang!==NULL): ?> <input name="lang" type="hidden" value="<?=$lang?>" /> <?php endif; ?>
                
                <input name="printable" type="hidden" value="1" />
                <div class="mb-3">
                    <input type="submit" name="printablesubmit" value="<?=gT("Open printable view")?>" class="btn btn-outline-secondary" />
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-12 content-right">
                <?php echo $result['html']; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /**
     * This whole style block is a workaround for https://bugs.limesurvey.org/view.php?id=18250
     */
    .main-content-container {
        overflow-x: visible;
    }
    .table-responsive {
        overflow-x: visible;
    }

    #vue-sidebar-container {
        display: none;
    }
    #pjax-content {
        max-width: 100%!important;
    }
    #vue-apps-main-container > .col-11 {
        width: 100%;
        max-width: 100%;
    }
</style>