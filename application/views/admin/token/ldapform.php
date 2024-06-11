<?php
/**
 * Import tokens from LDAP
 */
?>

<div class='side-body'>
    <h3><?php eT("Import survey participants from LDAP"); ?></h3>

    <div class="row">
        <div class="col-12 content-right">

            <!-- Alert error -->
            <?php if (!empty($sError)): ?>
                <?php
                $message = '<strong>' . gT("Error") . '</strong>: ' .  $sError;
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => $message,
                    'type' => 'danger',
                ]);
                ?>
            <?php endif; ?>

            <!-- LDAP module is missing -->
            <?php if (!function_exists('ldap_connect')): ?>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT('Sorry, but the LDAP module is missing in your PHP configuration.'),
                    'type' => 'danger',
                ]);
                ?>
            <?php elseif (empty($ldap_queries) || !is_array($ldap_queries) || count($ldap_queries) == 0): ?>
                <br />
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT('LDAP is disabled or no LDAP query defined.'),
                    'type' => 'danger',
                ]);
                ?>
            <br /><br /><br />
            <?php else: ?>

            <!-- Form -->
                <?php echo CHtml::form(array("admin/tokens/sa/importldap/surveyid/{$iSurveyId}"), 'post', array('class'=>'')); ?>

                    <!-- LDAP query  -->
                    <div class="mb-3">
                        <label for="ldapQueries" class=" form-label">
                            <?php eT("Select the LDAP query you want to run:"); ?>
                        </label>
                        <div class="">
                            <select name='ldapQueries' class="form-select">
                                <?php 
                                uasort ( $ldap_queries , function ($a, $b) {
                                    return strnatcmp((string) $a['name'],(string) $b['name']); // or other function/code
                                    }
                                );                                      
                                foreach ($ldap_queries as $q_number => $q): ?>
                                    <option value="<?php echo $q_number; ?>"><?php echo $q['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Filter blank email -->
                    <div class="mb-3">
                        <label for='filterblankemail' class=" form-label"><?php echo eT("Filter blank email addresses:"); ?></label>
                        <div class="">
                            <input type='checkbox' id='filterblankemail' name='filterblankemail' checked='checked' />
                        </div>
                    </div>

                    <!-- Filter duplicate -->
                    <div class="mb-3">
                        <label for='filterduplicatetoken'  class=" form-label"><?php echo eT("Filter duplicate records:"); ?></label>
                        <div class="">
                            <input type='checkbox' id='filterduplicatetoken' name='filterduplicatetoken' checked='checked' />
                        </div>
                    </div>

                    <!-- Upload button -->
                    <input type='hidden' name='subaction' value='uploadldap' />
                    <p><input type='submit' class="btn btn-outline-secondary" name='submit' value='<?php eT('Upload');?>' /></p>
                </form>
            <?php endif; ?>

            <!-- Note -->
            <?php
            $message = '<strong>' . gT("Note") . '</strong>: ' .  gT("LDAP queries are defined by the administrator in the configuration file /application/config/ldap.php .");
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => $message,
                'type' => 'info',
            ]);
            ?>
        </div>
    </div>
</div>
