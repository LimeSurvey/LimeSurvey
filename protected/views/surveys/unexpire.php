<div class="row">
<div class="col-md-12">
    This will resume the survey (by removing its expiry date).
    <?php
        echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_HORIZONTAL, ['surveys/unexpire']);
        echo TbHtml::hiddenField('id', $survey->sid);
        echo TbHtml::submitButton('Resume survey', ['color' => 'primary']);
        echo TbHtml::endForm();
    ?>
</div>
</div>