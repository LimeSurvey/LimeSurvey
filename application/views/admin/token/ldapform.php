<?php if (!empty($sError)) { ?>
    <strong><font color='red'><?php eT("Error"); ?></font>: <?php echo $sError; ?></strong><br /><br />
<?php } ?>

<?php if (!function_exists('ldap_connect')) { ?>
<p>
    <?php eT('Sorry, but the LDAP module is missing in your PHP configuration.'); ?><br />
</p>
<?php
}
elseif (empty($ldap_queries) || !is_array($ldap_queries) || count($ldap_queries) == 0)
{
?>
<br />
<?php eT('LDAP is disabled or no LDAP query defined.'); ?>
<br /><br /><br />
<?php
}
else
{
?>
<?php echo CHtml::form(array("admin/tokens/sa/importldap/surveyid/{$iSurveyId}"), 'post'); ?>
    <p>
        <?php eT("Select the LDAP query you want to run:"); ?> <select name='ldapQueries'>
        <?php foreach ($ldap_queries as $q_number => $q) { ?>
            <option value="<?php echo $q_number; ?>"><?php echo $q['name']; ?></option>
        <?php } ?>
        </select><br />
    </p>
    <p>
        <label for='filterblankemail'><?php echo eT("Filter blank email addresses:"); ?></label>
        <input type='checkbox' id='filterblankemail' name='filterblankemail' checked='checked' />
    </p>
    <p>
        <label for='filterduplicatetoken'><?php echo eT("Filter duplicate records:"); ?></label>
        <input type='checkbox' id='filterduplicatetoken' name='filterduplicatetoken' checked='checked' />
    </p>
    <input type='hidden' name='subaction' value='uploadldap' />
    <p><input type='submit' name='submit' /></p>
</form>
<?php
}
?>

<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo eT("Note"); ?></div><br />
    <?php eT("LDAP queries are defined by the administrator in the configuration file /application/config/ldap.php ."); ?>
</div>
