<?php
/**
 * This view display the right container of the updater
 * The ajax code change the active step.
 */
?>

<div id="updaterContent">
    <!-- the ajax loader -->
    <div id="ajaxContainerLoading">
        <p><?php eT('Please wait, loading data...');?></p>
        <img src="<?php echo Yii::app()->baseUrl;?>/images/ajax-loader.gif" alt="loading..."/>    <br/>
    </div>
    
    <!-- Here come the different steps content. Content is loaded by the ajax request (see ./steps for html views)  -->
    <div id="updaterContainer">
        <!-- content loaded by ajax -->
    </div>      
</div>