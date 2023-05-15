<div class="accordion <?=$class?>" id="<?= $id ?>">
    <?php foreach ($items as $item) : ?>
    <div class="accordion-item" style="<?=$item['style']?>">
        <h2 class="accordion-header" id="<?=$item['id']?>">
            <button
                class="accordion-button"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#<?=$item['id']?>-body"
                aria-expanded="true"
                aria-controls="<?=$item['id']?>">
                <?=gT($item['title'])?>
            </button>
        </h2>
        <div
            id="<?=$item['id']?>-body"
            class="accordion-collapse collapse show"
            aria-labelledby="<?=$item['id']?>"
           >
            <div class="accordion-body">
                <?=$item['content']?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
