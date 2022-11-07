<?php
 /** @var string  $leftSide this could be a simple text or a breadcrumb */
 /** @var ButtonWidget[] $middle */
 /** @var ButtonWidget[] $rightSide */
?>

<div class="menubar topbar" id="fullpagebar">
    <div class="container-fluid">
        <div class="row">
            <!-- Title or breadcrumb -->
            <div class="col-md-5 text-start h1">
                <h1><?= $leftSide ?></h1>

            </div>

            <?php
            if ($isBreadCrumb) {
                $aData = App()->getController()->aData;
                $oTopbarConfig = TopbarConfiguration::createFromViewData($aData);

                Yii::app()->getController()->widget(
                    'ext.TopbarWidget.TopbarWidgetSurvey',
                    array(
                        'config' => $oTopbarConfig,
                        'aData' => $aData,
                    )
                );
            } else{ ?>
                <!-- middle part with buttons -->
                <div class="col">
                    <?php
                    if ($middle !== null) {
                        foreach ($middle as $buttonWidget) {
                            echo $buttonWidget;
                        }
                    }
                    ?>
                </div>

                <!-- left part with buttons -->
                <div class="col-md-auto text-end">
                    <?php
                    if ($rightSide !== null) {
                        foreach ($rightSide as $buttonWidget) {
                            echo $buttonWidget;
                        }
                    }
                    ?>
                </div>
            <?php }
            ?>
        </div>
    </div>
</div>
