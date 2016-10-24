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
        <?php foreach($indexItems as $step=>$indexItem): ?>
            <!-- bs class for testing : except text-danger : all other can use specific class (not from bs ?) but bg-danger is really great here -->
            <li class="<?php echo ($indexItem['stepStatus']['has-unanswered'])? "bg-warning":"" ?> <?php echo ($indexItem['stepStatus']['has-error'])? "bg-danger":"" ?> <?php echo (!$indexItem['stepStatus']['is-before'])? "text-muted":"" ?> <?php echo ($indexItem['stepStatus']['is-current'])? "current active":"" ?>">
                <a href='<?php echo $indexItem['url']; ?>' data-limesurvey-submit='<?php echo $indexItem['submit']; ?>'>
                    <?php echo $indexItem['text']; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</li>
