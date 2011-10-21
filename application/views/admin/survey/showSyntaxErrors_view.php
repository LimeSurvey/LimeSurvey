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
                <th><?php $clang->eT("Source Expression"); ?></th>
                <th><?php $clang->eT("Syntax Highlighted"); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($errors)){?>
                <?php foreach ($errors as $error){?>
                    <tr>
                        <td><?php echo $error['errortime'];?></td>
                        <td><?php echo $error['sid'];?></td>
                        <td><?php echo $error['gid'];?></td>
                        <td><?php echo $error['qid'];?></td>
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
