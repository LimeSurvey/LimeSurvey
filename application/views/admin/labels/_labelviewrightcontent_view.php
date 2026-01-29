<div class="row">
  <div class="col-6 offset-3 text-center mt-4">
    <div id="accordion" role="tablist">
      <div class="card card-primary" id="up_resmgmt">

        <!-- Uploaded resources management -->
        <div class="card-header" role="tab" id="headingOne">
          <div class="">
            <a role="button" data-bs-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              <?php eT("Uploaded resources management"); ?>
            </a>
          </div>
        </div>
        <!-- headingOne -->

        <!-- Body -->
        <div id="collapseOne" class="panel-collapse collapse show" role="tabpanel" aria-labelledby="headingOne">
          <div class="card-body">
            <div id="upload-form-container">

              <!-- Form browselabelresources -->
              <?php echo CHtml::form(
                  Yii::app()->baseUrl . '/vendor/kcfinder/browse.php?language=' . sTranslateLangCode2CK(App()->language),
                  'get',
                  array('id' => 'browselabelresources',
                    'class' => 'form30',
                    'name' => 'browselabelresources',
                    'target' => '_blank')
              ); ?>

                  <!-- Browse uploaded resources -->
                    <div class="row mb-2">
                        <div class="col-8 offset-2">
                            <?php echo CHtml::dropDownList('type', 'files', array('files' => gT('Files', 'unescaped'), 'flash' => gT('Flash', 'unescaped'), 'images' => gT('Images', 'unescaped')), array('class' => ' form-select')); ?>
                        </div>
                    </div>
                <div class="row mb-2">
                    <div class="">
                      <input type='submit' class="btn btn-outline-secondary" value="<?php eT("Browse uploaded resources") ?>" />
                    </div>
                </div>
                  <!-- Export resources as ZIP archive -->
                <div class="row mb-2">
                    <div class="">
                    <button class="btn btn-outline-secondary" <?php echo hasResources($lid, 'label') === false ? ' disabled="disabled"' : '' ?> onclick='window.open("
                    <?php echo $this->createUrl("/admin/export/sa/resources/export/label/lid/$lid"); ?>", "_blank")'>
                        <?php eT("Export resources as ZIP archive") ?>
                    </button>
                    </div>
                </div>
                <input type='hidden' name='lid' value='<?php echo $lid; ?>' />
                <?php
                echo CHtml::endForm();
                ?>

                <!-- Form importlabelresources -->
                <?php
                echo CHtml::form(
                    array('admin/labels/sa/importlabelresources'),
                    'post',
                    array(
                        'id' => 'importlabelresources',
                        'class' => 'form30',
                        'name' => 'importlabelresources',
                        'enctype' => 'multipart/form-data',
                        'onsubmit' => 'return window.LS.validatefilename(this, "' . gT(
                            'Please select a file to import!',
                            'js'
                        ) . '");'
                    )
                );
                ?>
                <div class="container mt-4">
                    <div class="row mb-2">
                        <label class="col-12 label-control" for='the_file'>
                            <?php
                            eT("Select ZIP file:") ?>
                        </label>
                    </div>
                    <div class="row">
                        <!-- Select ZIP file -->
                        <div class="col-8 offset-2 mb-3">
                            <input id='the_file' name="the_file" class="form-control" type="file"/>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Import resources ZIP archive -->
                        <div class="">
                            <input class="btn btn-outline-secondary" type='button' value='<?php
                            eT("Import resources ZIP archive") ?>' <?php
                            echo !class_exists('ZipArchive')
                                ? "onclick='alert(\"" . gT(
                                    "The ZIP library is not activated in your PHP configuration thus importing ZIP files is currently disabled.",
                                    "js"
                                ) . "\");'"
                                : "onclick='if (window.LS.validatefilename(this.form,\"" . gT(
                                    'Please select a file to import!',
                                    'js'
                                ) . "\")) { this.form.submit();}'"
                            ?>/>
                        </div>
                    </div>
                </div>
            </div>
              <input type='hidden' name='lid' value='<?php
                echo $lid; ?>'/>
              <input type='hidden' name='action' value='importlabelresources'/>
              <?= CHtml::endForm(); ?>
            </div>
            <!-- upload-form-container -->
          </div>
          <!-- card-body -->
        </div>
        <!-- collapseOne -->
      </div>
      <!-- up_resmgmt -->
    </div>
    <!-- accordion -->
  </div>
  <!-- content-right -->
</div>
<!--row -->
