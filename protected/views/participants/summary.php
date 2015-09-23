<div class="row">
    <div class="col-md-6 col-md-offset-3">
    <?php
    echo \TbHtml::tag('h1', [], gT("Central participants database summary"));
    $this->widget(WhDetailView::class, [
        'data' => $data
    ]);
    ?>
        </div>
</div>