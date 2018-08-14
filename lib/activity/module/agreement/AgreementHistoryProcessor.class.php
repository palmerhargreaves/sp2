<?php

/**
 * History processor of the activity module
 *
 * @author Сергей
 */
class AgreementHistoryProcessor implements HistoryProcessor
{
    public function getSourceUri(LogEntry $entry)
    {
        $user = sfContext::getInstance()->getUser();

        if ($entry->getObjectType() == 'agreement_special_concept_by_reg_manager' || $entry->getObjectType() == 'agreement_special_concept_report_regional_manager')
            return $this->getSourcesUriForAgreement('regional_manager', $entry);
        elseif ($entry->getObjectType() == 'agreement_concept_report_by_importer' || $entry->getObjectType() == 'agreement_concept_only_by_importer' || $entry->getObjectType() == 'agreement_special_concept_importer' || $entry->getObjectType() == 'agreement_special_concept_report_importer')
            return $this->getSourcesUriForAgreement('importer', $entry);
        elseif ($entry->getAction() == 'sent_to_specialist' && $user->isSpecialist())
            return $this->getSourcesUriForAgreement('specialist', $entry);
        elseif ($user->isManager() || $user->isImporter())
            return $this->getSourcesUriForAgreement('management', $entry);
        elseif ($user->isSpecialist())
            return $this->getSourcesUriForAgreement('specialist', $entry);
        elseif ($user->isDealerUser())
            return $this->getSourceUriForDealer($entry);
        else
            return false;
    }

    public function getModelNumber(LogEntry $entry)
    {
        switch ($entry->getObjectType()) {
            case 'model_message':
                $message = MessageTable::getInstance()->find($entry->getObjectId());
                if (!$message)
                    return false;

                $model = AgreementModelTable::getInstance()->findOneByDiscussionId($message->getDiscussionId());
                if (!$model)
                    return 0;

                return $model->getId();

            case 'agreement_model':
            case 'agreement_concept':
                $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
                if (!$model)
                    return 0;

                return $model->getId();

            case 'agreement_report':
            case 'agreement_concept_report':
                $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
                if (!$model)
                    return 0;

                return $model->getId();
        }

        return 0;
    }

    protected function getSourcesUriForAgreement($mode, LogEntry $entry)
    {
        switch ($entry->getObjectType()) {
            case 'model_message':
                $message = MessageTable::getInstance()->find($entry->getObjectId());
                if (!$message)
                    return false;

                $model = AgreementModelTable::getInstance()->findOneByDiscussionId($message->getDiscussionId());
                if (!$model)
                    return false;

                return "@agreement_module_{$mode}_models#model/" . $model->getId() . '/discussion/' . $model->getDiscussionId() . '/message/' . $entry->getObjectId();

            case 'agreement_special_concept_by_reg_manager':
            case 'agreement_special_concept_importer':
            case 'agreement_concept_only_by_importer':
            case 'agreement_model':
            case 'agreement_concept':
                $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
                if (!$model)
                    return false;

                return "@agreement_module_{$mode}_models#model/" . $entry->getObjectId() . '/discussion/' . $model->getDiscussionId() . '/model';


            case 'agreement_report':
            case 'agreement_concept_report':
            case 'agreement_special_concept_report_regional_manager':
            case 'agreement_special_concept_report_importer':
            case 'agreement_concept_report_by_importer':
                $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
                if (!$model)
                    return false;

                return "@agreement_module_{$mode}_models#model/" . $entry->getObjectId() . '/discussion/' . $model->getDiscussionId() . '/report';
        }

        return false;
    }

    protected function getSourceUriForDealer(LogEntry $entry)
    {
        switch ($entry->getObjectType()) {
            case 'model_message':
                $message = MessageTable::getInstance()->find($entry->getObjectId());
                if (!$message)
                    return false;

                $model = AgreementModelTable::getInstance()->findOneByDiscussionId($message->getDiscussionId());
                if (!$model)
                    return false;

                return $this->getActivityUri(function () use ($model) {
                    return $model->getActivity();
                }, 'model/' . $model->getId() . '/discussion/' . $model->getDiscussionId() . '/message/' . $entry->getObjectId());


            case 'agreement_model':
            case 'agreement_concept':
                $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
                if (!$model)
                    return false;

                return $this->getActivityUri(function () use ($model) {
                    return $model->getActivity();
                }, 'model/' . $entry->getObjectId() . '/discussion/' . $model->getDiscussionId() . '/model');


            case 'agreement_model_blank':
                $blank = AgreementModelBlankTable::getInstance()->find($entry->getObjectId());
                if (!$blank)
                    return false;

                return $this->getActivityUri(function () use ($blank) {
                    return $blank->getActivity();
                }, '');


            case 'agreement_report':
            case 'agreement_concept_report':
                $model = AgreementModelTable::getInstance()->find($entry->getObjectId());
                if (!$model)
                    return false;

                return $this->getActivityUri(function () use ($model) {
                    return $model->getActivity();
                }, 'model/' . $entry->getObjectId() . '/discussion/' . $model->getDiscussionId() . '/report');
        }

        return false;
    }

    protected function getActivityUri(Closure $activity_func, $params)
    {
        $activity = $activity_func();

        return $activity ? '@agreement_module_models?activity=' . $activity->getId() . '#' . $params : false;
    }
}
