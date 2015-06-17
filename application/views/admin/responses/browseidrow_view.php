<tr <?php echo $inserthighlight; ?>>
    <th>
        <?php if(isset($fnames[$i]['code'])){ ?>
            [<strong class="qcode"><?php echo $fnames[$i]['code']; ?></strong>] 
        <?php }?>
        <?php echo strip_tags(stripJavaScript($fnames[$i][1])); ?></th>
    <td>
        <?php echo $answervalue; ?>
    </td>
</tr>
