<ol class="sortable">
<?php foreach ($aGroupsAndQuestions as  $aGroupAndQuestions)
    {?>
    <li><div> <?php echo $aGroupAndQuestions['gid'];?></div>
    <?php if (isset ($aGroupAndQuestions['questions']))
        {?>
        <ol>
            <?php
                foreach($aGroupAndQuestions['questions'] as $aQuestion)
                {?>
                <li><div> <?php echo $aQuestion['title'];?></div></li>

                <?php }?>
        </ol>
        <?php }?>
    <li>
    <?php
}?>
</ol>
