<div class="row">
    <div class="span3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="span9">
        <h2><?php echo $title; ?></h2>
        <p><?php echo $descp; ?></p>
        <b> <?php eT("Administrator credentials"); ?>:</b><br /><br />
        <?php eT("Username"); ?>: <?php echo $user; ?> <br />
        <?php eT("Password"); ?>: <?php echo $pwd; ?>
        <div style="text-align: center">
            <?php
                $this->widget('ext.bootstrap.widgets.TbButton', array(
                    'url' => $this->createUrl("/admin"),
                    'label' => gT("Administration")
                ));
            ?>
        </div>
    </div>
</div>
