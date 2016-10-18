<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row welcome survey-action">
        <div class="col-sm-12 content-right">
            <div class="jumbotron message-box message-box-error">
                <p class="lead text-warning">
                    <strong>
                        <?php eT("Survey participants have not been initialised for this survey."); ?>
                    </strong>
                </p>
                <p>

                    <?php
                        if (Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update') || Permission::model()->hasSurveyPermission($surveyid, 'tokens','create'))
                        {
                            eT("If you initialise a survey participant table for this survey then this survey will only be accessible to users who provide a token either manually or by URL.");
                        ?><br /><br />

                        <?php
                            if ($thissurvey['anonymized'] == 'Y')
                            {
                                eT("Note: If you turn on the -Anonymized responses- option for this survey then LimeSurvey will mark participants who complete the survey only with a 'Y' instead of date/time to ensure the anonymity of your participants.");
                            ?><br /><br />
                            <?php
                            }
                            eT("Do you want to create a survey participant table for this survey?");
                        ?>
                        <br /><br />

                        <?php echo CHtml::form(array("admin/tokens/sa/index/surveyid/{$surveyid}"), 'post'); ?>
                            <button type="submit" class="btn btn-default  btn-lg"  name="createtable" value="Y"><?php eT("Initialise participant table"); ?></button>
                            <a href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>" class="btn btn-default  btn-lg"><?php eT("No, thanks."); ?></a>
                        </form>


                    <?php
                    }
                    else
                    {
                        eT("You don't have the permission to activate tokens.");
                    ?>
                    <input type='submit' value='<?php eT("Back to main menu"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>', '_top')" /></div>

                    <?php
                    }
                    ?>
                </p>
            </div>
        </div>

<?php
// Do not offer old postgres token tables for restore since these are having an issue with missing index
if ($tcount > 0 && (Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update') || Permission::model()->hasSurveyPermission($surveyid, 'tokens','create'))):
?>
        <div class="col-sm-12 content-right">
            <div class="jumbotron message-box">
                <h2><?php eT("Restore options"); ?></h2>
                <p class="lead text-success">
                    <strong>
                        <?php eT("The following old token tables could be restored:"); ?>
                    </strong>
                </p>
                <p>
                    <?php echo CHtml::form(array("admin/tokens/sa/index/surveyid/{$surveyid}"), 'post'); ?>
                        <select size='4' name='oldtable'>
                            <?php
                                foreach ($oldlist as $ol)
                                {
                                    echo "<option>" . $ol . "</option>\n";
                                }
                            ?>
                        </select><br /><br />
                        <input type='submit' value='<?php eT("Restore"); ?>' class="btn btn-default btn-lg"/>
                        <input type='hidden' name='restoretable' value='Y' />
                        <input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
                    </form>
                </p>
            </div>
        </div>
<?php endif;?>

</div>
</div>


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
