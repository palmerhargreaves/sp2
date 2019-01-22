<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 22.01.2019
 * Time: 11:31
 */

class control_point_term_of_loadingActions extends BaseActivityActions {

    public function executeIndex(sfWebRequest $request) {
        $this->current_year = date('Y');

        $this->initDealersList();

        $this->getControlPointTermsOfLoading($this->current_year);
    }

    public function executeChangeYear(sfWebRequest $request) {
        sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');

        $this->initDealersList();

        $selected_year = $request->getParameter('year');
        $this->getControlPointTermsOfLoading($selected_year);

        return $this->sendJson(array('data' => get_partial('dealers_list', array('control_point_terms_loading' => $this->control_point_terms_loading))));
    }

    /**
     * Изменить статус загрузки у дилера за период (год, квртал)
     * @param sfWebRequest $request
     * @return mixed
     */
    public function executeControlPointChangeStatus(sfWebRequest $request) {
        $year = $request->getParameter('year');
        $quarter = $request->getParameter('quarter');
        $dealer_id = $request->getParameter('dealer_id');
        $status = $request->getParameter('status');

        $control_point_item = DealersControlPointByTermsOfLoadingTable::getInstance()->createQuery()
            ->where('dealer_id = ? and year = ?', array($dealer_id, $year))
            ->fetchOne();

        $result = false;
        if ($control_point_item) {
            $result = true;

            $custom_function = 'setQ'.$quarter;

            $control_point_item->$custom_function($status);
            $control_point_item->save();
        }

        return $this->sendJson(array('success' => $result));
    }

    private function getControlPointTermsOfLoading($year) {
        $this->control_point_terms_loading = DealersControlPointByTermsOfLoadingTable::getInstance()->createQuery()->where('year = ?', $year)->execute();

        if (!count($this->control_point_terms_loading)) {
            foreach ($this->active_dealers_list as $dealer_item) {
                $control_point = new DealersControlPointByTermsOfLoading();
                $control_point->setArray(array(
                    'dealer_id' => $dealer_item['id'],
                    'year' => $year,
                    'q1' => 1,
                    'q2' => 1,
                    'q3' => 1,
                    'q4' => 1,
                ));
                $control_point->save();
            }

            $this->control_point_terms_loading = DealersControlPointByTermsOfLoadingTable::getInstance()->createQuery()->where('year = ?', $year)->execute();
        }
    }

    private function initDealersList() {
        $this->active_dealers_list = DealerTable::getVwDealersQuery()->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }
}
