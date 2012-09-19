<?php
$clang = Yii::app()->lang;
?>
<div class='statisticscolumnid'>
    <?php
    $image="sort_none.png";
    if($sortby=="id") {
        switch($sortmethod) {
            case "asc":
                $image="sort_desc.png";
                break;
            case "desc":
                $image="sort_asc.png";
                break;
        }
    }
    ?>
    <?php $sort=(isset($sortby) && $sortby=="id" && $sortmethod=='asc') ? 'desc' : 'asc'; ?>
    <img src='<?php echo Yii::app()->getConfig('adminimageurl') . "/" . $image ?>' class='sortorder' id='sortorder_<?php echo $column ?>_id_<?php echo $sort ?>_T' style='cursor: pointer' />
</div>
<div class='statisticscolumndata'>
    <?php
    $image="sort_none.png";
    if($sortby==$column) {
        switch($sortmethod) {
            case "asc":
                $image="sort_desc.png";
                break;
            case "desc":
                $image="sort_asc.png";
                break;
        }
    }
    ?>
    <?php $sort=(isset($sortby) && $sortby==$column && $sortmethod=='asc') ? 'desc' : 'asc'; ?>
    <img src='<?php echo Yii::app()->getConfig('adminimageurl') . "/".$image ?>' class='sortorder' id='sortorder_<?php echo $column ?>_<?php echo $column ?>_<?php echo $sort ?>_<?php echo $sorttype ?>' style='cursor: pointer' /></div>
<div style='clear: both'></div>
<?php
foreach ($data as $row) {
?>
<div class='statisticscolumnid'>
    <a href='<?php echo Yii::app()->getController()->createUrl("admin/responses/view/surveyid/".$surveyid."/id/".$row['id']); ?>' target='_blank'>
        <img src='<?php echo Yii::app()->getConfig('adminimageurl') . "/search.gif" ?>' title='<?php $clang->eT("View response"); ?>'/>
    </a>
</div>
<div class='statisticscolumndata'>
    <?php echo stripslashes($row['value']) ?>
</div>
<div style='clear: both'></div>
<?php
}
?>