<?php
/**
 * Publication Panel
 */
?>
<!-- Publication panel -->
<div id='publication' class="tab-pane fade in">
    
    <!-- List survey publicly -->
    <div class="form-group">
        <label class="col-sm-4 control-label" for='public'><?php  eT("List survey publicly:");?></label>
        <div class="col-sm-8">
            <select id='public' name='public'  class="form-control">
                <option value='Y'
                    <?php if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") { ?>
                  selected='selected'
                    <?php } ?>
                    ><?php  eT("Yes"); ?>
                </option>
                <option value='N'
                    <?php if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") { ?>
                  selected='selected'
                    <?php } ?>
                 ><?php  eT("No"); ?>
                </option>
            </select>                    
        </div>
    </div>                                                      

    <!-- Start date/time -->
    <div class="form-group">
        <label class="col-sm-4 control-label" for='startdate'><?php  eT("Start date/time:"); ?></label>
        <div class="col-sm-8">
            <input type='text' class='popupdatetime' id='startdate' size='20' name='startdate' value="<?php echo $startdate; ?>"  class="form-control" />
        </div>
    </div>    

    <!-- Expiry date/time -->
    <div class="form-group">
        <label class="col-sm-4 control-label" for='expires'><?php  eT("Expiry date/time:"); ?></label>
        <div class="col-sm-8">
            <input type='text' class='popupdatetime' id='expires' size='20' name='expires' value="<?php echo $expires; ?>"  class="form-control" />                
        </div>
    </div>    

    <!-- Set cookie to prevent repeated participation -->
    <div class="form-group">
        <label class="col-sm-4 control-label" for='usecookie'><?php  eT("Set cookie to prevent repeated participation?"); ?></label>
        <div class="col-sm-8">
            <select name='usecookie' id='usecookie'  class="form-control">
                <option value='Y'
                        <?php if ($esrow['usecookie'] == "Y") { ?>
                         selected='selected'
                        <?php } ?>
                        ><?php  eT("Yes"); ?>
                </option>
                <option value='N'
                        <?php if ($esrow['usecookie'] != "Y") { ?>
                         selected='selected'
                           <?php } ?>
                        ><?php  eT("No"); ?>
                </option>
            </select>

        </div>
    </div>    

    <!-- Use CAPTCHA for -->
    <div class="form-group">
        <label class="col-sm-4 control-label" for='usecaptcha'><?php  eT("Use CAPTCHA for"); ?>:</label>
        <div class="col-sm-8">
            <select name='usecaptcha' id='usecaptcha'  class="form-control" >
                <option value='A'
                <?php if ($esrow['usecaptcha'] == "A") { ?>
                      selected='selected'
                <?php } ?>
                     ><?php  eT("Survey Access"); ?> / <?php  eT("Registration"); ?> / <?php echo gT("Save & Load"); ?></option>
                <option value='B'
                <?php if ($esrow['usecaptcha'] == "B") { ?>
                      selected='selected'
                <?php } ?>
                
                     ><?php  eT("Survey Access"); ?> / <?php  eT("Registration"); ?> / ---------</option>
                <option value='C'
                <?php if ($esrow['usecaptcha'] == "C") { ?>
                      selected='selected'
                <?php } ?>
                
                     ><?php  eT("Survey Access"); ?> / ------------ / <?php  eT("Save & Load"); ?></option>
                <option value='D'
                <?php if ($esrow['usecaptcha'] == "D") { ?>
                      selected='selected'
                <?php } ?>
                
                     >------------- / <?php  eT("Registration"); ?> / <?php  eT("Save & Load"); ?></option>
                <option value='X'
                
                <?php if ($esrow['usecaptcha'] == "X") { ?>
                      selected='selected'
                <?php } ?>
                
                     ><?php  eT("Survey Access"); ?> / ------------ / ---------</option>
                <option value='R'
                <?php if ($esrow['usecaptcha'] == "R") { ?>
                      selected='selected'
                <?php } ?>
                     >------------- / <?php  eT("Registration"); ?> / ---------</option>
                <option value='S'
                <?php if ($esrow['usecaptcha'] == "S") { ?>
                      selected='selected'
                <?php } ?>
                     >------------- / ------------ / <?php  eT("Save & Load"); ?></option>
                <option value='N'
                <?php if ($esrow['usecaptcha'] == "N") { ?>
                      selected='selected'";
                <?php } ?>
                     >------------- / ------------ / ---------</option>
            </select>
        </div>
    </div>    
</div>
