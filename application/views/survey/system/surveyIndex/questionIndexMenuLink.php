<?php
/**
 * $type integer
 * $indexItems array[]
 *
 */
?>
<li class="dropdown ls-index-menu index-menu-<?php echo ($type>1)? 'full':'incremental' ?>">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
        <?php echo gT("Question index"); ?>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        <?php foreach($indexItems as $group): ?>
            <?php if(!empty($first)): ?>
                <li class="divider" role="separator"></li>
            <?php else: ?>
                <?php $first=true; ?>
            <?php endif; ?>
            <li class="dropdown-header">
                <?php echo viewHelper::flatEllipsizeText($group['text'],true,30," &hellip; ",0.6); ?>
            </li>
            <?php foreach($group['questions'] as  $step=>$indexItem): ?>
                <?php
                    /* bs class for testing : bg-danger is really great here, but only if menu is set in navigator or after */
                    $statusClass = $indexItem['stepStatus']['index-item-unanswered']? " bg-warning":"";
                    $statusClass.= $indexItem['stepStatus']['index-item-error']? " bg-danger":"";
                    $statusClass.= $indexItem['stepStatus']['index-item-current']? " active":"";
                ?>
                <li class="<?php echo $indexItem['coreClass']; ?><?php echo $statusClass; ?>">
                    <a href='<?php echo $indexItem['url']; ?>' data-limesurvey-submit='<?php echo $indexItem['submit']; ?>'>
                        <?php echo viewHelper::flatEllipsizeText($indexItem['text'],true,30," &hellip; ",0.6); ?>
                    </a>
                </li>
            <?php endforeach;?>
        <?php endforeach;?>
    </ul>
</li>

