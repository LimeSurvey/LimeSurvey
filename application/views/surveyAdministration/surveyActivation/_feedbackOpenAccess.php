<?php
    /** @var $surveyId */
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary border-left-success h-100">
            <div class="card-header">
                <h5 class="card-title">
                    <?php et('Congrats! Your survey has been activated successfully in open-access mode.');?>
                </h5>
            </div>
            <div class="card-body d-flex">
                <ul class="list-unstyled">
                    <li>
                <?php et('Statistics and responses are now accessible.'); ?>
                <a href="<?= Yii::app()->createUrl('responses/browse', ['surveyId' => $surveyId])?>"><?= gT('See all responses and statistics')?></a>
                    </li>
                <?php
                et("By default, surveys are activated in open-access mode and participants don't need an invitation code. ");
                ?>
                </ul>
            </div>
        </div>
    </div>
</div>
