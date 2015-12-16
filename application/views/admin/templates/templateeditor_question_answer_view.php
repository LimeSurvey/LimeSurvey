<?php
if (isset($alt))
{
?>
<p class="question answer-item text-item "><label class="hide label" for="answer1295X1X2"><?php eT('Answer') ?></label>
<textarea cols="40" rows="5" alt="<?php eT('Answer') ?>" id="answer1295X1X2" name="1295X1X2" class="textarea form-control"><?php eT('Some text in this answer') ?></textarea></p>
<?php
}else{
?>
<div class="col-sm-12 answer">
    <div class="row">    <div class="col-xs-12">
        <div class="form-group">    <label for="answer975363X1X4A1" class="answertext control-label">One</label>        <input class="radio" value="A1" name="975363X1X4" id="answer975363X1X4A1" onclick="if (document.getElementById('answer975363X1X4othertext') != null) document.getElementById('answer975363X1X4othertext').value='';checkconditions(this.value, this.name, this.type)" type="radio">
        </div>
        <div class="form-group">    <label for="answer975363X1X4A2" class="answertext control-label">Two</label>        <input class="radio" value="A2" name="975363X1X4" id="answer975363X1X4A2" onclick="if (document.getElementById('answer975363X1X4othertext') != null) document.getElementById('answer975363X1X4othertext').value='';checkconditions(this.value, this.name, this.type)" type="radio">
        </div>
        <div class="form-group">    <label for="answer975363X1X4A3" class="answertext control-label">Three</label>        <input class="radio" value="A3" name="975363X1X4" id="answer975363X1X4A3" onclick="if (document.getElementById('answer975363X1X4othertext') != null) document.getElementById('answer975363X1X4othertext').value='';checkconditions(this.value, this.name, this.type)" type="radio">
        </div> <!-- wrapper row -->
    </div>
</div>
<?php
}
?>
