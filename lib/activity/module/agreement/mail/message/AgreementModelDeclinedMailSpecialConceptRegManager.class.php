<?php

/**
 * Description of AgreementModelAcceptedMail
 *
 * @author Сергей
 */
class AgreementModelDeclinedMailSpecialConceptRegManager extends AgreementModelDeclinedMailSpecialConcept
{
    /**
     * @param AgreementModel $model
     * @return string
     */
    protected function getMailLabel($model)
    {
        return "Внесите комментарии регионального менеджера. ";
    }
}
