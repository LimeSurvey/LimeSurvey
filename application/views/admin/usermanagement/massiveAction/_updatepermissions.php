<?php $aMySurveys = Survey::model()->findAll('owner_id=:oid', [':oid' => App()->user->id]);?>
<div class="container-center">
    <div class="row">
        <div class="col-xs-12">
            <form class="custom-modal-datas form form-horizontal">
                <div class="form-group ls-space margin top-5 bottom-5">
                    <label class="control-label">
                        Stufe wählen: 
                    </label>
                    <select class="form-control custom-data selector_submitField" name="permissionclass" id="smk--selector--permissionclass-mass">
                        <option value="surveymanager">Befragungsmanager</option>
                        <option value="classmanager">Gruppenmanager</option>
                    </select>
                </div>
                <div class="form-group ls-space margin top-5 bottom-5" id="smk--selector--surveypermission-mass" style="display:none;">
                    <label class="control-label">
                        Umfragen zur Berechtigung auswählen: (Nur bei Gruppenmanager)
                    </label>
                    <select class="form-control custom-data selector_submitField" name="entity_ids[]" multiple="multiple" id="smk--selector--entity-ids-mass">
                        <?php foreach($aMySurveys as $oSurvey) {
                            echo "<option value='".$oSurvey->sid."'>".$oSurvey->currentLanguageSettings->surveyls_title."</option>";
                        } ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
</div>