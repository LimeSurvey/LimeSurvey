<div id="modalPlanUpgrade" class="modal fade border-10" role="dialog" style="margin: auto; background-color:rgba(0, 0, 0, 0.5);">
    <div class="modal-dialog border border-light border-2" style="margin-top: 3em;">
        <!-- Modal content-->
        <div class="modal-content">
            <?php if ($currentPlan == 'free') : ?>
                <!-- Upgrade to Expert -->
                <?php Yii::app()->getController()->renderPartial(
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
                            <td class="pt-2"><?php eT("More active admin user"); ?></td>
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
            <?php elseif ($currentPlan == 'expert') : ?>
                <!-- Upgrade to Enterprise -->
                <?php Yii::app()->getController()->renderPartial(
                    '/layouts/partial_modals/modal_header',
                    ['modalTitle' => gT('Activate more users with LimeSurvey Enterprise')]
                );
                ?>
                <div class="modal-body pt-0 pb-0">
                    <p class='modal-body-text'><?php eT('Need more active users to collaborate with? Upgrade your plan to enterprise and activate more users to collaborate with. Enjoy priority support and many more advantages.'); ?></p>
                    <table class="mb-1">
                        <tr>
                            <th class="pt-2 col-md-8"><?php eT("Benefits you get"); ?></th>
                            <th class="pt-2 col-md-3 text-center text-secondary"><?php eT("Your plan"); ?></th>
                            <th class="pt-2 col-md-3 text-center"><?php eT("Enterprise"); ?></th>
                        </tr>
                        <tr>
                            <td class="pt-2"><?php eT("More active admin user"); ?></td>
                            <td class="pt-2 col-md-2 text-center text-secondary font-weight-normal">3</td>
                            <td class="pt-2 col-md-2 text-center font-weight-normal">5</td>
                        </tr>
                        <tr>
                            <td class="pt-2"><?php eT("More responses/year"); ?></td>
                            <td class="pt-2 col-md-2 text-center text-secondary">10 000</td>
                            <td class="pt-2 col-md-2 text-center">100 000</td>
                        </tr>
                        <tr>
                            <td class="pt-2"><?php eT("1 alias domain"); ?></td>
                            <td class="pt-2 col-md-2 text-center text-secondary"><i class="ri-close-line text-dark"></i></td>
                            <td class="pt-2 col-md-2 text-center"><i class="ri-check-line text-success"></i></td>
                        </tr>
                        <tr>
                            <td class="pt-2"><?php eT("Priority support"); ?></td>
                            <td class="pt-2 col-md-2 text-center text-secondary"><i class="ri-close-line text-dark"></i></td>
                            <td class="pt-2 col-md-2 text-center"><i class="ri-check-line text-success"></i></td>
                        </tr>
                    </table>
                </div>
            <?php else : ?>
                <!-- Upgrade to Corporate -->
                <?php Yii::app()->getController()->renderPartial(
                    '/layouts/partial_modals/modal_header',
                    ['modalTitle' => gT('Activate more users with LimeSurvey Corporate')]
                );
                ?>
                <div class="modal-body pt-0 pb-0">
                    <p class='modal-body-text'><?php eT('Upgrade your plan to corporate for a maximum of flexibility with unlimited active users to collaborate with on your surveys. Contact our sales team.'); ?></p>
                    <table class="mb-1">
                        <tr>
                            <th class="pt-2 col-md-8"><?php eT("Benefits you get"); ?></th>
                            <th class="pt-2 col-md-3 text-center text-secondary"><?php eT("Your plan"); ?></th>
                            <th class="pt-2 col-md-3 text-center"><?php eT("Corporate"); ?></th>
                        </tr>
                        <tr>
                            <td class="pt-2"><?php eT("Flex. active admin user"); ?></td>
                            <td class="pt-2 col-md-2 text-center text-secondary font-weight-normal">5</td>
                            <td class="pt-2 col-md-2 text-center font-weight-normal">10+</td>
                        </tr>
                        <tr>
                            <td class="pt-2"><?php eT("Flex. responses/year"); ?></td>
                            <td class="pt-2 col-md-2 text-center text-secondary">100 000</td>
                            <td class="pt-2 col-md-2 text-center">100 000+</td>
                        </tr>
                        <tr>
                            <td class="pt-2"><?php eT("SSO, LDAP, SAML"); ?></td>
                            <td class="pt-2 col-md-2 text-center text-secondary"><i class="ri-close-line text-dark"></i></td>
                            <td class="pt-2 col-md-2 text-center"><i class="ri-check-line text-success"></i></td>
                        </tr>
                        <tr>
                            <td class="pt-2"><?php eT("VIP support"); ?></td>
                            <td class="pt-2 col-md-2 text-center text-secondary"><i class="ri-close-line text-dark"></i></td>
                            <td class="pt-2 col-md-2 text-center"><i class="ri-check-line text-success"></i></td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
            <div class="modal-footer">
                <?php if ($currentPlan == 'Enterprise') : ?>
                    <a href="https://account.limesurvey.org/contact-corporate" class="btn btn-primary btn-block w-100">&nbsp;<?php eT("Contact sales"); ?></a>
                <?php else : ?>
                    <a href="https://account.limesurvey.org/pricing" class="btn btn-primary btn-block w-100">&nbsp;<?php eT("Upgrade now"); ?></a>
                <?php endif; ?>
                <button class="text-secondary btn-block w-100 border-0 bg-white mt-3" data-bs-dismiss="modal">&nbsp;<?php eT("No, thanks"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    function modalPlanUpgradeOpen() {
        var id = '#modalPlanUpgrade'
        var modal = new bootstrap.Modal(id)
        modal.show();

        // to prevent stackable modal from opening after the Plan upgrade modal
        $(id).off('hidden.bs.modal').on('hidden.bs.modal', function (e) {
            e.preventDefault()
        });
    }
</script>
