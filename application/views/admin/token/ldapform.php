<?php if (!empty($sError)) { ?>
    <strong><font color='red'><?php $clang->eT("Error"); ?></font>: <?php echo $sError; ?></strong><br /><br />
<?php } ?>

<?php if (!function_exists('ldap_connect')) { ?>
<p>
    <?php $clang->eT('Sorry, but the LDAP module is missing in your PHP configuration.'); ?><br />
</p>
<?php
}
elseif (empty($ldap_queries) || !is_array($ldap_queries) || count($ldap_queries) == 0)
{
?>
<br />
<?php $clang->eT('LDAP is disabled or no LDAP query defined.'); ?>
<br /><br /><br />
<?php
}
else
{
?>
<form method='post' action='<?php echo $this->createUrl("admin/tokens/sa/importldap/surveyid/$iSurveyId"); ?>' method='post'>
    <p>
        <?php $clang->eT("Select the LDAP query you want to run:"); ?><br />
        <select name='ldapQueries'>
        <?php foreach ($ldap_queries as $q_number => $q) { ?>
            <option value="<?php echo $q_number; ?>"><?php echo $q['name']; ?></option>
        <?php } ?>
        </select><br />
    </p>
    <p>
        <label for='filterblankemail'><?php echo $clang->eT("Filter blank email addresses:"); ?></label>
        <input type='checkbox' id='filterblankemail' checked='checked' />
    </p>
    <p>
        <label for='filterduplicatetoken'><?php echo $clang->eT("Filter duplicate records:"); ?></label>
        <input type='checkbox' id='filterduplicatetoken' checked='checked' />
    </p>
    <input type='hidden' name='subaction' value='uploadldap' />
    <p><input type='submit' name='submit' /></p>
</form>
<?php
}
?>

<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo $clang->eT("Note"); ?></div><br />
    <?php $clang->eT("LDAP queries are defined by the administrator in the config-ldap.php file."); ?>
</div>
