<div class='side-body'>
<div class="col-12 list-surveys">
    <div class="row">
        <div class="col-12 content-right">

    <div class="jumbotron message-box <?php if($errormsg){echo 'message-box-error';}?>">

        <?php if($errormsg): ?>
                <h2 class="danger"><?php eT("Try again"); ?></h2>
                <p>
                    <?php
                        foreach($dataentrymsgs as $msg)
                        {
                            echo $msg . "<br />\n";
                        }
                    ?>
                </p>
                <p>
                    <?php echo $errormsg; ?>
                </p>
                <p>
                    <input type='submit' class="btn btn-lg  btn-outline-secondary" value='<?php eT("Add another record"); ?>' onclick="window.open('<?php echo $this->createUrl('/admin/dataentry/sa/view/surveyid/'.$surveyid.'/lang/'.$lang); ?>', '_top')" />
                    <br /><br />
                    <input type='submit' class="btn btn-lg  btn-outline-secondary" value='<?php eT("Return to survey administration"); ?>' onclick="window.open('<?php echo $this->createUrl('surveyAdministration/view/surveyid/'.$surveyid); ?>', '_top')" />
                    <br /><br />
                </p>
                <p>
                    <?php if(isset($save)): ?>
                        <input type='submit' class="btn btn-lg btn-outline-secondary" value='<?php eT("Browse saved responses"); ?>' onclick="window.open('<?php echo $this->createUrl('/admin/saved/sa/view/surveyid/'.$surveyid.'/all'); ?>', '_top')" />
                        <br /><br />
                    <?php endif; ?>
                </p>
        <?php else:?>

                <!-- SUCCESS -->

                <h2 class="success"><?php eT("Success"); ?></h2>
                <p>
                    <?php
                        foreach($dataentrymsgs as $msg)
                        {
                            echo $msg . "<br />\n";
                        }
                    ?>
                </p>
                <p>
                    <?php if(isset($thisid)): ?>
                        <?php echo gT("The entry was assigned the following record id: ")."{$thisid}"; ?> <br /><br />
                    <?php endif; ?>
                </p>
                <p>
                    <input type='submit' class="btn btn-lg btn-outline-secondary" value='<?php eT("Add another record"); ?>' onclick="window.open('<?php echo $this->createUrl('/admin/dataentry/sa/view/surveyid/'.$surveyid.'/lang/'.$lang); ?>', '_top')" />
                    <br /><br />
                    <input type='submit' class="btn btn-lg btn-outline-secondary" value='<?php eT("Return to survey administration"); ?>' onclick="window.open('<?php echo $this->createUrl('surveyAdministration/view/surveyid/'.$surveyid); ?>', '_top')" />
                    <br /><br />

                    <?php if(isset($thisid) && Permission::model()->hasSurveyPermission($surveyid, 'responses','read')): ?>
                        <input type='submit' class="btn btn-lg btn-outline-secondary" value='<?php eT("View this record"); ?>' onclick="window.open('<?php echo $this->createUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $thisid]); ?>', '_top')" />
                        <br /><br />
                    <?php endif; ?>

                    <?php if(isset($save)): ?>
                        <input type='submit' class="btn btn-lg btn-outline-secondary" value='<?php eT("Browse saved responses"); ?>' onclick="window.open('<?php echo $this->createUrl('/admin/saved/sa/view/surveyid/'.$surveyid.'/all'); ?>', '_top')" />
                        <br /><br />
                    <?php endif; ?>
                </p>
        <?php endif;?>

</div>

</div></div></div>
</div>
