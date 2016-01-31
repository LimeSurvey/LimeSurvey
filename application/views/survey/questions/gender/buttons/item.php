<?php
/**
 * Gender question, button item Html
 *
 * $name                        $ia[1]
 * $checkconditionFunction      $checkconditionFunction $checkconditionFunction(this.value, this.name, this.type)
 * $fChecked
 * $mChecked
 * $naChecked
 * $value                       $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]
 */
?>
<div class="answers-list radio-list">

    <div class="btn-group" data-toggle="buttons">

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
           <span class="glyphicon glyphicon-user"></span>
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
            <span class="glyphicon glyphicon-user"></span>
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
