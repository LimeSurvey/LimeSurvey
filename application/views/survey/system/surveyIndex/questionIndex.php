<?php
/**
 * $type integer
 * $indexItems array[]
 *
 */
?>
<div class="list-group ls-index-buttons index-button-<?php echo ($type>1)? 'full':'incremental' ?>">
    <div class="list-group-item">
        <div class="h3 list-group-item-heading"><?php echo gT("Question index"); ?></div>
    </div>
    <?php foreach($indexItems as $group): ?>
        <div class="list-group" data-gid="<?php $group['gid'] ?>">
            <div class="list-group-item">
                <div class="h4 list-group-item-heading"><?php echo viewHelper::flatEllipsizeText($group['text'],true,80," &hellip; ",0.6); ?></div>
            </div>
            <?php foreach($group['questions'] as  $step=>$indexItem): ?>
                <?php
                    $statusClass = $indexItem['stepStatus']['index-item-unanswered']? " list-group-item-warning":"";
                    $statusClass.= $indexItem['stepStatus']['index-item-error']? " list-group-item-danger":"";
                    $statusClass.= $indexItem['stepStatus']['index-item-current']? " disabled":"";
                ?>
                <button type="submit" name="move" value="<?php echo $indexItem['step']; ?>" data-qid="<?php $indexItem['qid'] ?>"
                    class="<?php echo $indexItem['coreClass']; ?> list-group-item <?php echo $statusClass; ?>">
                    <?php echo viewHelper::flatEllipsizeText($indexItem['text'],true,80," &hellip; ",0.6); ?>
                </button>
            <?php endforeach;?>
        </div>
    <?php endforeach;?>
</div>
