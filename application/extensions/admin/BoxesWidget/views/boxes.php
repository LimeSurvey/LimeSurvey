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
    </div>
</div>
