<?php
/** @var AdminController $this */
$dateFormatDetails=getDateFormatData(Yii::app()->session['dateformat']);

?>
<?php $form = $this->beginWidget('CActiveForm', array('id'=>'survey-expiry',)); ?>

<div id='publication' class="container-center">
    <div class="row">
        <!-- Expiry date/time -->
        <div class="form-group">

            <label class="col-sm-6 control-label" for='expires'><?php  eT("Expiry date/time:"); ?></label>
            <div class='col-sm-6'>

                <input class="form-control" name="datepickerInputField" id="datepickerInputField" type="text" value="">
                <input class="form-control custom-data" name="expires" id="expires" type="hidden" value="">
                <script type="text/javascript">
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
                    $(function () {
                        $('#datepickerInputField').datetimepicker(datepickerConfig.initDatePickerObject);
                        $('#datepickerInputField').on("dp.change", function(e){
                            $("#expires").val(e.date.format(datepickerConfig.dateformatdetailsjs));
                        })
                    });
                </script>
            </div>
        </div>
    </div>
</div>
<?php $this->endWidget(); ?>
