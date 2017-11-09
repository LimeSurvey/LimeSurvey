<?php
/**
 * Shortfreetext, input text style, item Html
 *
 * $extraclass
 * $name        $ia[1]
 * $prefix
 * $suffix
 * $kpclass
 * $tiwidth
 * $dispVal
 * $maxlength
 * $checkconditionFunction
 */
?>

<?php if($withColumn): ?>
<div class='<?php echo $coreClass; ?> row'>
    <div class="<?php echo $extraclass; ?>">
<?php else: ?>
<div class='<?php echo $coreClass; ?> <?php echo $extraclass; ?>'>
<?php endif; ?>
    <?php if ($prefix !== '' || $suffix !== ''): ?>
        <div class="ls-input-group">
    <?php endif; ?>
        <!-- Prefix -->
        <?php if ($prefix !== ''): ?>
            <div class='ls-input-group-extra prefix-text prefix'><?php echo $prefix; ?></div>
        <?php endif; ?>

        <!-- Input -->
        <input
            class="form-control <?php echo $kpclass;?>"
            type="text"
            name="<?php echo $name; ?>"
            id="answer<?php echo $name;?>"
            value="<?php echo $dispVal; ?>"
            <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?>
            <?php echo ($maxlength ? 'maxlength='.$maxlength: ''); ?>
            aria-labelledby="ls-question-text-<?php echo $basename; ?>"
        />

        <!-- Suffix -->
        <?php if ($suffix !== ''): ?>
            <div class='ls-input-group-extra suffix-text suffix'><?php echo $suffix; ?></div>
        <?php endif; ?>
    <?php if ($prefix !== '' || $suffix !== ''): ?>
        </div>
    <?php endif; ?>
<?php if($withColumn): ?>
    </div>
</div>
<?php else: ?>
</div>
<?php endif; ?>
<script>
$(function(){
    //first define 
    var parseBrowser = new BrowserCheck();
    <?php if ($suffix !== ''): ?>
    var result = parseBrowser.getBrowserInfoShort();
    <?php else: ?>
    var result = parseBrowser.getBrowserInfoLong();
    <?php endif; ?>
    
    $("answer<?php echo $name;?>").val()
    
});
</script>
