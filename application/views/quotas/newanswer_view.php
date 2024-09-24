<?php
/* @var $this AdminController */
/* @var Quota $oQuota */
?>

<div class='side-body'>
    <div class="row">
        <div class="col-xl-8 content-right">
            <h3>
                <?php eT("Survey quota");?>: <?php eT("Add answer");?>
            </h3>


            <div class="jumbotron message-box">
                <div class='row'>
                    <h2><?php echo sprintf(gT("New answer for quota '%s'"), CHtml::encode($oQuota->name));?></h2>
                    <p class="lead">
                        <?php eT("Select question");?>:
                    </p>
                    <?php echo CHtml::form(array("quotas/newanswer/surveyid/{$oQuota->sid}/sSubaction/new_answer_two"), 'post', array('class' => '')); ?>
                        <div class='mb-3'>
                            <div class='col-md-5 offset-md-4'>
                                <select class='form-select' name="quota_qid" size="15">
                                    <?php foreach ($oQuota->survey->quotableQuestions as $questionlisting) { ?>
                                        <option value="<?php echo $questionlisting['qid'];?>">
                                            <?php echo $questionlisting['title'];?>: <?php echo strip_tags(substr((string) $questionlisting->questionl10ns[$sBaseLang]->question,0,40));?>
                                        </option>
                                        <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class='mb-3'>
                            <div class='col-md-5 offset-md-4'>
                                <input name="submit" type="submit" class="submit btn btn-outline-secondary" value="<?php eT("Next");?>" />
                            </div>
                        </div>
                        <input type="hidden" name="sid" value="<?php echo $surveyid;?>" />
                        <input type="hidden" name="action" value="quotas" />
                        <input type="hidden" name="subaction" value="new_answer_two" />
                        <input type="hidden" name="quota_id" value="<?php echo sanitize_int($oQuota->id);?>" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
