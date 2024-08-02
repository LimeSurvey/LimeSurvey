<div class="box-widget">
    <div class="row row-cols-<?php echo $boxesbyrow ?>">
        <?php $this->render('box', array('items' => $items)); ?>
    </div>
    <div class="btn-container">
        <a href="#" id="load-more" data-page="1" data-limit="<?php echo $limit?>">Load more</a>
    </div>
</div>
