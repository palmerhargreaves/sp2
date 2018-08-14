<?php

/**
 * Description of AgreementModelAcceptedMail
 *
 * @author Сергей
 */
class AgreementModelDeclinedMailSpecialConceptImporter extends AgreementModelDeclinedMailSpecialConcept
{
    /**
     * @param AgreementModel $model
     * @return string
     */
    protected function getMailLabel($model)
    {
        return "Внесите комментарии специалиста. ";
    }
}
