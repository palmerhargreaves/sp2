<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 28.08.2018
 * Time: 11:23
 */

?>
<?php if ($sf_user->getAuthUser()->isAdmin() || $sf_user->getAuthUser()->isImporter()): ?>
    <div class="special-agreement-concept-target group open">
        <div class="group-header">
            <span class="title">Цели</span>
        </div>

        <div class="group-content">
            <div id="information-block-wrapper">
                <table class="models">
                    <tr>
                        <td class="content-column">
                            <div style="margin: 15px 0px 10px 15px;">
                                <?php
                                if ($activity->haveSpecialAgreementActivityInformationBlock($sf_user->getAuthUser())): ?>
                                    <?php echo html_entity_decode($activity->getRawValue()->getSpecialAgreementActivityInformationBlock($sf_user->getAuthUser())); ?>
                                <?php endif; ?>
                            </div>

                            <textarea id="activity-information-text">
                                                                <?php echo $activity->getSpecialAgreementActivityInformationBlock($sf_user->getAuthUser()); ?>
                                                            </textarea>

                            <button id="js-save-information-block"
                                    data-activity-id="<?php echo $activity->getId(); ?>"
                                    data-dealer-id="<?php echo $sf_user->getAuthUser()->getDealerUsers()->getFirst()->getDealerId(); ?>"
                                    style="margin-top: 12px;"
                                    class="float-right button modal-zoom-button modal-form-button modal-form-submit-button submit-btn"
                                    type="submit">
                                <span>Сохранить</span>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?php elseif ($activity->haveSpecialAgreementActivityInformationBlock($sf_user->getAuthUser())): ?>
    <div class="group open">
        <div class="group-header">
            <span class="title">Цели</span>
        </div>

        <div class="group-content">
            <table class="models">
                <tr>
                    <td class="content-column">
                        <div style="margin: 15px 0px 10px 15px;">
                            <?php echo html_entity_decode($activity->getRawValue()->getSpecialAgreementActivityInformationBlock($sf_user->getAuthUser())); ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php endif; ?>
