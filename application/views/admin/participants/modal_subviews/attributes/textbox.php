    <label class=" control-label" for="Attributes[<?=CHtml::encode($name); ?>]"><?php echo $defaultname; ?></label>
    <div>
        <div class=''>
        <input class="form-control" name="Attributes[<?=CHtml::encode($name); ?>]" id="Attributes_<?=CHtml::encode($name); ?>" type="text" maxlength="254" value="<?=CHtml::encode($value); ?>">
        </div>
        