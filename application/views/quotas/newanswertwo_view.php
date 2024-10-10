<?php
/* @var bool  $isAllAnswersSelected*/
/* @var Quota $oQuota */
/* @var Question $oQuestion */
?>

<div class='side-body'>
    <div class="row">
        <div class="col-12 content-right">
            <h3>
                <?php eT("Survey quota");?>: <?php eT("Add answer");?>
            </h3>
            <div class="jumbotron message-box">
                <div class='row'>
            <?php if ($isAllAnswersSelected){ ?>
                    <h2><?php eT("All answers are already selected in this quota.");?></h2>
                    <p>
                        <input class="btn btn-lg btn-primary" type="submit" onclick="window.open('<?php echo $this->createUrl("quotas/index/surveyid/{$oQuota->sid}");?>', '_top')" value="<?php eT("Continue");?>"/>
                    </p>
            <?php } else {
                echo CHtml::form(
                    array("quotas/insertQuotaAnswer/surveyid/{$oQuota->sid}"),
                    'post',
                    array('#'=>'quota_'.sanitize_int($_POST['quota_id']), 'class' => ''));
                if ($oQuestion->type == '*') {
                    $this->renderPartial(
                        '_newanswer_equation',
                        ['oQuota'=>$oQuota,'oQuestion'=>$oQuestion]
                    );
                } else { ?>
                        <h2><?php echo sprintf(gT("New answer for quota '%s'"), CHtml::encode($oQuota->name));?></h2>
                        <p class="lead"><?php eT("Select answer:");?></p>
                        <div class='mb-3'>
                            <div class='col-md-5 offset-md-4'>
                                <select class='form-select' name="quota_anscode" size="15">
                                    <?php
                                        foreach ($question_answers as $key => $value) {
                                            if (!isset($value['rowexists'])) {
                                                echo '<option value="' . $key . '">' . strip_tags(substr((string) $value['Display'], 0, 40)) . '</option>';
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                <?php }?>

                    <div class='mb-3'>
                        <div class='col-md-5 offset-md-4'>
                            <button class="btn btn btn-primary" name="submit"  class="submit btn btn-outline-secondary">
                                <?php eT("Next");?>
                            </button>
                        </div>
                    </div>
                    <div class='mb-3'>
                        <?php eT("Save this, then create another:");?>
                        <input type="checkbox" name="createanother">
                        <input type="hidden" name="sid" value="<?= $oQuota->sid;?>" />
                        <input type="hidden" name="action" value="quotas" />
                        <input type="hidden" name="subaction" value="insertquotaanswer" />
                        <input type="hidden" name="quota_qid" value="<?= $oQuestion->qid;?>" />
                        <input type="hidden" name="quota_id" value="<?= $oQuota->id;?>" />
                        <?php CHtml::endForm()?>
                    </div>

            <?php }?>
                </div>
            </div>
        </div>

    </div>
</div>
