<?php
/**
 * Gender question, button item Html
 *
 * OBS: If you change the HTML flow of this question, please make
 * sure that <input> is the first child of <label> or change the
 * onclick function to reflect your change.
 *
 * @var $name
 * @var $fChecked
 * @var $mChecked
 * @var $naChecked
 * @var $value
 */

?>

<!--Gender question, buttons display -->

<!-- answer -->
<div class="answers-list radio-list gender-button">

    <div class='hidden'>  <!-- Hide this for now -->
        <button class="btn btn-danger btn-lg ls-icons" type="button" data-id="answer<?php echo $name;?>F"  >
            <span class="fa fa-venus lead gender-icon"  ></span>
            <span class="gender-text">
                <?php eT('Female');?>
            </span>
        </button>

        <button class="btn btn-info btn-lg ls-icons" type="button"  data-id="answer<?php echo $name;?>M" >
            <span class="fa fa-mars lead gender-icon" ></span>
            <span class="gender-text">
                <?php eT('Male');?>
            </span>
        </button>

        <?php if($noAnswer):?>
        <button class="btn btn-default btn-lg ls-icons" type="button"  data-id="answer<?php echo $name;?>" >
            <span class="fa fa-genderless lead gender-icon"  ></span>
            <span class="gender-text">
                <?php eT('No answer'); ?>
            </span>
        </button>
        <?php endif; ?>
    </div>

    <div class='col-xs-12 col-sm-6'>  <!-- Full width on Phone; otherwise half width -->
        <div class="btn-group btn-group-justified" data-toggle="buttons">

          <!-- Female -->
          <label
                class="btn btn-primary <?php if($fChecked!=''){echo 'active';}?>" id="label-answer<?php echo $name;?>F"
                onclick="checkconditions(this.children[0].value, this.children[0].name, this.children[0].type);"
            >
              <input
                class="radio"
                type="radio"
                name="<?php echo $name;?>"
                id="answer<?php echo $name;?>F"
                value="F"
                <?php echo $fChecked; ?>
               />
               <span class="fa fa-venus"></span>
              <?php eT('Female');?>
          </label>

          <!-- Male -->
            <label
                class="btn btn-primary  <?php if($mChecked!=''){echo 'active';}?> " id="label-answer<?php echo $name;?>M"
                onclick="checkconditions(this.children[0].value, this.children[0].name, this.children[0].type);"
            >
                <input
                  class="radio"
                  type="radio"
                  name="<?php echo $name;?>"
                  id="answer<?php echo $name;?>M"
                  value="M"
                  <?php echo $mChecked;?>
                />
                <span class="fa fa-mars"></span>
                <?php eT('Male');?>
          </label>

          <!-- No answer -->
          <?php if($noAnswer):?>
              <label
                    class="btn btn-primary  <?php if($naChecked!=''){echo 'active';}?>" id="label-answer<?php echo $name;?>"
                    onclick="checkconditions(this.children[0].value, this.children[0].name, this.children[0].type);"
                >
                  <input
                      class="radio"
                      type="radio"
                      name="<?php echo $name;?>"
                      id="answer<?php echo $name;?>"
                      value=""
                      <?php echo $naChecked;?>
                  />
                  <span class="fa fa-genderless"></span>
                  <span class='wrap-normal'><?php eT('No answer'); ?></span>
              </label>
            <?php endif;?>
        </div>
    </div>

    <!-- Value -->
    <input
        type="hidden"
        name="java<?php echo $name;?>"
        id="java<?php echo $name; ?>"
        value="<?php echo $value;?>"
    />
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
<!-- end of answer -->
