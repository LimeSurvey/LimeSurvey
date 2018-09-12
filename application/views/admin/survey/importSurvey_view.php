<?php
/**
 * Render the result of the import survey action
 */
 ?>


<!-- Import Failed -->
<?php if ($bFailed):?>

    <div class="jumbotron message-box message-box-error">
        <h2 class="danger"><?php echo $sHeader;?></h2>
        <p class="lead danger">
            <?php eT("Error"); ?>
        </p>
        <!-- error message -->
        <p>
        <?php echo $sErrorMessage; ?>
        </p>
        <!-- buttons -->
        <p>
            <input type='submit' class="btn btn-default btn-large" value='<?php eT("Main Admin Screen"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/'); ?>', '_top')" />
        </p>
    </div>

<!-- Import success but errors -->
<?php elseif ( isset($aImportResults['error']) && $aImportResults['error']!=false ): ?>

    <div class="jumbotron message-box message-box-error">
        <h2 class="text-success"> <?php eT("Success"); ?></h2>
        <p class="lead"><?php eT("File upload succeeded.");?> </p>
            <h2 class="warning"><?php eT("Error");?></h2>
            <!-- errors -->
            <?php
            if(is_array($aImportResults['error']))
            {
                foreach($aImportResults['error'] as $error)
                    echo '<p>'.$error."<p/>";
            }
            else
            {
                echo '<p>'.$aImportResults['error'].'</p>';
            }
            ?>

            <!-- buttons -->
            <p>
                <input type='submit' value='<?php eT("Main Admin Screen");?>' class="btn btn-large btn-default" onclick="window.open('<?php echo $this->createUrl('/admin');?>', '_top')" />
            </p>

    </div>

<!-- Success -->
<?php else:?>
    <div class="jumbotron message-box">
        <h2 class="text-success"><?php eT("Success"); ?></h2>

        <p class="lead"><?php echo $sSummaryHeader; ?></p>

        <!-- Import result messages -->
        <div class="container-fluid">
            <div class="row center-block">
            <div class="col-md-2 col-md-offset-5">
            <table class="table table-striped table-condensed ">
                <tr><td><?php eT("Surveys");?>:</td><td><?php echo $aImportResults['surveys'];?></td></tr>
                <tr><td><?php eT("Languages");?>:</td><td><?php echo $aImportResults['languages'];?></td></tr>
                <tr><td><?php eT("Question groups");?>:</td><td><?php echo $aImportResults['groups'];?></td></tr>
                <tr><td><?php eT("Questions");?>:</td><td><?php echo $aImportResults['questions'];?></td></tr>
                <tr><td><?php eT("Question attributes");?>:</td><td><?php echo $aImportResults['question_attributes'];?></td></tr>
                <tr><td><?php eT("Answers");?>:</td><td><?php echo $aImportResults['answers'];?></td></tr>
                <?php if (isset($aImportResults['subquestions']))
                    {?>
                    <tr><td><?php eT("Subquestions");?>:</td><td><?php echo $aImportResults['subquestions'];?></td></tr>
                    <?php }
                    if (isset($aImportResults['defaultvalues']))
                    {?>
                    <tr><td><?php eT("Default answers");?>:</td><td><?php echo $aImportResults['defaultvalues'];?></td></tr>
                    <?php }
                    if (isset($aImportResults['conditions']))
                    {?>
                    <tr><td><?php eT("Condition");?>:</td><td><?php echo $aImportResults['conditions'];?></td></tr>
                    <?php }
                    if (isset($aImportResults['labelsets']))
                    {?>
                    <tr><td><?php eT("Label sets");?>:</td><td><?php echo $aImportResults['labelsets'];?></td></tr>
                    <?php }
                    if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls']>0)
                    {?>
                    <tr><td><?php eT("Not imported label sets");?>:</td><td><?php echo $aImportResults['deniedcountls'];?> <?php eT("(Label sets were not imported since you do not have the permission to create label sets.)"); ?> </td></tr>
                    <?php }?>
                <tr><td><?php eT("Assessments");?>:</td><td><?php echo $aImportResults['assessments'];?></td></tr>
                <tr><td><?php eT("Quotas");?>:</td><td><?php echo $aImportResults['quota'];?></td></tr>
                <tr><td><?php eT("Quota members:");?></td><td><?php echo $aImportResults['quotamembers'];?></td></tr>
                <tr><td><?php eT("Quota language settings:");?></td><td><?php echo $aImportResults['quotals'];?></td></tr>
            </table>
            </div>
            </div>
            </div>


        <!-- Response import summary -->
        <?php if (isset($aImportResults['responses'])): ?>
            <strong><?php eT("Response import summary"); ?></strong><br />
            <ul class="list-unstyled">
                <li><?php eT("Responses");?>: <?php echo $aImportResults['responses'];?></li>
            </ul>
        <?php endif;?>

        <!-- Warnings -->
        <?php if (count($aImportResults['importwarnings'])>0): ?>
            <h2 class="warning"><?php eT("Warnings");?>:</h2>
            <ul  class="list-unstyled">
                <?php
                    foreach ($aImportResults['importwarnings'] as $warning)
                    { ?>
                    <li><?php echo $warning; ?></li>
                    <?php
                } ?>
            </ul>
        <?php endif; ?>

        <!-- Import Result -->
        <?php if ($action == 'importsurvey'): ?>
            <p class="text-info"><?php eT("Import of survey is completed.");?></p>
        <?php elseif($action == 'copysurvey'): ?>
            <p class="text-info"><?php eT("Copy of survey is completed.");?></p>
        <?php endif; ?>

        <!-- Buttons -->
        <p>
            <input type='submit' class="btn btn-default btn-large" value='<?php eT("Go to survey");?>' onclick="window.open('<?php echo $sLink; ?>', '_top')"><br /><br />
        </p>
    </div>
<?php endif;?>
