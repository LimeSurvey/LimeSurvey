<?php
/** @var int $ugid */
/** @var bool $groupfound */
/** @var string $usergroupdescription */
/** @var array $headercfg */
/** @var bool $useradddialog */
/** @var array $addableUsers */
/** @var User $model */
$dataProvider = $model->searchUserGroupMembers($ugid);
?>

<div class="col-12 list-surveys">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 content-right">
                <div class="h4"><?php eT("Group members"); ?></div>

                <?php if (isset($groupfound)) : ?>
                    <strong><?php eT("Group description: "); ?></strong>
                    <?php echo htmlspecialchars($usergroupdescription); ?>

                <?php endif; ?>

                <br/><br/>

                <?php if (isset($headercfg)) : ?>
                    <?php if ($headercfg["type"] === "success") : ?>
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <?php echo $headercfg["message"]; ?>
                        </div>

                    <?php else : ?>
                        <div class="alert alert-warning alert-dismissible" role="alert">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <?php echo $headercfg["message"]; ?>
                        </div>

                    <?php endif; ?>
                <?php endif; ?>

                <br/><br/>
                <?php
                $this->widget('application.extensions.admin.grid.CLSGridView',
                    [
                        'id' => 'usergroup-members-grid',
                        'dataProvider' => $dataProvider,
                        'filter' => $model,
                        'ajaxType'        => 'POST',
                        'columns' => [
                            array(
                                'header'      => gT('Actions'),
                                'name'        => 'buttons',
                                'type'        => 'raw',
                                'filter'      => false,
                                'value'       => '$data->GroupMemberListButtons',
                                'htmlOptions' => array('class' => 'text-start'),
                            ),
                            array(
                                'header'      => gT('Username'),
                                'name'        => 'users_name',
                                'value'       => '$data->users_name',
                                'htmlOptions' => array('class' => ''),
                            ),
                            array(
                                'header'      => gT('Email'),
                                'name'        => 'email',
                                'value'       => '$data->email',
                                'htmlOptions' => array('class' => ''),
                            ),
                        ],
                        'emptyText' => gT('No user group members found.'),
                        'ajaxUpdate' => 'usergroup-members-grid',
                        'massiveActionTemplate' => $this->renderPartial('_addUserDropdown', [
                            'useradddialog' => $useradddialog,
                            'addableUsers' => $addableUsers,
                            'ugid' => $ugid
                        ], true),
                        'summaryText' => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(
                                gT('%s rows per page'),
                                CHtml::dropDownList(
                                    'pageSize',
                                    $dataProvider->pagination->getPageSize(),
                                    App()->params['pageSizeOptions'],
                                    [
                                        'class' => 'changePageSize form-select',
                                        'style' => 'display: inline; width: auto'
                                    ]
                                )
                            ),
                    ]
                );
                ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        // To update rows per page via ajax
        $(document).on("change", '#pageSize', function () {
            $.fn.yiiGridView.update('usergroup-members-grid', {data: {pageSize: $(this).val()}});
        });
    });
</script>
