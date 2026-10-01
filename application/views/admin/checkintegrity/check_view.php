<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('checkIntegrity');
?>

<div class="row">
    <div class="col-12">
        <div class="jumbotron message-box">
            <h2><?php eT("Data consistency check"); ?></h2>
            <p class="lead"><?php eT("Checks for orphaned or inconsistent data (questions, groups, conditions, quotas, sort orders, old survey/participant list tables with no records, ...) and fixes it automatically."); ?>
             <br><?php eT("Run it again if new errors keep appearing."); ?></p>

            <?php if (!empty($consistencyCheckRan)) { ?>
                <?php if (empty($consistencyCheckMessages) && empty($consistencyCheckWarnings)) { ?>
                    <?php
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'text' => gT("No errors were found."),
                        'type' => 'success',
                    ]);
                    ?>
                <?php } else { ?>
                    <?php
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'text' => gT("Errors were found and fixed automatically. See the log of fixes below for details."),
                        'type' => 'info',
                    ]);
                    ?>
                    <?php if (!empty($consistencyCheckWarnings)) { ?>
                        <?php
                        $warningList = '<ul>';
                        foreach ($consistencyCheckWarnings as $warning) {
                            $warningList .= '<li>' . $warning . '</li>';
                        }
                        $warningList .= '</ul>';
                        $this->widget('ext.AlertWidget.AlertWidget', [
                            'header' => gT('Warning'),
                            'text' => $warningList,
                            'type' => 'warning',
                        ]);
                        ?>
                    <?php } ?>
                    <details class="log-of-fixes mb-3 text-start d-inline-block">
                        <summary><?php eT("Log of fixes"); ?></summary>
                        <ul class="mt-2">
                            <?php foreach ($consistencyCheckMessages as $consistencyCheckMessage) { ?>
                                <li><?php echo $consistencyCheckMessage; ?></li>
                            <?php } ?>
                        </ul>
                    </details>
                <?php } ?>
            <?php } ?>

            <?php echo CHtml::form(["admin/checkintegrity", "sa" => 'fixintegrity'], 'post'); ?>
            <input type="hidden" name="ok" value="Y" />
            <button type="submit" class="btn btn-primary">
                <?php eT("Run data consistency check"); ?>
            </button>
            </form>
        </div>

        <!-- Data redundancy check -->
        <div class="jumbotron message-box">
            <h2><?php eT("Data redundancy check"); ?></h2>
            <p class="lead">
                <?php eT("The redundancy check looks for tables leftover after deactivating a survey. You can delete these if you no longer require them."); ?>
            </p>
            <p>
                <?php if ($redundancyok) { ?>
                    <?php
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'text' => gT("No database action required!"),
                        'type' => 'success',
                    ]);
                    ?>
                <?php } else { ?>
                    <?php echo CHtml::form(["admin/checkintegrity", 'sa' => 'fixredundancy'], 'post', ['id' => 'redundancy-check-form']); ?>
            <ul id="data-redundancy-list" class='data-redundancy-list list-unstyled'>
                    <?php
                    if (isset($redundantsurveytables)) { ?>
                    <li class="pb-2"><?php eT("The following old survey response tables exist and may be deleted if no longer required:"); ?>
                        <?php if (count($redundantsurveytables) > 1) { ?>
                            <div class='mb-2'>
                                <input
                                    type='checkbox'
                                    class='redundancy-group-toggle'
                                    id='check-all-response-tables'
                                    data-target-list='response-tables-list'
                                />
                                <label for='check-all-response-tables'>
                                    <?php printf(gT("Check all items in this group (%s)."), count($redundantsurveytables)); ?>
                                </label>
                            </div>
                        <?php } ?>
                        <ul class='response-tables-list list-unstyled'>
                                <?php
                                foreach ($redundantsurveytables as $surveytable) { ?>
                                <li>
                                    <input type='checkbox' id='cbox_<?php echo $surveytable['table'] ?>' value='<?php echo $surveytable['table'] ?>' name='oldsmultidelete[]' onclick="toggleDisableState(this)"/>
                                    <label for='cbox_<?php echo $surveytable['table'] ?>'><?php echo $surveytable['details'] ?></label>
                                </li>
                                    <?php
                                } ?>
                        </ul>
                    </li>
                        <?php
                    } ?>

                    <?php
                    if (isset($redundanttokentables) && count($redundanttokentables) > 0) { ?>
                    <li><?php eT("The following old participant lists exist and may be deleted if no longer required:"); ?>
                        <?php if (count($redundanttokentables) > 1) { ?>
                            <div class='mb-2'>
                                <input
                                    type='checkbox'
                                    class='redundancy-group-toggle'
                                    id='check-all-token-tables'
                                    data-target-list='token-tables-list'
                                />
                                <label for='check-all-token-tables'>
                                    <?php printf(gT("Check all items in this group (%s)."), count($redundanttokentables)); ?>
                                </label>
                            </div>
                        <?php } ?>
                        <ul class='token-tables-list list-unstyled'>
                                <?php
                                foreach ($redundanttokentables as $tokentable) { ?>
                                <li>
                                    <input type='checkbox' id='cbox_<?php echo $tokentable['table'] ?>' value='<?php echo $tokentable['table'] ?>' name='oldsmultidelete[]'/>
                                    <label for='cbox_<?php echo $tokentable['table'] ?>'><?php echo $tokentable['details'] ?></label>
                                </li>
                                    <?php
                                } ?>
                        </ul>
                    </li>
                        <?php
                    } ?>
            </ul>
            <div>
                <input type='hidden' name='ok' value='Y' />
                <button id='delete-checked-items-button' type='submit' name='ok' value='Y'
                        class="btn btn-danger mb-2"><?php
                        eT("Delete checked items!"); ?>
                </button>
            </div>
                    <?php
                    $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("Note that you cannot undo a delete if you proceed. The data will be gone."),
                    'type' => 'warning',
                    ]);
                    ?>
            </form><?php
                } ?>
        </div>
    </div>
</div>
