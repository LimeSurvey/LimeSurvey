<div id='tabs'>
    <ul>
        <li><a href='#general'><?php echo $clang->gT("General"); ?></a></li>
        <li><a href='#presentation'><?php echo $clang->gT("Presentation & navigation"); ?></a></li>
        <li><a href='#publication'><?php echo $clang->gT("Publication & access control"); ?></a></li>
        <li><a href='#notification'><?php echo $clang->gT("Notification & data management"); ?></a></li>
        <li><a href='#tokens'><?php echo $clang->gT("Tokens"); ?></a></li>
        <li><a href='#import'><?php echo $clang->gT("Import"); ?></a></li>
        <li><a href='#copy'><?php echo $clang->gT("Copy"); ?></a></li>
    </ul>
    <form class='form30' name='addnewsurvey' id='addnewsurvey' action='<?php echo site_url("admin/database/index/insertsurvey"); ?>' method='post' onsubmit="alert('hi');return isEmpty(document.getElementById('surveyls_title'), '<?php echo $clang->gT("Error: You have to enter a title for this survey.", 'js'); ?> ');" >
    <div id='general'>
    <ul><li>
    
    