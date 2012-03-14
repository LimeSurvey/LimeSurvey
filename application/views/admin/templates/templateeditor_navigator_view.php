<?php
if ($screenname != 'welcome')
{
?>
    <input class="submit" type="submit" value="&lt;&lt;<?php $clang->eT('Previous') ?>" name="move" />
<?php
}
?>
<input class="submit" type="submit" value="<?php $clang->eT('Next') ?>&gt;&gt;" name="move" />
