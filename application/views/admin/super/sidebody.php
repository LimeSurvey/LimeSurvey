<div 
    class='side-body'

    <?php if (isset($id)): ?>
        id="<?php echo $id; ?>"
    <?php endif; ?>

    <?php if ($sideMenuBehaviour == 'adaptive' || $sideMenuBehaviour == ''): ?>
    <?php elseif ($sideMenuBehaviour == 'alwaysClosed'): ?>
    <?php elseif ($sideMenuBehaviour == 'alwaysOpen'): ?>
    <?php endif; ?>
>
    <?php echo $content; ?>
</div>
