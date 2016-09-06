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
<input
    class="form-control"
    type="text"
    id="othertext<?php echo $name; ?>"
    name="<?php echo $name; ?>other"
    style='<?php echo $display; ?>'
    value='<?php echo $value?>'
/>

<script>
if($("#answer<?php echo $name; ?>").val()!="-oth-"){
    $("#othertext<?php echo $name; ?>").hide();
}
$(document).on("change","#answer<?php echo $name; ?>",function(){
    if($("#answer<?php echo $name; ?>").val()!="-oth-"){
        $("#othertext<?php echo $name; ?>").hide();
        $("#othertext<?php echo $name; ?>").val("").trigger("keyup");
    }else{
        $("#othertext<?php echo $name; ?>").show();
        $("#othertext<?php echo $name; ?>").focus();
    }
});
</script>
<!-- end of othertext -->
