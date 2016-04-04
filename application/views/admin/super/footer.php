<?php
/**
 * Footer view
 * Inserted in all pages
 */
?>

<!-- Footer -->
<footer class='footer'>
	<div class="container-fluid">
		<div class="row">
		    <!-- Link to manual -->
		    <div class="col-xs-6 col-md-4 col-lg-1 ">
		    	<a href='http://manual.limesurvey.org' onclick='function go(ev) { ev.preventDefault(); var win = window.open("http://manual.limesurvey.org", "_blank"); win.focus(); }; go(event);'>
                    <span class="glyphicon glyphicon-info-sign" id="info-footer"></span>
                </a>
		    </div>

		    <!-- Support / Donate -->
		    <div  class="col-xs-6 col-md-4  col-lg-5 text-right"  >
		    	<a href='http://donate.limesurvey.org'>
                    <img alt='<?php eT("Support this project - Donate to "); ?>LimeSurvey' title='<?php eT("Support this project - Donate to "); ?>LimeSurvey!' src='<?php echo Yii::app()->getConfig('adminimageurl');?>donate.png'/>
                </a>
		    </div>

		    <!-- Lime survey website -->
		    <div class="col-xs-6 col-md-4 col-lg-6 text-right">
		    	<a  title='<?php eT("Visit our website!"); ?>' href='http://www.limesurvey.org' target='_blank'>LimeSurvey</a><br /><?php echo $versiontitle."  ".$versionnumber." ".$buildtext;?>
		    </div>
		</div>
	</div>
</footer>

<!-- Modal for confirmation -->
<div id="confirmation-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirm</h4>
            </div>
            <div class="modal-body">
                <p><?php eT("Are you sure?"); ?></p>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-primary btn-ok"><span class='fa fa-check'></span>&nbsp;<?php eT("Yes"); ?></a>
                <button type="button" class="btn btn-danger" data-dismiss="modal"><span class='fa fa-ban'></span>&nbsp;<?php eT("No"); ?></button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
