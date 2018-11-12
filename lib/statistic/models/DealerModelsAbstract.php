<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 29.11.2016
 * Time: 16:43
 */

abstract class DealerModelsAbstract {
    const MODEL_STATUS_ALL = 'all';
    const MODEL_STATUS_DECLINED = 'declined';
    const MODEL_STATUS_WAIT = 'wait';
    const MODEL_STATUS_NO_REPORT = 'no_report';

    const MODEL_REPORT_WAIT = 'wait_report';

    const MODEL_BLOCKED = 'blocked';

    protected $_filter = null;
    protected $_params = null;

    protected $_result = arraY('models' => array(), 'total_amount' => 0);

    private $_request = null;

    public function __construct($params)
    {
        $this->_params = $params;

        $this->makeFilter();
        $this->getData();
    }

    /**
     * Make filter
     */
    protected function makeFilter() {
        $this->_request = $this->_params['request'];

        if (isset($this->_params['default_filter']) && !is_null($this->_params['default_filter'])) {
            $this->_filter['quarter'] = $this->_params['default_filter']['quarter'];
            $this->_filter['year'] = $this->_params['default_filter']['year'];
            $this->_filter['dealer_id'] = $this->_params['default_filter']['dealer_id'];

            if (isset($this->_params['default_filter']['activity_id'])) {
                $this->_filter['activity'] = $this->_params['default_filter']['activity'];
            }
        } else {
            $this->_filter['quarter'] = $this->_request->getPostParameter('quarter', D::getQuarter(D::calcQuarterData(time())));
            $this->_filter['year'] = $this->_request->getPostParameter('year', D::getYear(D::calcQuarterData(time())));
            $this->_filter['activity'] = $this->_request->getPostParameter('activity', -1);
            $this->_filter['model_status'] = $this->_request->getPostParameter('model_status', self::MODEL_STATUS_ALL);
            $this->_filter['dealer_id'] = $this->_request->getPostParameter('dealer_id', -1);
        }

    }

    /**
     * Make base query
     * @return Doctrine_Query
     */
    protected function makeBaseQuery($filter_by_activity = true) {
        $query = AgreementModelTable::getInstance()->createQuery('am')->orderBy('am.id DESC');

        if (isset($this->_filter['activity']) && $this->_filter['activity'] != -1 && !empty($this->_filter['activity']) && $filter_by_activity) {
            $query->andWhere('am.activity_id = ?', $this->_filter['activity']);
        }

        if (isset($this->_filter['dealer_id']) && $this->_filter['dealer_id'] != -1) {
            $query->andWhere('am.dealer_id = ?', $this->_filter['dealer_id']);
        }

        //Удаленные заявки не выбираем
        $query->andWhere('is_deleted = ?', false);

        return $query;
    }

    /**
     * Make query and get list of completed models and reports
     */
    protected function getData() { }
}
