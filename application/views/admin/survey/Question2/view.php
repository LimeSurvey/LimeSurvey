<?php $this->renderPartial("./survey/Question2/_jsVariables", ['data' => $jsData]); ?>

<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <div class="container-fluid">
        <div class="pagetitle h3"><?php eT('Question'); ?>:  <?php echo  $qrrow['title'];?> <small>(ID: <?php echo  $qid;?>)</small></div>
            <div class="row">
                <div id="advancedQuestionEditor" class="col-xs-12"><app/></div>
            </div>
        
        
        <div id="questionEditLoader" class="ls-flex ls-flex-column align-content-center align-items-center" style="min-height: 100%;">
            <div class="ls-flex align-content-center align-items-center">
                <div class="loader-advancedquestionsettings text-center">
                    <div class="contain-pulse animate-pulse">
                        <div class="square"></div>
                        <div class="square"></div>
                        <div class="square"></div>
                        <div class="square"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>