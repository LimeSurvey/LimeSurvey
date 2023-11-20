<?php
/** @var $recordcount */
/** @var $duplicatelist */
/** @var $mincriteria */
/** @var $imported */
/** @var $errorinupload */
/** @var $invalidemaillist */
/** @var $aInvalidFormatlist */
/** @var $mandatory */
/** @var $invalidattribute */
/** @var $invalidparticipantid */
/** @var $overwritten */
/** @var $dupreason */

$successSummary = '';
$infoSummary = '';
$warningSummary = '';
$errorSummary = '';
$alerts = '';
if (empty($errorinupload)) {
    $successSummary .= gT('Uploaded CSV file successfully');
    if ($imported != 0) {
        $successSummary .= "<br/>" . gT('Successfully created CPDB entries');
    } else {
        $warningSummary .= gT("No new participants were created") . "<br/>";
    }
    if (!empty($recordcount)) {
        $infoSummary .= "<ul><li>" . sprintf(gT("%s records found in CSV file"), $recordcount) . "</li>";
    }
    if (!empty($mandatory)) {
        $infoSummary .= "<li>" . sprintf(gT("%s records have empty mandatory fields"), $mandatory) . "</li>";
    }
    $infoSummary .= "<li>" . sprintf(gT("%s records met minimum requirements"), $mincriteria) . "</li>";
    $infoSummary .= "<li>" . sprintf(gT("%s new participants were created"), $imported) . "</li>";
    if ($overwritten > 0) {
        $infoSummary .= "<li>" . sprintf(gT("%s records were duplicate but had attributes updated"), $overwritten) . "</li>";
    }
    if (count($duplicatelist) || count($invalidemaillist) || count($invalidattribute) || count($aInvalidFormatlist)) {
        $warningSummary .= "<div class='warningheader'>" . gT('Warnings') . "</div><ul>";
        if (count($duplicatelist) > 0) {
            $warningSummary .= "<li>" . sprintf(gT("%s were found to be duplicate entries and did not need a new participant to be created."), count($duplicatelist));
            if ($dupreason == "participant_id") {
                $warningSummary .= '<br>' . sprintf(gT("They were found to be duplicate using the participant id field"));
            } else {
                $warningSummary .= "<br>" . sprintf(gT("They were found to be duplicate using a combination of firstname, lastname and email fields"));
            }
            $warningSummary .= "<div class='badtokenlist' id='duplicateslist'><ul>";
            foreach ($duplicatelist as $data) {
                $warningSummary .= "<li>" . $data . "</li>";
            }
            $warningSummary .= "</ul></div></li>";
        }
        if (count($invalidemaillist) > 0) {
            $warningSummary .= "<li style='width: 400px'>" . sprintf(gT("%s records with invalid email address removed"), count($invalidemaillist));
            $warningSummary .= "<div class='badtokenlist' id='invalidemaillist'><ul>";
            foreach ($invalidemaillist as $data) {
                $warningSummary .= "<li>" . $data . "</li>";
            }
            $warningSummary .= "</ul></div></li>";
        }
        if (count($invalidattribute) > 0) {
            $warningSummary .= "<li style='width: 400px'>" . sprintf(gT("%s records have incomplete or wrong attribute values"), count($invalidattribute));
            $warningSummary .= "<div class='badtokenlist' id='invalidattributelist' ><ul>";
            foreach ($invalidattribute as $data) {
                $warningSummary .= "<li>" . $data . "</li>";
            }
            $warningSummary .= "</ul></div></li>";
        }
        if (count($aInvalidFormatlist) > 0) {
            $warningSummary .= "<li style='width: 400px'>" . sprintf(gT("%s records where the number of fields does not match"), count($aInvalidFormatlist));
            $warningSummary .= "<div class='badtokenlist' id='invalidattributelist' ><ul>";
            foreach ($aInvalidFormatlist as $data) {
                $warningSummary .= "<li>" . vsprintf(gT('Line %s: Fields found: %s Expected: %s'), explode(',', (string) $data)) . "</li>";
            }
            $warningSummary .= "</ul></div></li>";
        }
    }
    $alerts .= $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => $successSummary,
        'type' => 'success',
        'htmlOptions' => ['class' => 'successheader']
    ], true);

    if ($warningSummary !== '') {
        $alerts .= $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => $warningSummary,
            'type' => 'warning',
            'htmlOptions' => ['class' => 'warningheader']
        ], true);
    }
} else {
    $alerts .= $this->widget('ext.AlertWidget.AlertWidget', [
        'header' => gT('Error'),
        'text' => $errorinupload['error'],
        'type' => 'danger',
        'htmlOptions' => ['class' => 'warningheader']
    ], true);
}

?>
<div id='attribute-map-csv-modal' class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT("CPDB CSV summary"); ?></h5>
                <a href="<?= App()->createUrl("admin/participants/sa/displayParticipants") ?>" type="button" class="btn-close" aria-label="Close"></a>
            </div>
            <div class="modal-body">
                <?= $alerts ?>
            </div>
            <div class="modal-footer">
                <a href="<?= App()->createUrl("admin/participants/sa/displayParticipants") ?>" class="btn btn-outline-secondary"><?php eT("Ok");?></a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

