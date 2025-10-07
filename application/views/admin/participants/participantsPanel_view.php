<?php
/**
 * @var bool $ownsAddParticipantsButton
 * @var bool $ownsAddAttributeButton
 * @var bool $aAttributes
 */
?>
<?php
App()->getClientScript()->registerScriptFile(
    App()->getConfig('adminscripts') . 'topbar.js',
    CClientScript::POS_END
);
?>
<script src="<?php echo Yii::app()->getConfig('adminscripts') . "participantpanel.js" ?>" type="text/javascript"></script>

<script type="text/javascript">
    var exporttocsvcountall = "<?php echo Yii::app()->getController()->createUrl("/admin/participants/sa/exporttocsvcountAll"); ?>";
    var exporttocsvall = "<?php echo Yii::app()->getController()->createUrl("exporttocsvAll"); ?>";
    var okBtn = "<?php eT("OK", 'js') ?>";
    var error = "<?php eT("Error", 'js') ?>";
    var exportBtn = "<?php eT("Export", 'js') ?>";
    var cancelBtn = "<?php eT("Cancel", 'js') ?>";
    var exportToCSVURL = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/exporttocsv"); ?>";
    var openModalParticipantPanel = "<?php echo ls\ajax\AjaxHelper::createUrl("/admin/participants/sa/openModalParticipantPanel"); ?>";
    var editValueParticipantPanel = "<?php echo Yii::app()->getController()->createUrl("/admin/participants/sa/editValueParticipantPanel"); ?>";
    var deleteLanguageFromAttributeUrl = "<?php echo Yii::app()->getController()->createUrl("/admin/participants/sa/deleteLanguageFromAttribute"); ?>";

    var translate_blacklisted = "<?php echo '<i class=\"ri-arrow-go-back-line\"></i> ' . gT('Remove from blocklist?'); ?>";
    var translate_notBlacklisted = "<?php echo '<i class=\"ri-forbid-2-line\"></i> ' . gT('Add to blocklist?'); ?>";
    var datepickerConfig =     <?php
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        echo json_encode(
            [
                'dateformatdetails'    => $dateformatdetails['dateformat'],
                'dateformatdetailsjs'  => $dateformatdetails['jsdate'],
                "initDatePickerObject" => [
                    "format"   => $dateformatdetails['jsdate'],
                    "tooltips" => [
                        "today"        => gT('Go to today'),
                        "clear"        => gT('Clear selection'),
                        "close"        => gT('Close the picker'),
                        "selectMonth"  => gT('Select month'),
                        "prevMonth"    => gT('Previous month'),
                        "nextMonth"    => gT('Next month'),
                        "selectYear"   => gT('Select year'),
                        "prevYear"     => gT('Previous year'),
                        "nextYear"     => gT('Next year'),
                        "selectDecade" => gT('Select decade'),
                        "prevDecade"   => gT('Previous decade'),
                        "nextDecade"   => gT('Next decade'),
                        "prevCentury"  => gT('Previous century'),
                        "nextCentury"  => gT('Next century')
                    ]
                ]
            ]
        );?>;
</script>

<!-- Modal for editing participants-->
<div class="modal fade" id="participantPanel_edit_modal" tabindex="-1" role="dialog" aria-labelledby="participantPanel_edit_modal">
    <div class="modal-dialog " role="document">
        <div class="modal-content">

        </div>
    </div>
</div>

<?php
$aModalData = ['aAttributes' => $aAttributes];
App()->getController()->renderPartial('/admin/participants/modal_subviews/_exportCSV', $aModalData);
?>
