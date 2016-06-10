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

<!-- othertext -->
<script type="text/javascript">
    <!--
        function showhideother(name, value)
        {
            var hiddenothername='othertext'+name;
            if (value == "-oth-")
            {
                document.getElementById(hiddenothername).style.display='';
                document.getElementById(hiddenothername).focus();
            }
            else
            {
                document.getElementById(hiddenothername).style.display='none';
                document.getElementById(hiddenothername).value=''  // reset othercomment fiel
            }
        }
    -->
</script>

<input
    class="form-control"
    type="text"
    id="othertext<?php echo $name; ?>"
    name="<?php echo $name; ?>other"
    style='<?php echo $display; ?>'
    value='<?php echo $value?>'
    onchange='<?php echo $checkconditionFunction;?>(this.value, this.name, this.type);'
/>
<!-- end of othertext -->
