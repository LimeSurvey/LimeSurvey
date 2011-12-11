<br />
    <table class='showsyntaxerrors'>
        <thead>
            <tr>
                <th><?php $clang->eT("time"); ?></th>
                <th><?php $clang->eT("sid"); ?></th>
                <th><?php $clang->eT("gid"); ?></th>
                <th><?php $clang->eT("qid"); ?></th>
                <th><?php $clang->eT("gseq"); ?></th>
                <th><?php $clang->eT("qseq"); ?></th>
                <th><?php $clang->eT("Type"); ?></th>
                <th><?php $clang->eT("Source expression"); ?></th>
                <th><?php $clang->eT("Syntax highlighted"); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($errors)){?>
                <?php foreach ($errors as $error){?>
                    <tr>
                        <td><?php echo $error['errortime'];?></td>
                        <td><a href="<?php echo site_url('admin/survey/view/'.$error['sid']);?>">
                            <?php echo $error['sid'];?>
                            </a>
                        </td>
                        <td><a href="<?php echo site_url('admin/survey/view/'.$error['sid'].'/'.$error['gid']);?>">
                            <?php echo $error['gid'];?>                                
                            </a>
                        </td>
                        <td><a href="<?php echo site_url('admin/question/editquestion/'.$error['sid'].'/'.$error['gid'].'/'.$error['qid']);?>">
                            <?php echo $error['qid'];?>
                            </a>
                        </td>
                        <td><?php echo $error['gseq'];?></td>
                        <td><?php echo $error['qseq'];?></td>
                        <td><?php echo $clang->eT($error['type']);?></td>
                        <td><?php echo htmlspecialchars($error['eqn']);?></td>
                        <td><?php echo $error['prettyprint'];?></td>
                    <?php } ?>
                <?php } ?>
        </tbody>
    </table>
<br />
