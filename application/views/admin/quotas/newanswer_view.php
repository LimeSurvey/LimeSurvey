<div class="side-body col-lg-8">
    <div class="row">                             
        <div class="col-lg-12 content-right">
            <h3>
                <?php eT("Survey quota");?>: <?php eT("Add answer");?>
            </h3>


            <div class="jumbotron message-box">
                    <h2><?php echo sprintf(gT("New answer for quota '%s'"), $quota_name);?></h2>
                    <p class="lead">
                        <?php eT("Select question");?>:
                    </p>
                    <?php echo CHtml::form(array("admin/quotas/sa/new_answer/surveyid/{$iSurveyId}/subaction/new_answer_two"), 'post'); ?>                    
                    <p>
                        <select name="quota_qid" size="15">
                            <?php foreach ($newanswer_result as $questionlisting) { ?>
                                <option value="<?php echo $questionlisting['qid'];?>">
                                    <?php echo $questionlisting['title'];?>: <?php echo strip_tags(substr($questionlisting['question'],0,40));?>
                                </option>
                                <?php } ?>
                        </select>
                        <input type="hidden" name="sid" value="'.$iSurveyId.'" />
                        <input type="hidden" name="action" value="quotas" />
                        <input type="hidden" name="subaction" value="new_answer_two" />
                        <input type="hidden" name="quota_id" value="<?php echo sanitize_int($_POST['quota_id']);?>" />
                    </p>
                    <p>
                        <input name="submit" type="submit" class="submit btn btn-default" value="<?php eT("Next");?>" />
                    </p>
                    </form>
            </div>
        </div>
    </div>
</div>