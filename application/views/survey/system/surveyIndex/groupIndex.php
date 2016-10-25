<?php
/**
 * $type integer
 * $indexItems array[]
 *
 */
?>
<div class="list-group ls-index-buttons index-button-<?php echo ($type>1)? 'full':'incremental' ?>">
    <div class="list-group-item">
        <div class="h4 list-group-item-heading"><?php echo gT("Question index"); ?></div>
    </div>
    <?php foreach($indexItems as $step=>$indexItem): ?>
        <?php
            $statusClass = $indexItem['stepStatus']['has-unanswered']? " list-group-item-warning":"";
            $statusClass.= $indexItem['stepStatus']['has-error']? " list-group-item-danger index-item-error":"";
            $statusClass.= $indexItem['stepStatus']['is-before']? " index-item-before":"";
            $statusClass.= $indexItem['stepStatus']['is-current']? " active index-item-current":"";
        ?>
        <button type="submit" name="move" value="<?php echo $indexItem['step']; ?>"
            class="list-group-item <?php echo $statusClass; ?>">
            <?php echo $indexItem['text']; ?>
        </button>
    <?php endforeach; ?>
</div>
