<?php
/**
 * Render the result of the import survey action
 */
 ?>


<!-- Import Failed because of page reload -->
<?php if (!isset($bFailed)):?>

    <div class="jumbotron message-box message-box-error">
        <h2 class="danger"><?php echo gT("Import survey data"); ?></h2>
        <p class="lead text-danger">
            <?php eT("Error"); ?>
        </p>
        <!-- error message -->
        <p>
        <?php echo eT('Import failed because of page reload.'); ?>
        </p>
        <!-- buttons -->
        <p>
            <input type='submit' class="btn btn-outline-secondary btn-large" value='<?php eT("Main Admin Screen"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/'); ?>', '_top')" />
        </p>
    </div>

<!-- Import Failed -->
<?php elseif ($bFailed):?>

    <div class="jumbotron message-box message-box-error">
        <h2 class="danger"><?php echo $sHeader;?></h2>
        <p class="lead text-danger">
            <?php eT("Error"); ?>
        </p>
        <!-- error message -->
        <p>
        <?php echo $sErrorMessage; ?>
        </p>
        <!-- buttons -->
        <p>
            <input type='submit' class="btn btn-outline-secondary btn-large" value='<?php eT("Main Admin Screen"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/'); ?>', '_top')" />
        </p>
    </div>

<!-- Success -->
<?php else:?>
    <div class="jumbotron message-box">
        <h2 class="text-success"><?php eT("Success"); ?></h2>

        <p class="lead"><?php echo $sSummaryHeader; ?></p>

        <!-- Import result messages -->
        <div class="row justify-content-center">
            <div class="col-lg-2">
                <table class="table table-striped table-condensed ">
                    <tr>
                        <td><?php eT("Surveys"); ?>:</td>
                        <td><?php echo $aImportResults['surveys']; ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Languages"); ?>:</td>
                        <td><?php echo $aImportResults['languages']; ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Question groups"); ?>:</td>
                        <td><?php echo $aImportResults['groups']; ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Questions"); ?>:</td>
                        <td><?php echo $aImportResults['questions']; ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Question attributes"); ?>:</td>
                        <td><?php echo $aImportResults['question_attributes']; ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Answers"); ?>:</td>
                        <td><?php echo $aImportResults['answers']; ?></td>
                    </tr>
                    <?php if (isset($aImportResults['subquestions'])) {
                        ?>
                        <tr>
                            <td><?php eT("Subquestions"); ?>:</td>
                            <td><?php echo $aImportResults['subquestions']; ?></td>
                        </tr>
                    <?php }
                    if (isset($aImportResults['defaultvalues'])) {
                        ?>
                        <tr>
                            <td><?php eT("Default answers"); ?>:</td>
                            <td><?php echo $aImportResults['defaultvalues']; ?></td>
                        </tr>
                    <?php }
                    if (isset($aImportResults['conditions'])) {
                        ?>
                        <tr>
                            <td><?php eT("Condition"); ?>:</td>
                            <td><?php echo $aImportResults['conditions']; ?></td>
                        </tr>
                    <?php }
                    if (isset($aImportResults['labelsets'])) {
                        ?>
                        <tr>
                            <td><?php eT("Label sets"); ?>:</td>
                            <td><?php echo $aImportResults['labelsets']; ?></td>
                        </tr>
                    <?php }
                    if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls'] > 0) {
                        ?>
                        <tr>
                            <td><?php eT("Not imported label sets"); ?>:</td>
                            <td><?php echo $aImportResults['deniedcountls']; ?><?php eT("(Label sets were not imported since you do not have the permission to create label sets.)"); ?> </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td><?php eT("Assessments"); ?>:</td>
                        <td><?php echo $aImportResults['assessments']; ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Quotas"); ?>:</td>
                        <td><?php echo $aImportResults['quota']; ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Quota members:"); ?></td>
                        <td><?php echo $aImportResults['quotamembers']; ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Quota language settings:"); ?></td>
                        <td><?php echo $aImportResults['quotals']; ?></td>
                    </tr>
                    <?php if (!empty($aImportResults['plugin_settings'])) { ?>
                        <tr>
                            <td><?php eT("Plugin settings:"); ?></td>
                            <td><?php echo $aImportResults['plugin_settings']; ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td><?php eT("Themes:"); ?></td>
                        <td><?php echo $aImportResults['themes']; ?></td>
                    </tr>
                </table>
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
            <input type='submit' class="btn btn-outline-secondary btn-large" value='<?php eT("Go to survey");?>' onclick="window.open('<?php echo $sLink; ?>', '_top')"><br /><br />
        </p>

        <!-- Theme doesn't exist -->
        <?php if (!empty($aImportResults['template_deleted'])): ?>
            <p class="lead"><?php echo eT("Warning: original survey theme doesn't exist!"); ?></p>
        <?php endif; ?>

        <!-- Theme options differences warnings -->
        <?php if (!empty($aImportResults['theme_options_differences'])): ?>
            <p class="lead"><?php echo eT('Warning: There are some differences between current theme options and original theme options!'); ?></p>
            <p class="lead"><?php echo eT('Current theme options are applied for this survey.'); ?></p>
            <h2 class="warning"><?php eT("Theme options differences"); ?>:</h2>
                <div class="row justify-content-center">
                    <div class="col-lg-4">
                        <table class="table table-striped table-condensed ">
                            <tr>
                                <th class="text-center"><?php echo gT('Option'); ?></th>
                                <th class="text-center"><?php echo gT('Current value'); ?></th>
                                <th class="text-center"><?php echo gT('Original value'); ?></th>
                            </tr>
                            <?php
                            foreach ($aImportResults['theme_options_differences'] as $warning) { ?>
                                <tr>
                                    <td><?php echo $warning['option']; ?></td>
                                    <td><?php echo $warning['current_value']; ?></td>
                                    <td><?php echo $warning['original_value']; ?></td>
                                </tr>
                                <?php
                            } ?>
                        </table>
                    </div>
                </div>

            <?php echo CHtml::form([$sLinkApplyThemeOptions], 'post', []); ?>
            <label><?php echo eT('If you want to apply original theme options, click here: '); ?></label>
            <input type="hidden" name="themeoptions" value='<?php echo $aImportResults['theme_options_original_data']; ?>'/>
            <input type="submit" class="btn btn-outline-secondary btn-large" value="<?php eT("Apply and go to survey"); ?>"><br/><br/>
            </form>

        <?php endif; ?>
    </div>
<?php endif;?>
