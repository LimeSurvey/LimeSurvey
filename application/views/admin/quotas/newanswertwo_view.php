<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <h3>
                <?php eT("Survey quota");?>: <?php eT("Add answer");?>
            </h3>

            <?php if (count($question_answers) == $x): ?>
                <div class="jumbotron message-box">
                        <h2><?php eT("All answers are already selected in this quota.");?></h2>
                        <p>
                            <input class="btn btn-lg btn-success" type="submit" onclick="window.open('<?php echo $this->createUrl("admin/quotas/sa/index/surveyid/$iSurveyId");?>', '_top')" value="<?php eT("Continue");?>"/>
                        </p>
                </div>
            <?php else:?>
                <div class="jumbotron message-box">
                    <div class='row'>
                        <h2><?php echo sprintf(gT("New answer for quota '%s'"), $quota_name);?></h2>
                        <p class="lead"><?php eT("Select answer:");?></p>
                        <?php echo CHtml::form(array("admin/quotas/sa/insertquotaanswer/surveyid/{$iSurveyId}"), 'post', array('#'=>'quota_'.sanitize_int($_POST['quota_id']), 'class' => 'form-horizontal')); ?>
                            <div class='form-group'>
                                <div class='col-sm-5 col-sm-offset-4'>
                                    <select class='form-control' name="quota_anscode" size="15">
                                        <?php
                                            while (list($key,$value) = each($question_answers))
                                            {
                                                if (!isset($value['rowexists'])) echo '<option value="'.$key.'">'.strip_tags(substr($value['Display'],0,40)).'</option>';
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group'>
                                <div class='col-sm-5 col-sm-offset-4'>
                                    <input class="btn btn btn-success" name="submit" type="submit" class="submit btn btn-default" value="<?php eT("Next");?>" />
                                </div>
                            </div>
                            <div class='form-group'>
                                <input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
                                <input type="hidden" name="action" value="quotas" />
                                <input type="hidden" name="subaction" value="insertquotaanswer" />
                                <input type="hidden" name="quota_qid" value="<?php echo sanitize_int($_POST['quota_qid']);?>" />
                                <input type="hidden" name="quota_id" value="<?php echo sanitize_int($_POST['quota_id']);?>" />
                                <?php eT("Save this, then create another:");?>
                                <input type="checkbox" name="createanother">
                            </div>
                       </form>
                   </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>
