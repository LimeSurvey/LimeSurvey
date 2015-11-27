<div id="translationloading" style="width: 100%; font-weight: bold; color: #000; text-align: center;">
    <br />
    <?php eT("Loading translations");?><br /><br />
</div>

<?php echo CHtml::form(array("admin/translate/sa/index/surveyid/{$surveyid}/lang/{$tolang}"), 'post', array('name'=>'translateform','id'=>'translateform'));?>
	<input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
	<input type='hidden' name='action' value='translate' />
	<input type='hidden' name='actionvalue' value='translateSave' />
	<input type='hidden' name='tolang' value='<?php echo $tolang;?>' />
	<input type='hidden' name='baselang' value='<?php echo $baselang;?>' />

	<script type="text/javascript">
		sGoogleApiError = "<?php eT("There was an error using the Google API.");?>";
		sDetailedError  = "<?php eT("Detailed Error");?>";
		translateJsonUrl = "<?php echo $this->createUrl("admin/translate/sa/ajaxtranslategoogleapi"); ?>";
	</script>

	<div id="translationtabs" style="display: none;">
		<ul class="nav nav-tabs">
		<?php
		for($i = 0, $len = count($tab_names); $i < $len; $i++) {
			$amTypeOptionsTemp = $amTypeOptions[$i];
			$type = $tab_names[$i];
			?> <li <?php if($i==0){echo ' class="active" ';}?>>
                    <a data-toggle="tab"  href="#tab-<?php echo $type;?>">
                        <span>
                            <?php echo $amTypeOptionsTemp["description"];?>
                        </span>
                    </a>
                </li> <?php
		}
		$i = 0;
		?>
		</ul>
        <div class="tab-content">
