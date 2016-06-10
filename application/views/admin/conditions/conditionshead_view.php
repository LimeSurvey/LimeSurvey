        <div class='side-body <?php echo getSideBodyClass(false); ?>'>
            <?php $this->renderPartial('/admin/survey/breadcrumb', array('oQuestion'=>$oQuestion, 'active'=>gT("Conditions designer") )); ?>
           <h3><?php eT("Conditions designer");?>:</h3>
         <div class="row">
            <div class="col-lg-12 content-right">


<?php echo $conditionsoutput_action_error;?>
<?php echo $javascriptpre;?>
