<!-- The file upload form used as target for the file upload widget -->
<?php echo CHtml::beginForm($this->url, 'post', $this->htmlOptions); ?>
<div class="fileupload-buttonbar">
    <div class="span7">
        <!-- The fileinput-button span is used to style the file input field as button -->
		<span class="btn btn-success fileinput-button"> <i class="icon-plus icon-white"></i> <span>Add files...</span>
			<?php
			if ($this->hasModel()) :
				echo CHtml::activeFileField($this->model, $this->attribute, $htmlOptions) . "\n"; else :
				echo CHtml::fileField($name, $this->value, $htmlOptions) . "\n";
			endif;
			?>
		</span>
        <button type="submit" class="btn btn-primary start">
            <i class="icon-upload icon-white"></i>
            <span>Start upload</span>
        </button>
        <button type="reset" class="btn btn-warning cancel">
            <i class="icon-ban-circle icon-white"></i>
            <span>Cancel upload</span>
        </button>
        <button type="button" class="btn btn-danger delete">
            <i class="icon-trash icon-white"></i>
            <span>Delete</span>
        </button>
        <input type="checkbox" class="toggle">
    </div>
    <div class="span5 fileupload-progress fade">
        <!-- The global progress bar -->
        <div class="progress progress-success progress-striped active" role="progressbar">
            <div class="bar" style="width:0%;"></div>
        </div>
        <!-- The extended global progress information -->
        <div class="progress-extended">&nbsp;</div>
    </div>
</div>
<!-- The loading indicator is shown during image processing -->
<div class="fileupload-loading"></div>
<br>
<!-- The table listing the files available for upload/download -->
<div class="row-fluid">
    <table class="table table-striped">
        <tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody>
    </table>
</div>
<?php echo CHtml::endForm(); ?>

