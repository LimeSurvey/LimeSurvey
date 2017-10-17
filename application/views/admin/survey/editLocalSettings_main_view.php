<?php
/**
 * General container for edit survey action
 *
 * @var AdminController $this
 * @var Survey $oSurvey
 */

$templateData['oSurvey'] = $oSurvey;
?>

<script type="text/javascript">
    var formId = '<?=$entryData['name']?>';
</script>

<?php
$count = 0;
if(isset($scripts))
    echo $scripts;
?>

<div class="row col-12">
    <h3 class="pagetitle"><?php echo $entryData['title']; ?></h3>

    <!-- Edition container -->

    <!-- Form -->
    <div class="col-xs-12">
        <?php echo CHtml::form(array("admin/database/index/".$entryData['action']), 'post', array('id'=>$entryData['name'],'name'=>$entryData['name'],'class'=>' form30')); ?>

        <div class="row">
            <div class="<?=$entryData['classes']?>">
                <?php $this->renderPartial($entryData['partial'],$templateData); ?>
            </div>
        </div>

        <!--
        This hidden button is now necessary to save the form.
        Before, there where several nested forms in Global settings, which is invalid in html
        The submit button from the "import ressources" was submitting the whole form.
        Now, the "import ressources" is outside the global form, in a modal ( subview/import_ressources_modal.php)
        So the globalsetting form needs its own submit button
        -->
        <input type="hidden" name="action" value="<?=$entryData['action']?>" />
        <input type="hidden" name="sid" value="<?php echo $surveyid; ?>" />
        <input type="hidden" name="surveyid" value="<?php echo $surveyid; ?>" />
        <input type="hidden" name="language" value="<?php echo $surveyls_language; ?>" />
        <input type="hidden" name="responsejson" value="1" />
        <input type='submit' class="hide" id="globalsetting_submit" />
        </form>
    </div>
</div>
<script type="text/javascript">
$(document).on('ready pjax:complete', function(){

    $('#<?=$entryData['name']?>').off('submit');

    $('#<?=$entryData['name']?>').on('submit', function(e){
      e.preventDefault();
      var data = $(this).serializeArray();
      var uri = $(this).attr('action');
      $.ajax({
        url: uri,
        method:'POST',
        data: data,
        success: function(result){
            console.log({result: result});
          if(result.redirecturl != undefined ){
            window.location.href=result.redirecturl;
          } else {
            window.location.reload();
          }
        },
        error: function(result){
          console.log({result: result});
        }
      });
      return false;
    });
});
</script>
