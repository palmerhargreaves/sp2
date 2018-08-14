<div id="concept" class="active">
    <?php if ($concept): ?>
        <div class="border">
            <table id="activity-concept" class="models concept">
                <tbody>

                <?php
                foreach ($concept as $conceptItem) {
                    include_partial('concept_item', array('concept' => $conceptItem));
                }
                ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <?php include_partial('concept_empty_item'); ?>
    <?php endif; ?>

    <?php if ($activity->getManyConcepts() || $activity->getAllowSpecialAgreement()): ?>
        <div id="model-many-concepts" style="width: 100%; display: block; float: left;">
            <div id="add-model-concept-button" class="add small button"
                 style="float:left; margin-top: 10px; z-index: 9999;">Добавить концепцию
            </div>
        </div>
    <?php endif; ?>
</div>

<?php /*include_partial('concept_activity/modal_concept', array('activity' => $activity))*/ ?>
