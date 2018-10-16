<div class="row">
  <div class="col-sm-12 content-right text-center">
    <div class="panel-group" id="accordion" role="tablist" style="margin-top: 20px">
      <div class="panel panel-default" id="up_resmgmt">

        <!-- Uploaded resources management -->
        <div class="panel-heading" role="tab" id="headingOne">
          <div class="panel-title h4">
            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              <?php eT("Uploaded resources management"); ?>
            </a>
          </div>
        </div>
        <!-- headingOne -->

        <!-- Body -->
        <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
          <div class="panel-body">
            <div id="upload-form-container">

              <!-- Form browselabelresources -->
              <?php echo CHtml::form(Yii::app()->baseUrl.'/third_party/kcfinder/browse.php?language='.sTranslateLangCode2CK(App()->language), 'get', array('id'=>'browselabelresources','class'=>'form30','name'=>'browselabelresources','target'=>'_blank')); ?>
                <ul class="list-unstyled">

                  <!-- Browse uploaded resources -->
                  <li>
                    <label>&nbsp;</label>
                    <div class="">
                      <?php echo CHtml::dropDownList('type', 'files', array('files' => gT('Files','unescaped'), 'flash' => gT('Flash','unescaped'), 'images' => gT('Images','unescaped')), array('class'=>' form-control') ); ?>
                    </div>
                    <div class="">
                      <input type='submit' class="btn btn-default" value="<?php eT(" Browse uploaded resources ") ?>" />
                    </div>
                  </li>

                  <!-- Export resources as ZIP archive -->
                  <li>
                    <label>&nbsp;</label>
                    <input class="btn btn-default" type='button' <?php echo hasResources($lid, 'label')===false ? ' disabled="disabled"' : '' ?> onclick='window.open("
                    <?php echo $this->createUrl("/admin/export/sa/resources/export/label/lid/$lid"); ?>", "_blank")' value="
                      <?php eT("Export resources as ZIP archive") ?>" />
                  </li>
                </ul>
                <input type='hidden' name='lid' value='<?php echo $lid; ?>' />
                </form>

                <!-- Form importlabelresources -->
                <?php 
                    echo CHtml::form(
                        array('admin/labels/sa/importlabelresources'), 
                        'post', 
                        array(
                            'id'=>'importlabelresources',
                            'class'=>'form30',
                            'name'=>'importlabelresources',
                            'enctype'=>'multipart/form-data',
                            'onsubmit'=>'return window.LS.validatefilename(this, "'.gT('Please select a file to import!', 'js').'");'
                        )
                    ); 
                ?>
                  <ul class="list-unstyled">
                    <li>
                      <br/>
                      <label class="col-sm-12 label-control" for='the_file'>
                        <?php eT("Select ZIP file:") ?>
                      </label>

                      <!-- Select ZIP file -->
                      <div class="col-sm-6"> <input id='the_file' name="the_file" type="file" /> </div>


                      <!-- Import resources ZIP archive -->
                      <div class="col-sm-6">
                        <input class="btn btn-default" type='button' value='<?php eT("Import resources ZIP archive") ?>' <?php 
                        echo !function_exists("zip_open") 
                            ? "onclick='alert(\"" . gT("The ZIP library is not activated in your PHP configuration thus importing ZIP files is currently disabled.", "js") . "\");'" 
                            : "onclick='if (window.LS.validatefilename(this.form,\"" . gT('Please select a file to import!', 'js') . "\")) { this.form.submit();}'" 
                        ?>/>
                      </div>
                    </li>
                  </ul>
                  <input type='hidden' name='lid' value='<?php echo $lid; ?>' />
                  <input type='hidden' name='action' value='importlabelresources' />
                  </form>
            </div>
            <!-- upload-form-container -->
          </div>
          <!-- panel-body -->
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
