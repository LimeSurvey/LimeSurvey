<!-- templateeditor_question_answer_view -->
<?php
if (isset($alt))
{
?>

<div class="row question-wrapper">
            <div class="col-sm-12 answer">
                <!-- Long Free Text -->

<!-- answer -->
<p class="question answer-item text-item  inputwidth-12 col-sm-12">
    <label for="answer319974X232X4271" class="hide label">
        <?php eT('Answer') ?>
    </label>

<textarea class="form-control textarea  empty" name="319974X232X4271" id="answer319974X232X4271" rows="5" cols="12" onkeyup="checkconditions(this.value, this.name, this.type)">
<?php eT('Some text in this answer') ?>
</textarea>
</p>
<!-- end of answer -->

            </div>
</div>

<?php
}else{
?>
<!-- List Radio -->

<!-- answer -->
<div class="list-unstyled radio-list answers-list">


<!-- answer_row -->
<div id='javatbd319974X233X4277A1' class='col-xs-12 form-group answer-item radio-item radio'  >
    <input
        class="radio"
        type="radio"
        value="A1"
        name="319974X233X4277"
        id="answer319974X233X4277A1"
                onclick="if (document.getElementById('answer319974X233X4277othertext') != null) document.getElementById('answer319974X233X4277othertext').value='';checkconditions(this.value, this.name, this.type)"
        aria-labelledby="label-answer319974X233X4277A1"
     />
    <label for="answer319974X233X4277A1" class="control-label radio-label"></label>

    <!--
         The label text is provided inside a div,
         so final user can add paragraph, div, or whatever he wants in the subquestion text
         This field is related to the input thanks to attribute aria-labelledby
    -->
    <div class="label-text label-clickable" id="label-answer319974X233X4277A1">
        <?php eT('One'); ?>
    </div>
</div>
<!-- end of answer_row -->

<!-- answer_row -->
<div id='javatbd319974X233X4277A2' class='col-xs-12 form-group answer-item radio-item radio'  >
    <input
        class="radio"
        type="radio"
        value="A2"
        name="319974X233X4277"
        id="answer319974X233X4277A2"
                onclick="if (document.getElementById('answer319974X233X4277othertext') != null) document.getElementById('answer319974X233X4277othertext').value='';checkconditions(this.value, this.name, this.type)"
        aria-labelledby="label-answer319974X233X4277A2"
     />
    <label for="answer319974X233X4277A2" class="control-label radio-label"></label>

    <!--
         The label text is provided inside a div,
         so final user can add paragraph, div, or whatever he wants in the subquestion text
         This field is related to the input thanks to attribute aria-labelledby
    -->
    <div class="label-text label-clickable" id="label-answer319974X233X4277A2">
        <?php eT('Two'); ?>
    </div>
</div>
<!-- end of answer_row -->

<!-- answer_row -->
<div id='javatbd319974X233X4277A3' class='col-xs-12 form-group answer-item radio-item radio'  >
    <input
        class="radio"
        type="radio"
        value="A3"
        name="319974X233X4277"
        id="answer319974X233X4277A3"
                onclick="if (document.getElementById('answer319974X233X4277othertext') != null) document.getElementById('answer319974X233X4277othertext').value='';checkconditions(this.value, this.name, this.type)"
        aria-labelledby="label-answer319974X233X4277A3"
     />
    <label for="answer319974X233X4277A3" class="control-label radio-label"></label>

    <!--
         The label text is provided inside a div,
         so final user can add paragraph, div, or whatever he wants in the subquestion text
         This field is related to the input thanks to attribute aria-labelledby
    -->
    <div class="label-text label-clickable" id="label-answer319974X233X4277A3">
        <?php eT('Three'); ?>
    </div>
</div>
<!-- end of answer_row -->

<!-- answer_row_other -->
<div id='javatbd319974X233X4277other' class='col-xs-12 form-group answer-item radio-item radio'  >
    <!-- Checkbox + label -->
    <div class="pull-left othertext-label-checkox-container">
        <input
        class="radio"
        type="radio"
        value="-oth-"
        name="319974X233X4277"
        id="SOTH319974X233X4277"
                onclick="checkconditions(this.value, this.name, this.type)"
        aria-labelledby="label-SOTH319974X233X4277"
        />

        <label for="SOTH319974X233X4277" class="answertext control-label label-radio"></label>

        <!--
             The label text is provided inside a div,
             so final user can add paragraph, div, or whatever he wants in the subquestion text
             This field is related to the input thanks to attribute aria-labelledby
        -->
        <div class="label-text label-clickable" id="label-SOTH319974X233X4277">
                <?php eT('Other:');?>&nbsp;
        </div>
    </div>

    <!-- comment -->
    <div class="pull-left ">
        <input
        type="text"
        class="form-control text  input-sm"
        id="answer319974X233X4277othertext"
        name="319974X233X4277other"
        title="Other"  value=""        onkeyup="if($.trim($(this).val())!=''){ $('#SOTH319974X233X4277').click(); };  checkconditions(this.value, this.name, this.type)"
        />
    </div>
</div>
<!-- end of answer_row_other -->

<!-- answer_row_noanswer -->
<div  class="col-xs-12 form-group answer-item radio-item no-anwser-item radio">
    <input
    class="radio"
    type="radio"
    name="319974X233X4277"
    id="answer319974X233X4277"
    value=""
     checked="checked"    onclick="if (document.getElementById('answer319974X233X4277othertext') != null) document.getElementById('answer319974X233X4277othertext').value='';checkconditions(this.value, this.name, this.type)"
    aria-labelledby="label-answer319974X233X4277"
    />
    <label for="answer319974X233X4277" class="answertext control-label label-radio"></label>

    <!--
         The label text is provided inside a div,
         so final user can add paragraph, div, or whatever he wants in the subquestion text
         This field is related to the input thanks to attribute aria-labelledby
    -->
    <div class="label-text label-clickable" id="label-answer319974X233X4277">
        <?php eT("No answer");?>
    </div>
</div>
<!-- endof answer_row_noanswer -->

</div>

<?php
}
?>
<!-- endof templateeditor_question_answer_view -->
