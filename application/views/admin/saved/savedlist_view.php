<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3>
        <?php eT('Saved responses'); ?>
        <small><?php echo flattenText($sSurveyName) . ' ' . sprintf(gT('ID: %s'), $iSurveyId); ?></small>
    </h3>

        <div class="row">
            <div class="col-lg-12 content-right">
<?php
    $this->widget('ext.LimeGridView.LimeGridView', array(
            'id' => 'saved-grid',
            'ajaxUpdate' => 'saved-grid',
            'dataProvider' => $dataProvider,
            'ajaxType'      => 'POST',
            'template'      => "{items}\n<div class='row'><div class='col-sm-4 col-md-offset-4'>{pager}</div><div class='col-sm-4'>{summary}</div></div>",
            'columns' => array(
                array(
                    'header' => gT("ID"),
                    'name' => 'scid',
                ),
                array(
                    'class'=>'bootstrap.widgets.TbButtonColumn',
                    'template'=>'{editresponse}{delete}',
                    //~ 'htmlOptions' => array('class' => 'text-left response-buttons'),
                    'buttons'=> $SavedControlModel->getGridButtons($iSurveyId),
                ),
                array(
                    'header' => gT("Identifier"),
                    'name' => 'identifier',
                ),
                array(
                    'header' => gT("IP address"),
                    'name' => 'ip',
                ),
                array(
                    'header' => gT("Date saved"),
                    'name' => 'saved_date',
                ),
                array(
                    'header' => gT("Email address"),
                    'name' => 'email',
                ),
            ),
        ),
    );
?>
            </div>
        </div>
</div>
