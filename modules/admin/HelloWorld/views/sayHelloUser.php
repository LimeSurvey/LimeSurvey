<?php
/* @var $this AdminController  */
/* @var $sWho url parameter */
?>


<div class="col-sm-12 ">

    <h3 class="pagetitle"><?php eT('Hello World Admin Module'); ?></h3>
    <div class="row">
        <div class="col-sm-12 ">
          Hello <?php echo $sUserName; ?> ! <br/>
          sWho parameter in url: <?php echo $sWho; ?>
        </div>

        <div class="col-sm-12 ">
          No more menus? Just use the breadcrump to go back to root page :)
        </div>
    </div>

</div>
