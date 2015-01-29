<?php echo CHtml::beginForm($this->createUrl('installer/config'), 'post', array('class' => 'form-horizontal')); ?>
<div style="color:red; font-size:12px;">
    <?php echo CHtml::errorSummary($model, null, null, array('class' => 'errors')); ?>
</div>
<?php
$labelClasses = 'control-label col-sm-4';
$controlClasses = 'form-control';
$rows = [];
$rows[] = array(
    'label' => CHtml::activeLabelEx($model, 'dbtype', array('class' => $labelClasses, 'label' => gT("Database type"))),
    'control' => CHtml::activeDropDownList($model, 'dbtype', $model->supported_db_types, ['class' => $controlClasses, 'required' => 'required', 'autofocus' => 'autofocus']),
    'description' => gT("The type of your database management system")
);
$rows[] = array(
    'label' => CHtml::activeLabelEx($model, 'dblocation', array('class' => $labelClasses, 'label' => gT("Database location"))),
    'control' => CHtml::activeTextField($model, 'dblocation', ['class' => $controlClasses, 'required' => 'required']),
    'description' => gT('Set this to the IP/net location of your database server. In most cases "localhost" will work. You can force Unix socket with complete socket path.').' '.gT('If your database is using a custom port attach it using a colon. Example: db.host.com:5431')
);
$rows[] = array(
    'label' => CHtml::activeLabelEx($model, 'dbuser', array('class' => $labelClasses, 'label' => gT("Database user"))),
    'control' => CHtml::activeTextField($model, 'dbuser', ['class' => $controlClasses, 'required' => 'required','autocomplete'=>'off']),
    'description' => gT('Your database server user name. In most cases "root" will work.')
);
$rows[] = array(
    'label' => CHtml::activeLabelEx($model, 'dbpwd', array('class' => $labelClasses, 'label' => gT("Database password"))),
    'control' => CHtml::activePasswordField($model, 'dbpwd', ['class' => $controlClasses]),
    'description' => gT("Your database server password.")
);
$rows[] = array(
    'label' => CHtml::activeLabelEx($model, 'dbname', array('class' => $labelClasses, 'label' => gT("Database name"))),
    'control' => CHtml::activeTextField($model, 'dbname', array('required' => 'required', 'class' => $controlClasses)),
    'description' => gT("If the database does not yet exist it will be created (make sure your database user has the necessary permissions). In contrast, if there are existing LimeSurvey tables in that database they will be upgraded automatically after installation.")
);

$rows[] = array(
    'label' => CHtml::activeLabelEx($model, 'dbprefix', array('class' => $labelClasses, 'label' => gT("Table prefix"))),
    'control' => CHtml::activeTextField($model, 'dbprefix', array('value' => 'lime_', 'class' => $controlClasses)),
    'description' => gT('If your database is shared, recommended prefix is "lime_" else you can leave this setting blank.')
);

foreach ($rows as $row)
{
    echo CHtml::openTag('div', array('class' => 'form-group'));
        echo $row['label'];
        echo CHtml::tag('div', array('class' => 'col-sm-8'), $row['control']);
        echo CHtml::tag('span', array('class' => 'help-block'), $row['description']);
    echo CHtml::closeTag('div');
}

?>

<div class="btn-group pull-right">
    <?php
        echo TbHtml::linkButton(gT('Previous'), ['url' => ['installer/precheck']]);
        echo TbHtml::submitButton(gT("Next"), ['color' => TbHtml::BUTTON_COLOR_PRIMARY]); 
    ?>
</div>
<?php echo CHtml::endForm(); ?>
