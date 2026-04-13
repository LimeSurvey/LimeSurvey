<!-- Heading -->
<div class="card mb-4" id="panel-4">
  <div class="card-header ">
      <?php eT("Headings");?>
  </div>
  <div class="card-body">

    <!-- Headers -->
    <div class="mb-3 row">
        <label class="col-md-12 form-label" for=''>
        <?php eT("Export questions as:"); ?>
      </label>
      <div class="btn-group col-md-12">
        <?php foreach($headexports as $type=>$headexport):?>
            <input
                class="btn-check"
                value="<?= $type; ?>"
                id="headstyle-<?= $type; ?>"
                type="radio"
                name="headstyle" <?php if($headexport[ 'checked']=='checked' ){ echo 'checked';} ?>
            />
            <label class="btn btn-outline-secondary" for="headstyle-<?= $type; ?>">
                <?= $headexport['label'];?>
            </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Strip HTML -->
    <div class="mb-3 row">
      <label class="col-md-12 form-label" for='striphtmlcode'>
        <?php eT("Strip HTML code:"); ?>
      </label>
      <div class='col-md-12'>
          <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
              'name' => 'striphtmlcode',
              'checkedOption' => '1',
              'selectOptions' => [
                  '1' => gT('On'),
                  '0' => gT('Off'),
              ]
          ]); ?>
      </div>
    </div>

    <!-- Convert spaces -->
    <div class="mb-3 row">
      <label class="col-md-12 form-label" for='headspacetounderscores'>
        <?php eT("Convert spaces in question text to underscores:"); ?>
      </label>
      <div class='col-md-12'>
          <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
              'name' => 'headspacetounderscores',
              'checkedOption' => 0,
              'selectOptions' => [
                  '1' => gT('On'),
                  '0' => gT('Off'),
              ],
          ]); ?>
      </div>
    </div>

    <!-- Text abbreviated-->
    <div class="mb-3 row">
      <label class="col-md-12 form-label" for='abbreviatedtext'>
        <?php eT("Text abbreviated:"); ?>
      </label>
      <div class='col-md-12'>
          <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
              'name' => 'abbreviatedtext',
              'checkedOption' => 0,
              'selectOptions' => [
                  '1' => gT('On'),
                  '0' => gT('Off'),
              ],
          ]); ?>
      </div>
    </div>

    <!-- Use ExpressionScript Engine code-->
    <div class="mb-3 row">
      <label class="col-md-12 form-label" for='emcode'>
        <?php eT("Use ExpressionScript code:"); ?>
      </label>
      <div class='col-md-12'>
          <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
              'name' => 'emcode',
              'checkedOption' => 0,
              'selectOptions' => [
                  '1' => gT('On'),
                  '0' => gT('Off'),
              ],
          ]); ?>
      </div>
    </div>

    <div class="mb-3 row">
      <label for='abbreviatedtextto' class="col-md-12 form-label">
        <?php eT("Number of characters:"); ?>
      </label>
      <div class="col-md-12">
        <input min="1" step="1" type="number" value="15" name="abbreviatedtextto" id="abbreviatedtextto" class="form-control" />
      </div>
    </div>

    <div class="mb-3 row">
      <label for='codetextseparator' class="col-md-12 form-label">
        <?php eT("Code/text separator:"); ?>
      </label>
      <div class="col-md-12">
        <input size="4" type="text" value=". " name="codetextseparator" id="codetextseparator" class="form-control" />
      </div>
    </div>
  </div>
</div>
