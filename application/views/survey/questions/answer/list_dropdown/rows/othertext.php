<?php
/**
 * List DropDown, Other Option
 *
 * @var $name           $ia[1]
 * @var $display
 * @var $value
 * @var $checkconditionFunction
 */
?>
<label for="othertext<?php echo $name; ?>" class="sr-only">
    <?php echo LimeExpressionManager::ProcessStepString($label); ?>
</label>
<div class="form-group text-item other-text-item">
<?php
echo \CHtml::textField("{$name}other",$value,array(
    'id' => "othertext{$name}",
    'class'=>"form-control",
    'aria-labelledby' => "answer{$name}-oth-",
));
?>
</div>
<script>
if( $("#answer<?php echo $name; ?>").val() != "-oth-" ){
    $("#othertext<?php echo $name; ?>").hide();
}
$(document).on("change","#answer<?php echo $name; ?>", function(){
    if($("#answer<?php echo $name; ?>").val() != "-oth-"){
        $("#othertext<?php echo $name; ?>").hide();
        $("#othertext<?php echo $name; ?>").val("").trigger("keyup");
    }else{
        $("#othertext<?php echo $name; ?>").show();
        $("#othertext<?php echo $name; ?>").focus();
    }
});
</script>
<!-- end of othertext -->
