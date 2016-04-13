<?php
/**
 * Statistic output
 *
 * @var $outputs
 * @var $bSum
 * @var $bAnswer
 */
?>
<table class='statisticstable table table-bordered'>
    <thead>
        <tr class='success'>
            <th colspan='4' align='center' style='text-align: center; '>
                <strong>
                    <?php echo sprintf(gT("Field summary for %s"),$outputs['qtitle']); ?>
                </strong>
            </th>
        </tr>
        <tr>
            <th colspan='4' align='center' style='text-align: center; '>
                <!-- question title -->
                <strong>
                    <?php echo $outputs['qquestion'];?>
                </strong>
            </th>
        </tr>
        <!-- width depend on how much items... -->
        <tr>
            <th width='' align='center' >
                <strong>
                    <?php eT("Answer");?>
                </strong>
            </th>

            <th width='' align='center' >
                <strong><?php eT("Count"); ?></strong>
            </th>

            <th width='' align='center' >
                <strong><?php eT("Percentage");?></strong>
            </th>

            <?php if($bSum): ?>
                <th width='' align='center' >
                    <strong>
                        <?php eT("Sum");?>
                    </strong>
                </th>
            <?php endif; ?>
        </tr>
    </thead>
