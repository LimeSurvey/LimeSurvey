<?php
/**
 * $type integer
 * $indexItems array[]
 *
 */
?>
<div class="list-group ls-index-buttons index-button-<?php echo ($type>1)? 'full':'incremental' ?>">
    <?php foreach($indexItems as $step=>$indexItem): ?>
        <button type="submit" name="move" value="<?php echo $indexItem['step']; ?>"
            class="list-group-item <?php echo ($indexItem['stepStatus']['has-unanswered'])? "list-group-item-warning":"" ?> <?php echo ($indexItem['stepStatus']['has-error'])? "list-group-item-danger":"" ?> <?php echo (!$indexItem['stepStatus']['is-before'])? "text-muted":"" ?> <?php echo ($indexItem['stepStatus']['is-current'])? "active":"" ?>">
            <?php echo $indexItem['text']; ?>
        </button>
    <?php endforeach; ?>
</div>
