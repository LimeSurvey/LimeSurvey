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
            <input type='submit' class="btn btn-default btn-large" value='<?php eT("Main Admin Screen"); ?>' onclick="window.open('<?php echo $this->createUrl('admin'); ?>', '_top')" />
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
    <div class="jumbotron message-box ">
        <h2 class="text-success"><?php eT("Success"); ?></h2>

        <p class="lead"><?php echo $sSummaryHeader; ?></p>

        <!-- Import result messages -->
        <p>
            <ul class="list-unstyled">
                <li><?php eT("Surveys");?>: <?php echo $aImportResults['surveys'];?></li>
                <li><?php eT("Languages");?>: <?php echo $aImportResults['languages'];?></li>
                <li><?php eT("Question groups");?>: <?php echo $aImportResults['groups'];?></li>
                <li><?php eT("Questions");?>: <?php echo $aImportResults['questions'];?></li>
                <li><?php eT("Question attributes");?>: <?php echo $aImportResults['question_attributes'];?></li>
                <li><?php eT("Answers");?>: <?php echo $aImportResults['answers'];?></li>
                <?php if (isset($aImportResults['subquestions']))
                    {?>
                    <li><?php eT("Subquestions");?>: <?php echo $aImportResults['subquestions'];?></li>
                    <?php }
                    if (isset($aImportResults['defaultvalues']))
                    {?>
                    <li><?php eT("Default answers");?>: <?php echo $aImportResults['defaultvalues'];?></li>
                    <?php }
                    if (isset($aImportResults['conditions']))
                    {?>
                    <li><?php eT("Condition");?>: <?php echo $aImportResults['conditions'];?></li>
                    <?php }
                    if (isset($aImportResults['labelsets']))
                    {?>
                    <li><?php eT("Label sets");?>: <?php echo $aImportResults['labelsets'];?></li>
                    <?php }
                    if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls']>0)
                    {?>
                    <li><?php eT("Not imported label sets");?>: <?php echo $aImportResults['deniedcountls'];?> <?php eT("(Label sets were not imported since you do not have the permission to create label sets.)"); ?> </li>
                    <?php }?>
                <li><?php eT("Assessments");?>: <?php echo $aImportResults['assessments'];?></li>
                <li><?php eT("Quotas");?>: <?php echo $aImportResults['quota'];?> (<?php echo $aImportResults['quotamembers']?> <?php eT("quota members");?> <?php eT("and");?> <?php echo $aImportResults['quotals']?> <?php eT("quota language settings"); ?></li>
            </ul>
        </p>

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