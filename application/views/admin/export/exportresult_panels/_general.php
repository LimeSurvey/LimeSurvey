<!-- General -->
<div class="panel panel-primary" id="panel-3">
  <div class="panel-heading">
    <div class="panel-title h4">
      <?php eT("General"); ?>
    </div>
  </div>
  <div class="panel-body">
    <div class="form-group row">
      <label for='completionstate' class="control-label">
        <?php eT("Completion state:");?>
      </label>

      <div class="">
        <select name='completionstate' id='completionstate' class='form-control'>
          <option value='complete' <?php echo $selecthide;?>>
            <?php eT("Completed responses only");?>
          </option>
          <option value='all' <?php echo $selectshow;?>>
            <?php eT("All responses");?>
          </option>
          <option value='incomplete' <?php echo $selectinc;?>>
            <?php eT("Incomplete responses only");?>
          </option>
        </select>
      </div>
    </div>

    <div class="form-group row">
      <label for='exportlang' class="control-label">
        <?php eT("Export language:"); ?>
      </label>
      <div class=''>
        <?php echo CHtml::dropDownList('exportlang', null, $aLanguages, array('class'=>'form-control')); ?>
      </div>
    </div>
  </div>
</div>
