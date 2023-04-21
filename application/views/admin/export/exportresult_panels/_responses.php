<div class="card mb-4" id="panel-5">
    <div class="card-header ">
        <div class="">
            <?php eT("Responses");?>
        </div>
    </div>
    <div class="card-body">
        <div class='mb-3'>
            <label class="col-md-12 form-label" for=''>
                <?php eT("Export responses as:"); ?>
            </label>
            <!-- Answer codes / Full answers -->
            <div class="btn-group">
                <input class="btn-check" name="answers" value="short" type="radio" id="answers-short" autocomplete="off" />
                <label class="btn btn-outline-secondary" for="answers-short">
                    <?php eT("Answer codes");?>
                </label>

                <input class="btn-check" name="answers" value="long" type="radio" id="answers-long" autocomplete="off" checked />
                <label class="btn btn-outline-secondary" for="answers-long">
                    <?php eT("Full answers");?>
                </label>
            </div>
        </div>

        <!-- Responses  -->
        <div class="mb-3">
            <?= CHTML::checkBox('converty', false, ['value' => 'Y', 'id' => 'converty']) ?>
            <?= ' ' . CHTML::label(gT("Convert Y to:"), 'converty', ['class' => 'form-label']) ?>
            <?= CHTML::textField('convertyto', '1', ['id' => 'convertyto', 'size' => '3', 'maxlength' => '1', 'class' => 'form-control']) ?>
        </div>
        <div class="mb-3">
            <?= CHTML::checkBox('convertn', false, ['value' => 'Y', 'id' => 'convertn']) ?>
            <?= ' ' . CHTML::label(gT("Convert N to:"), 'convertn', ['class' => 'form-label']) ?>
            <?= CHTML::textField('convertnto', '2', ['id' => 'convertnto', 'size' => '3', 'maxlength' => '1', 'class' => 'form-control']) ?>

        </div>
        <div class="mb-3">
            <?= CHTML::checkBox('maskequations', true, ['value' => 'Y', 'id' => 'maskequations']) ?>
            <?= ' ' . CHTML::label(gT("Quote equations for CSV export"), 'maskequations', ['class' => 'form-label']) ?>
        </div>
    </div>
</div>
