<div class="col-lg-12 attribute-control">
    <h3 class="pagetitle"><?php eT("Attribute management"); ?></h3>

    <div class="row">
        <div class="container-fluid">
        <div class="row">

        </div>
        <div class="row">
            <?php
            $this->widget('bootstrap.widgets.TbGridView', array(
                'id' => 'list_attributes',
                'itemsCssClass' => 'table table-striped items',
                'dataProvider' => $model->search(),
                'columns' => $model->columns,
                'filter'=>$model,
                'htmlOptions' => array('class'=> 'table-responsive'),
                'itemsCssClass' => 'table table-responsive table-striped',
                'afterAjaxUpdate' => 'bindButtons',
                'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSizeAttributes',
                                $pageSizeAttributes,
                                Yii::app()->params['pageSizeOptions'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))
                            ),
                    ));
                ?>
            </div>
    </div>

            <div id="pager">

            </div>
        </div>
    </div>
</div>
