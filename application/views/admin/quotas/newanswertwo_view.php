<?php
/* @var $this AdminController */
/* @var Quota $oQuota */
/* @var Question $oQuestion */
$isAllAnswersSelected = ($oQuestion->type != "*" && count($question_answers) == $x);
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <h3>
                <?php eT("Survey quota");?>: <?php eT("Add answer");?>
            </h3>
            <div class="jumbotron message-box">
                <div class='row'>
            <?php if ($isAllAnswersSelected): ?>
                    <h2><?php eT("All answers are already selected in this quota.");?></h2>
                    <p>
                        <input class="btn btn-lg btn-success" type="submit" onclick="window.open('<?php echo $this->createUrl("admin/quotas/sa/index/surveyid/{$oQuota->sid}");?>', '_top')" value="<?php eT("Continue");?>"/>
                    </p>
            <?php else:?>
                <?php echo CHtml::form(array("admin/quotas/sa/insertquotaanswer/surveyid/{$oQuota->sid}"), 'post', array('#'=>'quota_'.sanitize_int($_POST['quota_id']), 'class' => '')); ?>
                <?php if ($oQuestion->type == '*'): ?>
                    <?php $this->renderPartial('/admin/quotas/_newanswer_equation',['oQuota'=>$oQuota,'oQuestion'=>$oQuestion]);?>
                <?php else:?>
                        <h2><?php echo sprintf(gT("New answer for quota '%s'"), $oQuota->name);?></h2>
                        <p class="lead"><?php eT("Select answer:");?></p>
                        <div class='form-group'>
                            <div class='col-sm-5 col-sm-offset-4'>
                                <select class='form-control' name="quota_anscode" size="15">
                                    <?php
                                        foreach ($question_answers as $key=>$value) {
                                            if (!isset($value['rowexists'])) echo '<option value="'.$key.'">'.strip_tags(substr($value['Display'],0,40)).'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                <?php endif;?>

                    <div class='form-group'>
                        <div class='col-sm-5 col-sm-offset-4'>
                            <input class="btn btn btn-success" name="submit" type="submit" class="submit btn btn-default" value="<?php eT("Next");?>" />
                        </div>
                    </div>
                    <div class='form-group'>
                        <?php eT("Save this, then create another:");?>
                        <input type="checkbox" name="createanother">
                        <input type="hidden" name="sid" value="<?= $oQuota->sid;?>" />
                        <input type="hidden" name="action" value="quotas" />
                        <input type="hidden" name="subaction" value="insertquotaanswer" />
                        <input type="hidden" name="quota_qid" value="<?= $oQuestion->qid;?>" />
                        <input type="hidden" name="quota_id" value="<?= $oQuota->id;?>" />
                        <?php CHtml::endForm()?>
                    </div>

            <?php endif;?>
                </div>
            </div>
        </div>

    </div>
</div>
