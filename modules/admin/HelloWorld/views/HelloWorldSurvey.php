<?php
/* @var $this AdminController  */
/* @var $sWho url parameter */
?>

<div class='menubar surveybar' id="helloworldbarid">
    <div class='row container-fluid'>
        <!-- left buttons -->
        <div class="col-md-10">
          <a class="btn btn-default pjax" href='<?php echo $this->createUrl('admin/HelloWorld/sa/sayHelloUser/', ['surveyid' => $oSurvey->sid, 'sWho'=> "foo"]); ?>' role="button">
              <span class="fa  fa-smile-o text-success"></span>
                Hello user
          </a>
        </div>
    </div>
</div>


<div class="col-sm-12 ">

    <h3 class="pagetitle"><?php eT('Hello World Admin Module'); ?></h3>
    <div class="row">
        <div class="col-sm-12 ">
          This is the root function. Just click on menu item to see the breadcrumb in action
        </div>
    </div>

</div>
