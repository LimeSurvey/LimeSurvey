<?php
/* @var $this AdminController */
/* @var $sWho url parameter */
?>

<div class='menubar surveybar' id="helloworldbarid">
    <div class="container-fluid">
        <div class='row'>
            <!-- left buttons -->
            <div class="col-lg-10">
                <a class="btn btn-outline-secondary pjax" href='<?php echo $this->createUrl('admin/HelloWorld/sa/sayHelloUser/', ['surveyid' => $oSurvey->sid, 'sWho' => "foo"]); ?>' role="button">
                    <span class="ri-emotion-happy-line text-success"></span>
                    Hello user
                </a>
            </div>
        </div>
    </div>
</div>


<div class="col-12 ">

    <h3 class="pagetitle"><?php eT('Hello World Admin Module'); ?></h3>
    <div class="row">
        <div class="col-12 ">
            This is the root function. Just click on menu item to see the breadcrumb in action
        </div>
    </div>

</div>
