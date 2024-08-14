<?php
    /**
    * Renders a container with a search box and a list of survey items, if the user has the necessary permissions.
    *
    * @var bool $searchBox Indicates whether to display the search box.
    * @var bool $enableLoadMoreBtn Controls the visibility of the "Load more" button.
    * @var int $limit The number of items to load per page when "Load more" is clicked.
    * @var array $items The list of survey items to render.
    * @var object $switch Optionally used in the search box widget.
    *
    */
?>
<div class="container">
    <div class="col-12">
        <?php if ($this->searchBox) : ?>
            <?php $this->controller->widget('ext.admin.SearchBoxWidget.SearchBoxWidget', [
                'model'      => new Survey('search'),
                'onlyfilter' => true,
                'switch'     => $this->switch
            ]);
            ?>
        <?php endif; ?>
        <?php if (Permission::model()->hasGlobalPermission('surveys', 'read')) : ?>
            <div class="row">
                <div class="box-widget p-0">
                    <div class="box-widget-list">
                        <?php $this->render('box', ['items' => $items]); ?>
                    </div>
                    <div class="box-widget-loadmore <?= !$enableLoadMoreBtn ? 'd-none' : '' ?>">
                        <a href="#" id="load-more" data-page="1" data-limit="<?= $limit ?>">
                            <?= gT('Load more') ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php elseif (!Permission::model()->hasGlobalPermission('surveys', 'create')
            || !Permission::model()->hasGlobalPermission('surveysgroups', 'create')
        ) : ?>
            <?php echo gT('No surveys found.'); ?>
        <?php endif; ?>
    </div>
</div>
