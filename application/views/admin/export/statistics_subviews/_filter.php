<?php
$colClass = 'col-lg-4 col-md-12';
if (!isset($datestamp) || $datestamp == "N") {
    $colClass = 'col-lg-6 col-md-12';
}
?>
<h4 class="h4"><?php
    eT("Filter"); ?></h4>
<div class="row">
    <div class="<?= $colClass ?>">
        <div class='mb-3'>
            <label class="form-label" for='idG'><?php
                eT("Response ID greater than:"); ?></label>
            <div class=''>
                <input class="form-control" type='number' id='idG' name='idG' size='10' value='<?php
                if (isset($_POST['idG'])) {
                    echo sanitize_int($_POST['idG']);
                } ?>' onkeypress="return window.LS.goodchars(event,'0123456789')"/>
            </div>
        </div>
    </div>
    <div class="<?= $colClass ?>">
        <div class='mb-3'>
            <label class="form-label" for='idL'><?php
                eT("Response ID less than:"); ?></label>
            <div class=''>
                <input class="form-control" type='number' id='idL' name='idL' size='10' value='<?php
                if (isset($_POST['idL'])) {
                    echo sanitize_int($_POST['idL']);
                } ?>' onkeypress="return window.LS.goodchars(event,'0123456789')"/>
            </div>
        </div>
    </div>
</div>
<?php
if (isset($datestamp) && $datestamp == "Y") : ?>
    <div class="row">
        <div class="col-lg-4 col-md-12">
            <div class='mb-3'>
                <label class="form-label" for='datestampE'><?php
                    eT("Submission date equals:"); ?></label>
                <div class="has-feedback">
                    <?php
                    Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                        'name' => "datestampE",
                        'id' => 'datestampE',
                        'value' => $_POST['datestampE'] ?? '',
                        'pluginOptions' => array(
                            'format' => ($dateformatdetails['jsdate']),
                            'allowInputToggle' => true,
                            'showClear' => true,
                            'theme' => 'light',
                            'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                        )
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class='mb-3'>
                <label class="form-label" for='datestampG'><?php
                    eT("Submission date later than:"); ?></label>
                <div class="has-feedback">
                    <?php
                    Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                        'name' => "datestampG",
                        'id' => 'datestampG',
                        'value' => $_POST['datestampG'] ?? '',
                        'pluginOptions' => array(
                            'format' => $dateformatdetails['jsdate'] . " HH:mm",
                            'allowInputToggle' => true,
                            'showClear' => true,
                            'theme' => 'light',
                            'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                        )
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class='mb-3 top-5'>
                <label class="form-label" for='datestampL'><?php
                    eT("Submission date earlier than:"); ?></label>
                <div class="has-feedback">
                    <?php
                    Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                        'name' => "datestampL",
                        'id' => 'datestampL',
                        'value' => $_POST['datestampL'] ?? '',
                        'pluginOptions' => array(
                            'format' => $dateformatdetails['jsdate'] . " HH:mm",
                            'allowInputToggle' => true,
                            'showClear' => true,
                            'theme' => 'light',
                            'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                        )
                    ));
                    ?>
                </div>
            </div>
        </div>
        <input type='hidden' name='summary[]' value='datestampE'/>
        <input type='hidden' name='summary[]' value='datestampG'/>
        <input type='hidden' name='summary[]' value='datestampL'/>
    </div>
    <?php
endif; ?>
