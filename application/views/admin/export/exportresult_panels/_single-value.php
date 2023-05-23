<?php
    $sResponsesId = App()->getRequest()->getParam('responseIds');
?>

<div class="card mb-4 <?= $SingleResponse ? 'd-none' : '' ?>" id="panel-2">
  <div class="card-header ">
    <?php eT("Selection");?>
  </div>
  <div class="card-body">
    <div class="mb-3">
        <!-- From -->
        <label for='export_ids' class="col-md-2 form-label">
          <?php eT("Selected answers"); ?>
        </label>

        <div class="col-md-6">
          <input
             name="responses_id" id="responses_id"
            type="text" readonly class="form-control"
            value="<?= Chtml::encode($sResponsesId); ?>"
          />
        </div>
        <div class="col-md-2">
          <a
            href="<?php echo Yii::app()->getController()->createUrl("admin/export/sa/exportresults", array('surveyid' => $surveyid)); ?>"
            class="btn btn-outline-secondary"  role="button"
          >
            <?php eT("Reset");?>
          </a>
        </div>
    </div>
  </div>
</div>
