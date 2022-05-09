<div class="card" id="panel-5">
    <div class="card-header bg-primary">
        <div class="">
            <?php eT("Responses");?>
        </div>
    </div>
    <div class="card-body">
        <div class='form-group row'>
            <label class="col-md-12 form-label" for=''>
                <?php eT("Export responses as:"); ?>
            </label>
            <!-- Answer codes / Full answers -->
            <div class="btn-group col-md-6" data-toggle="buttons">
                <label class="btn btn-outline-secondary">
                    <input name="answers" value="short" type="radio" id="answers-short" />
                    <?php eT("Answer codes");?>
                </label>

                <label class="btn btn-outline-secondary active">
                    <input name="answers" value="long" type="radio" checked='checked' id="answers-long" autofocus="true" />
                    <?php eT("Full answers");?>
                </label>
            </div>
        </div>

        <!-- Responses  -->
        <div class="form-group row">
            <div class='col-md-12'>
                <?php 
                echo CHTML::checkBox('converty',false,array('value'=>'Y','id'=>'converty'));
                echo ' '.CHTML::label(gT("Convert Y to:"),'converty');
                echo CHTML::textField('convertyto','1',array('id'=>'convertyto','size'=>'3','maxlength'=>'1', 'class' => 'form-control')); 
                ?>
            </div>
            <div class='col-md-12'>
                <?php 
                echo CHTML::checkBox('convertn',false,array('value'=>'Y','id'=>'convertn'));
                echo ' '.CHTML::label(gT("Convert N to:"),'convertn');
                echo CHTML::textField('convertnto','2',array('id'=>'convertnto','size'=>'3','maxlength'=>'1', 'class' => 'form-control')); 
                ?>
            </div>
        </div>
        <div class="form-group row">
            <div class='col-md-12'>
                <?php 
                echo CHTML::checkBox('maskequations',true,array('value'=>'Y','id'=>'maskequations'));
                echo ' '.CHTML::label(gT("Quote equations for CSV export"),'maskequations');
                ?>
            </div>
        </div>
    </div>
</div>
