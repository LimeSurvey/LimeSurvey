<?php
$yii = Yii::app();

if ($thissurvey['active'] == "Y")
            { ?>

                <script type='text/javascript'>
                      <!--
                        function saveshow(value)
                            {
                            if (document.getElementById(value).checked == true)
                                {
                                document.getElementById("closerecord").checked=false;
                                document.getElementById("closerecord").disabled=true;
                                document.getElementById("saveoptions").style.display="";
                                }
                            else
                                {
                                document.getElementById("saveoptions").style.display="none";
                                 document.getElementById("closerecord").disabled=false;
                                }
                            }
                      //-->
                      </script>
                <tr>
                <td colspan='3' align='center'>
                <table><tr><td align='left'>
                <div class="checkbox">
                    <input type='checkbox' class='checkboxbtn' name='closerecord' id='closerecord' checked='checked'/><label for='closerecord'><?php eT("Finalize response submission"); ?></label></td></tr>
                    <input type='hidden' name='closedate' value='<?php echo dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", $yii->getConfig('timeadjust')); ?>' />
                </div>
                <?php if ($thissurvey['allowsave'] == "Y")
                { ?>

                    <tr><td align='left'>
                        <div class="checkbox">
                            <input type='checkbox' class='checkboxbtn' name='save' id='save' onclick='saveshow(this.id)' />
                            <label for='save'><?php eT("Save for further completion by survey user"); ?></label>
                        </div>
                    </td></tr></table>
                    <div name='saveoptions' id='saveoptions' style='display: none' class=" ">

                        <!-- Save identifier -->
                        <div class="form-group">
                            <label class=" control-label" for="save_identifier"><?php eT("Identifier:"); ?></label>
                            <div class="">
                                <input class="form-control" type="text" size="50" id="save_identifier" name="save_identifier" value="">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class=" control-label" for="save_password"><?php eT("Password:"); ?></label>
                            <div class="">
                                <input class="form-control" type="password" size="50" id="save_password" name="save_password" value="">
                            </div>
                        </div>

                        <!-- Confirm password -->
                        <div class="form-group">
                            <label class=" control-label" for="save_confirmpassword"><?php eT("Confirm password:"); ?></label>
                            <div class="">
                                <input class="form-control" type="password" size="50" id="save_confirmpassword" name="save_confirmpassword" value="">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label class=" control-label" for="save_email"><?php eT("Email:"); ?></label>
                            <div class="">
                                <input class="form-control" type="email" size="50" id="save_email" name="save_email" value="">
                            </div>
                        </div>

                        <!-- Start language -->
                        <div class="form-group">
                            <label class=" control-label" for="save_language"><?php eT("Start language:"); ?></label>
                            <div class="">

                                <select name='save_language' id="save_language" class="form-control">
                                <?php foreach ($slangs as $lang)
                                {
                                if ($lang == $baselang) { ?>
                                  <option value='<?php echo $lang; ?>' selected='selected'><?php echo getLanguageNameFromCode($lang,false); ?></option>
                                  <?php }
                                else { ?>
                                  <option value='<?php echo $lang; ?>'><?php echo getLanguageNameFromCode($lang,false); ?></option>
                                  <?php }
                                } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    </td>
                    </tr>
                <?php } ?>
                <tr>
                <td colspan='3' align='center'>
                <input type='submit' id='submitdata' class="btn btn-default hidden" value='<?php eT("Submit"); ?>' />
                </td>
                </tr>
            <?php }
            elseif ($thissurvey['active'] == "N")
            { ?>
                <tr>
                <td colspan='3' align='center'>
                <font color='red'><strong><?php eT("This survey is not yet active. Your response cannot be saved"); ?>
                </strong></font></td>
                </tr>
            <?php }
            else
            { ?>
                </form>
                <tr>
                <td colspan='3' align='center'>";
                <font color='red'><strong><?php eT("Error"); ?></strong></font><br />
                <?php eT("The survey you selected does not exist"); ?><br /><br />
                <input type='submit' value='<?php eT("Main Admin Screen"); ?>' onclick="window.open('<?php echo $scriptname; ?>', '_top')" />
                </td>
                </tr>
                </table>
                <?php exit(); ?>
            <?php } ?>
            <tr>
            <td>
            <input type='hidden' name='subaction' value='insert' />
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
            <input type='hidden' name='language' value='<?php echo $sDataEntryLanguage; ?>' />
            </td>
            </tr>
            </table>
            </form>

            <?php if (tableExists('tokens_'.$thissurvey['sid'])): ?>
                <script>
                    // Token is mandatory, so disable save buttons
                    activateSubmit(null);
                </script>
            <?php endif; ?>

</div></div></div>
