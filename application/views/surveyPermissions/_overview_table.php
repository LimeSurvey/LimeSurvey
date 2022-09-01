<?php

/* @var $basePermissions array the base permissions a user could have */

?>

<table class='surveysecurity table table-hover'>
    <thead>
    <tr>
        <th> <?= gT("Action") ?> </th>
        <th> <?= gT("Username") ?> </th>
        <th> <?= gT("User group") ?> </th>
        <th> <?= gT("Full name") ?> </th>
        <?php foreach ($basePermissions as $sPermission => $aSubPermissions) {
            echo "<th>" . $aSubPermissions['title'] . "</th>\n";
        } ?>
    </tr>
    </thead>

    <tbody>
    <?php //todo here we must show the data from db ... ?>
    </tbody>
</table>

