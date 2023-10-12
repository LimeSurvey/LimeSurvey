<div id="modalPlanUpgrade" class="modal fade border-10" role="dialog" style="margin: auto; background-color:rgba(200, 200, 200, 0.5);">
    <div class="modal-dialog border border-light border-2" style="margin-top: 2%;">
        <!-- Modal content-->
        <div class="modal-content">
            <?php
            Yii::app()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Activate more users with LimeSurvey Expert')]
            );
            ?>
            <div class="modal-body pt-0 pb-0">
                <p class='modal-body-text'><?php eT('Upgrade your plan and activate more users to collaborate with on your survey and enjoy all other benefits of the popular expert plan'); ?></p>
                <table class="mb-1">
                    <tr>
                        <th class="pt-2 col-md-8"><?php eT("Benefits you get"); ?></th>
                        <th class="pt-2 col-md-2 text-center text-secondary"><?php eT("Your plan"); ?></th>
                        <th class="pt-2 col-md-2 text-center"><?php eT("Expert"); ?></th>
                    </tr>
                    <tr>
                        <td class="pt-2"><?php eT("More admin user"); ?></td>
                        <td class="pt-2 col-md-2 text-center text-secondary font-weight-normal">1</td>
                        <td class="pt-2 col-md-2 text-center font-weight-normal">3</td>
                    </tr>
                    <tr>
                        <td class="pt-2"><?php eT("More responses/year"); ?></td>
                        <td class="pt-2 col-md-2 text-center text-secondary">1000</td>
                        <td class="pt-2 col-md-2 text-center">10 000</td>
                    </tr>
                    <tr>
                        <td class="pt-2"><?php eT("White-label domain"); ?></td>
                        <td class="pt-2 col-md-2 text-center text-secondary"><i class="ri-close-line text-dark"></i></td>
                        <td class="pt-2 col-md-2 text-center"><i class="ri-check-line text-success"></i></td>
                    </tr>
                    <tr>
                        <td class="pt-2"><?php eT("Remove LimeSurvey branding"); ?></td>
                        <td class="pt-2 col-md-2 text-center text-secondary"><i class="ri-close-line text-dark"></i></td>
                        <td class="pt-2 col-md-2 text-center"><i class="ri-check-line text-success"></i></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <a href="https://account.limesurvey.org/pricing" class="btn btn-primary btn-block w-100">&nbsp;<?php eT("Upgrade now"); ?></a>
                <button class="text-secondary btn-block w-100 border-0 bg-white mt-3" data-bs-dismiss="modal">&nbsp;<?php eT("No, thanks"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    function hideAllModals() {
        $('.modal').each(function(i) {
            $(this).hide();
        });
    }
    function modalPlanUpgradeOpen() {
        // hideAllModals();
        console.log('modalPlanUpgrade')
        var modal = new bootstrap.Modal(document.getElementById('modalPlanUpgrade'), {})
        modal.show();
    }
</script>
