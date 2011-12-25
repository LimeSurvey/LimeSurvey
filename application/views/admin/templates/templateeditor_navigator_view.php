<?php
if ($screenname != 'welcome')
{
?>
    <input class="submit" type="submit" value="&lt;&lt;<?php echo $clang->gT('Previous') ?>" name="move" />
<?php
}
?>
<input class="submit" type="submit" value="<?php echo $clang->gT('Next') ?>&gt;&gt;" name="move" />
