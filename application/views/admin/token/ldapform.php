<?php
/**
 * Import tokens from LDAP
 */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'token'=>true, 'active'=>gT("Import survey participants from LDAP"))); ?>
    <h3><?php eT("Import survey participants from LDAP"); ?></h3>

    <div class="row">
        <div class="col-lg-12 content-right">

            <!-- Alert error -->
            <?php if (!empty($sError)): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong><?php eT("Error"); ?></strong>: <?php echo $sError; ?>
                </div>
            <?php endif; ?>

            <!-- LDAP module is missing -->
            <?php if (!function_exists('ldap_connect')): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <?php eT('Sorry, but the LDAP module is missing in your PHP configuration.'); ?>
                </div>
            <?php elseif (empty($ldap_queries) || !is_array($ldap_queries) || count($ldap_queries) == 0): ?>
                <br />
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <?php eT('LDAP is disabled or no LDAP query defined.'); ?>
                </div>
            <br /><br /><br />
            <?php else: ?>

            <!-- Form -->
                <?php echo CHtml::form(array("admin/tokens/sa/importldap/surveyid/{$iSurveyId}"), 'post', array('class'=>'form-horizontal')); ?>

                    <!-- LDAP query  -->
                    <div class="form-group">
                        <label for="ldapQueries" class="col-sm-3 control-label">
                            <?php eT("Select the LDAP query you want to run:"); ?>
                        </label>
                        <div class="col-sm-2">
                            <select name='ldapQueries' class="form-control">
                                <?php foreach ($ldap_queries as $q_number => $q): ?>
                                    <option value="<?php echo $q_number; ?>"><?php echo $q['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Filter blank email -->
                    <div class="form-group">
                        <label for='filterblankemail' class="col-sm-3 control-label"><?php echo eT("Filter blank email addresses:"); ?></label>
                        <div class="col-sm-9">
                            <input type='checkbox' id='filterblankemail' name='filterblankemail' checked='checked' />
                        </div>
                    </div>

                    <!-- Filter duplicate -->
                    <div class="form-group">
                        <label for='filterduplicatetoken'  class="col-sm-3 control-label"><?php echo eT("Filter duplicate records:"); ?></label>
                        <div class="col-sm-9">
                            <input type='checkbox' id='filterduplicatetoken' name='filterduplicatetoken' checked='checked' />
                        </div>
                    </div>

                    <!-- Upload button -->
                    <input type='hidden' name='subaction' value='uploadldap' />
                    <p><input type='submit' class="btn btn-default" name='submit' value='<?php eT('Upload');?>' /></p>
                </form>
            <?php endif; ?>

            <!-- Note -->
            <div class="alert alert-info alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button
                <strong><?php echo eT("Note:"); ?></strong> <?php eT("LDAP queries are defined by the administrator in the configuration file /application/config/ldap.php ."); ?>
            </div>

        </div>
    </div>
</div>
