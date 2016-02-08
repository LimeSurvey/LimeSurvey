<div id="general" class="tab-pane fade in active">
    <div class='col-lg-8'>
        <div class="row">
            <div class="form-group">
                <label   class="col-sm-2 control-label" for='language' title='<?php  eT("This is the base language of your survey and it can't be changed later. You can add more languages after you have created the survey."); ?>'><span class='annotationasterisk'>*</span><?php  eT("Base language:"); ?></label>
                <div class="col-sm-5">
                    <select id='language' name='language'  class="form-control">
                        <?php foreach (getLanguageDataRestricted (false, Yii::app()->session['adminlang']) as $langkey2 => $langname) { ?>
                            <option value='<?php echo $langkey2; ?>'
                            <?php if (Yii::app()->getConfig('defaultlang') == $langkey2) { ?>
                                 selected='selected'
                            <?php } ?>
                            ><?php echo $langname['description']; ?> </option>
                        <?php } ?>
                    </select>
                </div>
                <span class='text-warning'> <?php  eT("*This setting cannot be changed later!"); ?></span></li>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label"  for='surveyls_title'><?php  eT("Title"); ?> :</label>
                <div class="col-sm-5">
                    <input type='text' maxlength='200' id='surveyls_title' name='surveyls_title' required="required" autofocus="autofocus" style="width: 100%" />
                </div>
                <span class='text-warning'><?php  eT("Required"); ?> </span>
            </div>

            <div class="form-group">
                <label for='description' class="col-sm-2 control-label"><?php  eT("Description:"); ?> </label>
                <br/><br/>
                <div class='htmleditor col-sm-offset-2' style="position: relative; top: -30px; left: 1em;" >
                    <textarea cols='80' rows='10' id='description' name='description'></textarea>
                    <?php echo getEditor("survey-desc", "description", "[" .  gT("Description:", "js") . "]", '', '', '', $action); ?>
                </div>
            </div>

            <div class="form-group">
                <label for='welcome' class="col-sm-2 control-label">
                    <?php  eT("Welcome message:"); ?>
                </label>
                <br/><br/>
                <div class='htmleditor col-sm-offset-2' style="position: relative; top: -30px; left: 1em;" >
                    <textarea cols='80' rows='10' id='welcome' name='welcome'></textarea>
                    <?php echo getEditor("survey-welc", "welcome", "[" .  gT("Welcome message:", "js") . "]", '', '', '', $action) ?>
                </div>
            </div>

            <div class="form-group">
                <label for='endtext' class="col-sm-2 control-label">
                    <?php  eT("End message:") ;?>
                </label>
                <br/><br/>
                <div class='htmleditor col-sm-offset-2' style="position: relative; top: -30px; left: 1em;" >
                    <textarea cols='80' id='endtext' rows='10' name='endtext'></textarea>
                    <?php echo getEditor("survey-endtext", "endtext", "[" .  gT("End message:", "js") . "]", '', '', '', $action) ?>
                </div>
            </div>

        </div>
    </div>

    <div class='col-lg-4'>
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
            <div class="panel panel-default" id="generaloptionsContainer">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#generaloptions" aria-expanded="true" aria-controls="generaloptions">
                    <?php eT("General options");?>
                    </a>
                    </h4>
                </div>

                <div id="generaloptions" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for='url'><?php  eT("End URL:"); ?></label>
                                <div class="col-sm-9">
                                    <input type='text' class="form-control"  id='url' name='url' placeholder="http://example.com"  />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for='urldescrip'><?php  eT("URL description:") ; ?></label>
                                <div class="col-sm-9">
                                    <input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value=''  class="form-control"  placeholder="<?php eT('Some description');?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for='dateformat'><?php  eT("Date format:") ; ?></label>
                                <div class="col-sm-3">
                                    <?php echo CHtml::listBox('dateformat',$sDateFormatDefault, $aDateFormatData, array('id'=>'dateformat','size'=>'1', 'class'=>'form-control')); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for='numberformat'><?php  eT("Decimal mark:"); ?></label>
                                <div class="col-sm-3">
                                    <?php echo CHtml::listBox('numberformat',$sRadixDefault, $aRadixPointData, array('id'=>'numberformat','size'=>'1', 'class'=>'form-control')); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for='admin'><?php  eT("Administrator:") ; ?></label>
                                <div class="col-sm-9">
                                    <input type='text' size='50' id='admin' name='admin'   class="form-control"  value='<?php echo $owner['full_name'] ; ?>' />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for='adminemail'><?php  eT("Admin email:") ; ?></label>
                                <div class="col-sm-9">
                                    <input type='email' size='30'   class="form-control"   id='adminemail' name='adminemail' value='<?php echo $owner['email'] ; ?>' />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for='bounce_email'><?php  eT("Bounce Email:") ; ?></label>
                                <div class="col-sm-9">
                                    <input type='email' size='50'  class="form-control"  id='bounce_email' name='bounce_email' value='<?php echo $owner['bounce_email'] ; ?>' />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label" for='faxto'><?php  eT("Fax to:") ; ?></label>
                                <div class="col-sm-9">
                                    <input type='text' size='50' id='faxto' name='faxto'  class="form-control" />
                                </div>
                            </div>
                    </div>
                </div>
            </div>


            <div class="panel panel-default">

                <div class="panel-heading" role="tab" id="headingTwo">
                    <h4 class="panel-title">
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#presentationoptions" aria-expanded="false" aria-controls="presentationoptions">
                      <?php  eT("Presentation & navigation"); ?>
                    </a>
                    </h4>
                </div>

                <div id="presentationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="presentationoptions">
                    <div class="panel-body">
                        <?php  eT("Presentation & navigation"); ?>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingThree">
                    <h4 class="panel-title">
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#publicationoptions" aria-expanded="false" aria-controls="publicationoptions">
                      <?php  eT("Publication & access control"); ?>
                    </a>
                    </h4>
                </div>

                <div id="publicationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="publicationoptions">
                    <div class="panel-body">
                        <?php  eT("Publication & access control"); ?>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingFour">
                    <h4 class="panel-title">
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#notificationoptions" aria-expanded="false" aria-controls="notificationoptions">
                        <?php  eT("Notification & data management"); ?>
                    </a>
                    </h4>
                </div>

                <div id="notificationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="notificationoptions">
                    <div class="panel-body">
                        <?php  eT("Notification & data management"); ?>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingFive">
                    <h4 class="panel-title">
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#tokensoptions" aria-expanded="false" aria-controls="tokensoptions">
                        <?php  eT("Tokens"); ?>
                    </a>
                    </h4>
                </div>

                <div id="tokensoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="tokensoptions">
                    <div class="panel-body">
                        <?php  eT("Tokens"); ?>
                    </div>
                </div>
            </div>

        </div>




    </div>
</div>
