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
    <?php echo $label; ?>
</label>
<?php
echo \CHtml::textField("{$name}other",$value,array(
    'id' => "{$name}othertext",
    'style' => $display,
    'aria-labelledby' => "answer{$name}-oth-",
));
?>

<script>
if( $("#answer<?php echo $name; ?>").val() != "-oth-" ){
    $("#othertext<?php echo $name; ?>").hide();
}

$(document).on("change","#answer<?php echo $name; ?>", function(){
    if($("#answer<?php echo $name; ?>").val() != "-oth-"){
        $("#<?php echo $name; ?>othertext").hide();
        $("#<?php echo $name; ?>othertext").val("").trigger("keyup");
    }else{
        $("#<?php echo $name; ?>othertext").show();
        $("#<?php echo $name; ?>othertext").focus();
    }
});
</script>
<!-- end of othertext -->
