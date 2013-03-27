<div class="row">
    <div class="span3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep', 'clang')); ?>
    </div>
    <div class="span9">
        <h2><?php echo $title; ?></h2>
        <p><?php echo $descp; ?></p>
        <b> <?php $clang->eT("Administrator credentials"); ?>:</b><br /><br />
        <?php $clang->eT("Username"); ?>: <?php echo $user; ?> <br />
        <?php $clang->eT("Password"); ?>: <?php echo $pwd; ?>
        <div style="text-align: center">
            <?php
                $this->widget('ext.bootstrap.widgets.TbButton', array(
                    'url' => $this->createUrl("/admin"),
                    'label' => $clang->gT("Administration")
                ));
            ?>
        </div>
    </div>
</div>
