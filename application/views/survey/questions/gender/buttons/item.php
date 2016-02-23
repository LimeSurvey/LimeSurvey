<?php
/**
 * Gender question, button item Html
 *
 * $name
 * $checkconditionFunction
 * $fChecked
 * $mChecked
 * $naChecked
 * $value
 */
?>
<div class="answers-list radio-list">

    <button class="btn btn-danger btn-lg ls-icons" type="button" data-id="answer<?php echo $name;?>F"  >
        <span class="fa fa-female lead"  style="font-size: 2em;"></span>
        <br/>
       <?php eT('Female');?>
    </button>

    <button class="btn btn-info btn-lg ls-icons" type="button"  data-id="answer<?php echo $name;?>M" >
        <span class="fa fa-male lead" style="font-size: 2em;"></span>
        <br/>
        <?php eT('Male');?>
    </button>

    <button class="btn btn-default btn-lg ls-icons" type="button"  data-id="answer<?php echo $name;?>" >
        <span class="fa fa-genderless lead"  style="font-size: 2em;"></span>
        <br/>
        <?php eT('No answer'); ?>
    </button>

    <div class="btn-group hidden" data-toggle="buttons">

      <!-- Female -->
      <label class="btn btn-primary btn-lg <?php if($fChecked!=''){echo 'active';}?>">
          <input
            class="radio"
            type="radio"
            name="<?php echo $name;?>"
            id="answer<?php echo $name;?>F"
            value="F"
            <?php echo $fChecked; ?>
            onclick="<?php echo $checkconditionFunction; ?>"
           />
           <span class="fa fa-female"></span>
          <?php eT('Female');?>
      </label>

      <!-- Male -->
      <label class="btn btn-primary  btn-lg <?php if($mChecked!=''){echo 'active';}?> ">
            <input
              class="radio"
              type="radio"
              name="<?php echo $name;?>"
              id="answer<?php echo $name;?>M"
              value="M"
              <?php echo $mChecked;?>
              onclick="<?php echo $checkconditionFunction; ?>"
            />
            <span class="fa fa-male"></span>
            <?php eT('Male');?>
      </label>

      <!-- No answer -->
      <label class="btn btn-primary  btn-lg  <?php if($naChecked!=''){echo 'active';}?>">
                  <input
                      class="radio"
                      type="radio"
                      name="<?php echo $name;?>"
                      id="answer<?php echo $name;?>"
                      value=""
                      <?php echo $naChecked;?>
                      onclick="<?php echo $checkconditionFunction; ?>"
                  />
                  <span class="glyphicon glyphicon-user"></span>
                  <?php eT('No answer'); ?>
      </label>

    <!-- Value -->
    <input
        type="hidden"
        name="java<?php echo $name;?>"
        id="java<?php echo $name; ?>"
        value="<?php echo $value;?>"
    />
    </div>
</div>

<script>
    $(document).ready(function(){
        $('.btn.ls-icons').on('click', function(){
            $id='#'+$(this).data('id');
            console.log('gender, clicked on : '+$id);
            $gender = $($id);
            $gender.trigger('click');
        });
    });
</script>
