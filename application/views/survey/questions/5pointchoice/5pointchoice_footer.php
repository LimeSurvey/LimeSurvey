<?php
/**
 * 5 point choice : Footer
 *
 * @var $ia
 * @var $sJavaValue
 * @var $slider_rating
 */
?>

<!-- 5 point choice footer -->
<input
    type="hidden"
    name="java<?php echo $ia[1];?>"
    id="java<?php echo $ia[1];?>"
    value="<?php echo $sJavaValue;?>"
/>
<?php if($slider_rating==1):?>
    <script type='text/javascript'>
    <!--
        doRatingStar( <?php echo $ia[0];?> );
    -->
    </script>
<?php elseif($slider_rating==2):?>
    <script type='text/javascript'>
    <!--
        doRatingSlider( <?php echo $ia[0]; ?> );
    -->
    </script>
<?php endif;?>
</div>
