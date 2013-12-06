<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Token control"); ?> </strong> <?php echo htmlspecialchars($thissurvey['surveyls_title']); ?>
    </div></div><div class='messagebox ui-corner-all'>
    <div class='warningheader'><?php $clang->eT("Warning"); ?></div>
    <br /><strong><?php $clang->eT("Tokens have not been initialised for this survey."); ?></strong><br /><br />
    <?php
        if (Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update') || Permission::model()->hasSurveyPermission($surveyid, 'tokens','create'))
        {
            $clang->eT("If you initialise tokens for this survey then this survey will only be accessible to users who provide a token either manually or by URL.");
        ?><br /><br />

        <?php
            if ($thissurvey['anonymized'] == 'Y')
            {
                $clang->eT("Note: If you turn on the -Anonymized responses- option for this survey then LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.");
            ?><br /><br />
            <?php
            }
            $clang->eT("Do you want to create a token table for this survey?");
        ?>
        <br /><br />
        <input type='submit' value='<?php $clang->eT("Initialise tokens"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/$surveyid/createtable/Y"); ?>', '_top')" />
        <input type='submit' value='<?php $clang->eT("No, thanks."); ?>' onclick="window.open('<?php echo$this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>', '_top')" /></div>
    <?php
    }
    else
    {
        $clang->eT("You don't have the permission to activate tokens.");
    ?>
    <input type='submit' value='<?php $clang->eT("Back to main menu"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>', '_top')" /></div>

    <?php
    }

    // Do not offer old postgres token tables for restore since these are having an issue with missing index
    if ($tcount > 0 && (Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update') || Permission::model()->hasSurveyPermission($surveyid, 'tokens','create')))
    {
    ?>
    <br /><div class='header ui-widget-header'><?php $clang->eT("Restore options"); ?></div>
    <div class='messagebox ui-corner-all'>
        <?php echo CHtml::form(array("admin/tokens/sa/index/surveyid/{$surveyid}"), 'post'); ?>
            <?php $clang->eT("The following old token tables could be restored:"); ?><br /><br />
            <select size='4' name='oldtable' style='width:250px;'>
                <?php
                    foreach ($oldlist as $ol)
                    {
                        echo "<option>" . $ol . "</option>\n";
                    }
                ?>
            </select><br /><br />
            <input type='submit' value='<?php $clang->eT("Restore"); ?>' />
            <input type='hidden' name='restoretable' value='Y' />
            <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
        </form></div>
    <?php } ?>
<script type="text/javascript">
    <!--

    function addHiddenElement(theform,thename,thevalue)
    {
        var myel = document.createElement('input');
        myel.type = 'hidden';
        myel.name = thename;
        theform.appendChild(myel);
        myel.value = thevalue;
        return myel;
    }

    function sendPost(myaction,checkcode,arrayparam,arrayval)
    {
        var myform = document.createElement('form');
        document.body.appendChild(myform);
        myform.action =myaction;
        myform.method = 'POST';
        for (i=0;i<arrayparam.length;i++)
            {
            addHiddenElement(myform,arrayparam[i],arrayval[i])
        }
        myform.submit();
    }

    //-->
</script>
