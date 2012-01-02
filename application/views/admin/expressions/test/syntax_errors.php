<?php
$errors = LimeExpressionManager::GetSyntaxErrors();
?>
<table class='showsyntaxerrors' border='1'">
<thead>
    <tr>
        <th>time</th>
        <th>sid</th>
        <th>gid</th>
        <th>qid</th>
        <th>gseq</th>
        <th>qseq</th>
        <th>Type</th>
        <th>Source Expression</th>
        <th>Syntax Highlighted</th>
    </tr>
</thead>
<tbody>
    <?php if(count($errors)){?>
        <?php foreach ($errors as $error){?>
            <tr>
                <td><?php echo $error['errortime'];?></td>
                <td><a href="<?php echo $this->createUrl('/admin/survey/index/surveyid/'.$error['sid']);?>">
                    <?php echo $error['sid'];?>
                    </a>
                </td>
                <td><a href="<?php echo $this->createUrl('/admin/questiongroup/organize/surveyid'.$error['sid'].'/gid/'.$error['gid']);?>">
                    <?php echo $error['gid'];?>
                    </a>
                </td>
                <td><a href="<?php echo $this->createUrl('/admin/questiongroup/organize/surveyid/'.$error['sid'].'/gid/'.$error['gid'].'/qid/'.$error['qid']);?>">
                    <?php echo $error['qid'];?>
                    </a>
                </td>
                <td><?php echo $error['gseq'];?></td>
                <td><?php echo $error['qseq'];?></td>
                <td><?php echo $error['type'];?></td>
                <td><?php echo htmlspecialchars($error['eqn']);?></td>
                <td><?php echo $error['prettyprint'];?></td>
            <?php } ?>
        <?php } ?>
</tbody>
</table>