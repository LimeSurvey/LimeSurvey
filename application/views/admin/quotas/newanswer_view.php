<?php
/* @var $this AdminController */
/* @var Quota $oQuota */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-8 content-right">
            <h3>
                <?php eT("Survey quota");?>: <?php eT("Add answer");?>
            </h3>


            <div class="jumbotron message-box">
                <div class='row'>
                    <h2><?php echo sprintf(gT("New answer for quota '%s'"), htmlentities($oQuota->name));?></h2>
                    <p class="lead">
                        <?php eT("Select question");?>:
                    </p>
                    <?php echo CHtml::form(array("admin/quotas/sa/new_answer/surveyid/{$oQuota->sid}/subaction/new_answer_two"), 'post', array('class' => '')); ?>
                        <div class='form-group'>
                            <div class='col-sm-5 col-sm-offset-4'>
                                <select class='form-control' name="quota_qid" size="15">
                                    <?php foreach ($oQuota->survey->quotableQuestions as $questionlisting) { ?>
                                        <option value="<?php echo $questionlisting['qid'];?>">
                                            <?php echo $questionlisting['title'];?>: <?php echo strip_tags(substr($questionlisting['question'],0,40));?>
                                        </option>
                                        <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-5 col-sm-offset-4'>
                                <input name="submit" type="submit" class="submit btn btn-default" value="<?php eT("Next");?>" />
                            </div>
                        </div>
                        <input type="hidden" name="sid" value="<?php echo $iSurveyId;?>" />
                        <input type="hidden" name="action" value="quotas" />
                        <input type="hidden" name="subaction" value="new_answer_two" />
                        <input type="hidden" name="quota_id" value="<?php echo sanitize_int($oQuota->id);?>" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
