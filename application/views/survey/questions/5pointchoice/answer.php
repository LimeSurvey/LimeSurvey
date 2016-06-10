<?php
/**
 * 5 point choice Html
 *
 * @var $sRows         : the rows, generated with the view item_row.php
 * @var $slider_rating : slider rating display in question attribute
 *
 * @var $id
 * @var $sliderId  $ia[0];
 * @var $name'                   => $ia[1],
 * @var $sessionValue
 */
?>

<!-- 5 point choice -->

<!-- answer -->
<div id="<?php echo $id; ?>" class="answers-list five-point-choice radio-list">

    <?php
        // item_row.php
        echo $sRows;
    ?>

    <!-- 5 point choice footer -->
    <input
        type="hidden"
        name="java<?php echo $name;?>"
        id="java<?php echo $name;?>"
        value="<?php echo $sessionValue;?>"
    />

    <?php if($slider_rating==1):?>
        <script type='text/javascript'>
        <!--
            doRatingStar( <?php echo  $sliderId;?> );
        -->
        </script>
    <?php elseif($slider_rating==2):?>
        <script type='text/javascript'>
        <!--
            doRatingSlider( <?php echo  $sliderId; ?> );
        -->
        </script>
    <?php endif;?>
</div>
<!-- end of answer -->
