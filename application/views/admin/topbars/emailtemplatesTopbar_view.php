<!-- emailtemplatesTopbar -->
<div class='menubar surveybar' id="surveybarid">
    <div class='row container-fluid row-button-margin-bottom'>
        <!-- Left Buttons -->
        <div class="col-md-4">
            
        </div>

        <!-- Right Buttons -->
        <div class="col-sm-4 pull-right text-right">

            <!-- Save -->
            <?php if(Permission::model()->hasSurveyPermission($surveyid, 'surveylocale', 'update')): ?>
            <a id="save-button" class="btn btn-success" role="button">
                <i class="fa fa-floppy-o"></i>
                <?php eT("Save");?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

