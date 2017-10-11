<?php
/* @var $this SurveymenuEntriesController */
/* @var $dataProvider CActiveDataProvider */

// $this->breadcrumbs=array(
// 	'Surveymenu Entries',
// );

// $this->menu=array(
// 	array('label'=>'Create SurveymenuEntries', 'url'=>array('create')),
// 	array('label'=>'Manage SurveymenuEntries', 'url'=>array('admin')),
// );

$pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
    ?>
  <div class="container-fluid ls-space padding left-50 right-50">
    <div class="ls-flex-column ls-space padding left-35 right-35">
      <div class="col-12 h1">
        <?php eT('Menu entries')?>
        <a class="btn btn-primary pull-right col-xs-6 col-sm-3 col-md-2" id="createnewmenuentry" >
            <i class="fa fa-plus"></i>&nbsp;<?php eT('New menu entry') ?>
        </a>
        <?php if(Permission::model()->hasGlobalPermission('superadmin','read')):?>
            <a class="btn btn-danger pull-right ls-space margin right-10 col-xs-6 col-sm-3 col-md-2" href="#reset-menu-entries" data-toggle="modal">
                <i class="fa fa-refresh"></i>&nbsp;<?php eT('Reset menu-entries') ?>
            </a>
        <?php endif; ?>
      </div>

      <div class="ls-flex-row">
        <div class="col-12 ls-flex-item">
          <?php 
            $this->widget('bootstrap.widgets.TbGridView', array(
                    'dataProvider' => $model->search(),
                    'id' => 'surveymenu-entries-grid',
                    'columns' => $model->getColumns(),
                    'filter' => $model,
                    'emptyText'=>gT('No customizable entries found.'),
                    'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto')
                        )
                    ),
                    'itemsCssClass' =>'table table-striped',
                    'rowHtmlOptionsExpression' => '["data-surveymenu-entry-id" => $data->id]',
                    'htmlOptions'=>array('style'=>'cursor: pointer;', 'class'=>'hoverAction grid-view col-12'),
                    'ajaxType' => 'POST',
                    'ajaxUpdate' => true,
                    'afterAjaxUpdate'=>'surveyMenuEntryFunctions.bindAction',
                ));
            ?>
        </div>
      </div>
    </div>
  </div>

  <input type="hidden" id="surveymenu_open_url_selected_entry" value="" />
  <!-- modal! -->

  <div class="modal fade" id="editcreatemenuentry" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
      </div>
    </div>
  </div>

  <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><?php eT("Really delete this surveymenu?");?></h4>
        </div>
        <div class="modal-body">
          <?php eT("All menuentries of this menu will also be deleted."); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <?php eT('Cancel'); ?>
          </button>
          <button type="button" id="deletemodal-confirm" class="btn btn-danger">
            <?php eT('Delete now'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="reset-menu-entries" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><?php eT("Really restore the default surveymenu entries?");?></h4>
        </div>
        <div class="modal-body">
          <p><?php eT("All custom menu entries will be lost."); ?></p>
          <p><?php eT("Please do a backup of the menu entries you want to keep."); ?></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <?php eT('Cancel'); ?>
          </button>
          <button type="button" id="reset-menu-entries-confirm" class="btn btn-danger">
            <?php eT('Yes, restore default'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    var SurveyMenuEntryFunctions = function(urls) {
        
        var _editCreateModal = function(callback, menuEntryId){
            menuEntryId = menuEntryId || null;
            var loadSurveyEntryFormUrl = "<?php echo Yii::app()->urlManager->createUrl('/admin/menuentries/sa/getsurveymenuentryform' ) ?>";
            $('#editcreatemenuentry').find('.modal-content').load(loadSurveyEntryFormUrl, {menuentryid: menuEntryId}, function(response, status, xhr) {
                console.log(status);
                $('#surveymenu-entries-form').on('submit', function(evt) {
                    evt.preventDefault();
                    var data = $('#surveymenu-entries-form').serializeArray();
                    var url = $('#surveymenu-entries-form').attr('action');
                    $.ajax({
                        url: url,
                        data: data,
                        method: 'POST',
                        dataType: 'json',
                        success: callback,
                        error: function(error) {
                            console.log(error);
                        }
                    });
                });
            });
            $('#editcreatemenuentry').modal('show');
        };

      return {
        runCreateModal: function(){
            return _editCreateModal(
                function(data) {
                    $('#editcreatemenuentry').modal('hide');
                    $('#editcreatemenuentry').off('show.bs.modal');
                    $.fn.yiiGridView.update('surveymenu-entries-grid');
                  }
              );
        },
        runEditModal: function(menuEntryId){
            return _editCreateModal(
                function(data) {
                    $('#editcreatemenuentry').modal('hide');
                    $('#editcreatemenuentry').off('show.bs.modal');
                    $.fn.yiiGridView.update('surveymenu-entries-grid');
                  },
                  menuEntryId
              );
        },
        runDeleteModal: function(menuEntryid){
            $('#deletemodal').modal('show');
            $('#deletemodal').on('shown.bs.modal', function() {
              $('#deletemodal-confirm').on('click', function() {
                var url = "<?php echo Yii::app()->getController()->createUrl('/admin/menuentries/sa/delete'); ?>";
                $.ajax({
                  url: url,
                  data: {
                    menuEntryid: menuEntryid,
                    ajax: true
                  },
                  method: 'post',
                  success: function(data) {
                    window.location.reload();
                  },
                  error: function(err) {
                    window.location.reload();
                  }
                })
              })
            });
        },
        runRestoreModal: function(){
            $('#reset-menu-entries').find('.modal-content').html('<div class="ls-flex align-items-center align-content-center" style="height:200px"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div>')
            $.ajax({
                url: "<?php echo Yii::app()->getController()->createUrl('/admin/menuentries/sa/restore'); ?>",
                data: {},
                method: 'POST',
                dataType: 'json',
                success: function(result){
                    console.log(result);
                    $('#reset-menu-entries').find('.modal-content').html('<div class="ls-flex align-items-center align-content-center" style="height:200px">'+result.message+'</div>');
                    
                    if(result.success)
                        setTimeout(function(){window.location.reload();}, 1500);
                }
            });
        }
      };
    };
    var surveyMenuEntryFunctions = new SurveyMenuEntryFunctions();
    var bindAction = function() {
          $('#reset-menu-entries-confirm').on('click', function(e) {
            e.preventDefault();
            surveyMenuEntryFunctions.runRestoreModal();
          });

          $('#createnewmenuentry').on('click', function(e) {
              e.stopPropagation();
              e.preventDefault();
              console.log(e);
            surveyMenuEntryFunctions.runCreateModal();
          });

          $('#editcreatemenuentry').on('hidden.bs.modal', function() {
            $(this).find('.modal-content').html('');
          });

          $('#surveymenu-entries-grid').on('click', 'tr', function() {
            $(this).find('.action_selectthisentry').prop('checked', !$(this).find('.action_selectthisentry').prop('checked'));
          });
          $('.action_selectthisentry').on('click', function(e) {
            e.stopPropagation();
          });

          $('.action_surveymenuEntries_editModal').on('click', function(e){
              e.stopPropagation();
              e.preventDefault();
            var menuEntryid = $(this).closest('tr').data('surveymenu-entry-id');
            surveyMenuEntryFunctions.runEditModal(menuEntryid);  
          })

          $('.action_surveymenuEntries_deleteModal').on('click', function(e) {
              e.stopPropagation();
              e.preventDefault();
            var menuEntryid = $(this).closest('tr').data('surveymenu-entry-id');
            surveyMenuEntryFunctions.runDeleteModal(menuEntryid);
          });
        };

    $(document).on('ready pjax:complete',bindAction);
  </script>
