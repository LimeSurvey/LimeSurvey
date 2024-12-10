<?php
    /**
    * Renders a container with a search box and a list of survey items, if the user has the necessary permissions.
    *
    * @var bool $searchBox Indicates whether to display the search box.
    * @var bool $enableLoadMoreBtn Controls the visibility of the "Load more" button.
    * @var int $limit The number of items to load per page when "Load more" is clicked.
    * @var array $items The list of survey items to render.
    * @var object $switch Optionally used in the search box widget.
    * @var object $itemsCount max number of surveys.
    *
    */
?>
<div>
    <?php if ($this->searchBox) : ?>
        <?php $this->controller->widget('ext.admin.SearchBoxWidget.SearchBoxWidget', [
            'model'      => new Survey('search'),
            'onlyfilter' => true,
            'switch'     => $this->switch
        ]);
        ?>
    <?php endif; ?>
        <div class="row">
            <div class="box-widget p-0">
                <div class="box-widget-list">
                    <?php $this->render('box', ['items' => $items]); ?>
                </div>
                <div class="box-widget-loadmore">
                    <a href="#" id="load-more" data-page="1" data-limit="<?= $limit ?>" data-max-count="<?= $itemsCount ?>">
                        <?= gT('Load more') ?>
                    </a>
                </div>
            </div>
        </div>
</div>
