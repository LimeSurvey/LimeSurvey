<?php

?>

<?php if(Permission::model()->hasSurveyPermission($surveyid,'responses','read')){ ?>
    <div class='statisticscolumnid'>
        <?php
        $iconClass=' fa fa-sort-desc ';
        $disabled=true;
        if($sortby=="id") {
            $disabled = false;
            switch($sortmethod) {
                case "asc":
                    $iconClass="fa fa-sort-desc";
                    break;
                case "desc":
                    $image="fa fa-sort-asc";
                    break;
            }
        }
        ?>
        <?php $sort=(isset($sortby) && $sortby=="id" && $sortmethod=='asc') ? 'desc' : 'asc'; ?>
        <?php if(!$disabled): ?>
            <a href="#" class='sortorder'  id='sortorder_<?php echo $column ?>_id_<?php echo $sort ?>_T'>
                    <span class="<?php echo $iconClass?>"></span>
            </a>
        <?php endif;?>
    </div>
<?php } ?>
<div class='statisticscolumndata'>
    <?php
    $iconClass=' fa fa-sort-desc ';
    $disabled=true;
    if($sortby==$column) {
        $disabled = false;
        switch($sortmethod) {
            case "asc":
                $iconClass="fa fa-sort-desc";
                break;
            case "desc":
                $image="fa fa-sort-asc";
                break;
        }
    }
    ?>

    <?php $sort=(isset($sortby) && $sortby==$column && $sortmethod=='asc') ? 'desc' : 'asc'; ?>
    <?php if(!$disabled): ?>
        <a href="#" class='sortorder' id='sortorder_<?php echo $column ?>_<?php echo $column ?>_<?php echo $sort ?>_<?php echo $sorttype ?>'>
                <span class="<?php echo $iconClass?>"></span>
        </a>
    <?php endif;?>
    </div>
<div style='clear: both'></div>
<?php
foreach ($data as $row) {
?>
<?php if(Permission::model()->hasSurveyPermission($surveyid,'responses','read')){ ?>
    <div class='statisticscolumnid col-sm-1'>
        <a href='<?php echo Yii::app()->getController()->createUrl("admin/responses/sa/view/surveyid/".$surveyid."/id/".$row['id']); ?>' target='_blank' title='<?php eT("View response"); ?>' data-toggle="tooltip" data-placement="top">
            <span class="fa fa-search"></span>
        </a>
    </div>
<?php } ?>
<div class='statisticscolumndata col-sm-11 text-left' >
    <?php echo sanitize_html_string($row['value']) ?>
</div>

<?php
}
?>
