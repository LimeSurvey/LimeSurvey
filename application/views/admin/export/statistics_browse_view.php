<?php

?>

<?php if(Permission::model()->hasSurveyPermission($surveyid,'responses','read')){ ?>
    <div class='statisticscolumnid'>
        <?php
        $iconClass=' ri-arrow-down-s-fill ';
        $disabled=true;
        if($sortby=="id") {
            $disabled = false;
            switch($sortmethod) {
                case "asc":
                    $iconClass="ri-arrow-down-s-fill";
                    break;
                case "desc":
                    $image="ri-sort-asc";
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
    $iconClass=' ri-arrow-down-s-fill ';
    $disabled=true;
    if($sortby==$column) {
        $disabled = false;
        switch($sortmethod) {
            case "asc":
                $iconClass="ri-arrow-down-s-fill";
                break;
            case "desc":
                $image="ri-sort-asc";
                break;
        }
    }
    ?>

    <?php $sort=(isset($sortby) && $sortby==$column && $sortmethod=='asc') ? 'desc' : 'asc'; ?>
    <?php if(!$disabled): ?>
        <a href="#" class='sortorder d-print-none' id='sortorder_<?php echo $column ?>_<?php echo $column ?>_<?php echo $sort ?>_<?php echo $sorttype ?>'>
                <span class="<?php echo $iconClass?>"></span>
        </a>
    <?php endif;?>
    </div>
<div style='clear: both'></div>
<?php
foreach ($data as $row) {
?>
<?php if(Permission::model()->hasSurveyPermission($surveyid,'responses','read')){ ?>
    <div class='statisticscolumnid col-md-1 d-print-none'>
        <a href='<?php echo Yii::app()->getController()->createUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $row['id']]); ?>' target='_blank' title='<?php eT("View response"); ?>' data-bs-toggle="tooltip" data-bs-placement="top">
            <span class="ri-search-line"></span>
        </a>
    </div>
<?php } ?>
<div class='statisticscolumndata col-md-11 text-start' >
    <?php echo sanitize_html_string($row['value']) ?>
</div>

<?php
}
?>
