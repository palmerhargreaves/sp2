<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 07.09.2016
 * Time: 13:28
 */

class ActivityExamplesUtils {

    private $_filter = null;
    private $_result = array();
    private $_years = array();

    public function __construct($filter = null)
    {
        $this->_filter = $filter;
    }

    public function build() {
        $gen_years = Utils::getYearsList(sfConfig::get('app_min_year_for_gen'), sfConfig::get('app_plus_years'));

        $query = ActivityExamplesMaterialsTable::getInstance()->createQuery('ae')
            ->select('ae.*, ae.created_at as item_created_at');

        $years_result = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        foreach ($years_result as $result) {
            if (array_key_exists($result['year'], $gen_years)) {
                $this->_years[$result['year']] = $gen_years[$result['year']];
            }
        }

        $this->_result = $this->getCategoriesList();
    }

    private function getCategoriesList($parent_category = 0)
    {
        $result = array();

        $base_categories = ActivityExamplesMaterialsCategoriesTable::getInstance()->createQuery()->where('parent_category_id = ?', $parent_category)->orderBy('name ASC')->execute();
        foreach ($base_categories as $category) {
            if (!isset($result[$category->getId()])) {
                $result[$category->getId()] = array('data' => $category, 'categories' => array(), 'items' => array(), 'active' => $category->getStatus());
            }

            $result[$category->getId()]['categories'] = $this->getCategoriesList($category->getId());

            $query = ActivityExamplesMaterialsTable::getInstance()->createQuery('ae')
                ->select('ae.*, ae.created_at as item_created_at')
                ->leftJoin('ae.ActivityExamplesMaterialsCategories a_cat')
                ->leftJoin('ae.Dealer d')
                ->where('category_id = ?', $category->getId())
                ->orderBy('a_cat.name ASC');

            if (!is_null($this->_filter)) {
                if (isset($this->_filter['by_year']) && $this->_filter['by_year'] != 0) {
                    $query->andWhere('ae.year = ?', $this->_filter['by_year']);
                }

                if (isset($this->_filter['by_text']) && !is_null($this->_filter['by_text'])) {
                    $query->andWhere('(ae.name LIKE ? or a_cat.name LIKE ? or d.name LIKE ? or d.number LIKE ?)',
                        array
                        (
                            '%'.$this->_filter['by_text'].'%',
                            '%'.$this->_filter['by_text'].'%',
                            '%'.$this->_filter['by_text'].'%',
                            '%'.$this->_filter['by_text'].'%',
                        )
                    );
                }
            }

            $result[$category->getId()]['items'] = $query->execute();
            $result[$category->getId()]['active'] = count($result[$category->getId()]['items']) ? true : false;
        }

        //Make preview file thumbnail
        foreach ($result as $cat_id => $items) {
            foreach ($items['items'] as $item) {
                $preview_file_thumb = $item->getPreviewFileThumbnail();
                if (empty($preview_file_thumb))
                {
                    $file_path = sfConfig::get('app_uploads_path').'/'.ActivityExamplesMaterials::FILE_PREVIEW_PATH.$item->getPreviewFile();
                    $make_thumb = new UploadFilesHelper($file_path);

                    $file_name = pathinfo($file_path, PATHINFO_FILENAME);
                    if ($make_thumb->image_src_x > 224) {
                        $make_thumb->file_new_name_body = 'thumb_' . $file_name;
                        $make_thumb->image_resize = true;
                        //$make_thumb->image_y = 154;
                        $make_thumb->image_x = 224;
                        $make_thumb->image_ratio_y = true;
                        //$make_thumb->image_ratio_x = true;
                        $make_thumb->process(sfConfig::get('app_uploads_path') . '/' . ActivityExamplesMaterials::FILE_PREVIEW_THUMB_PATH);

                        if ($make_thumb->processed) {
                            $item->setPreviewFileThumbnail('thumb_' . $item->getPreviewFile());
                            $item->save();
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getData() {
        return $this->_result;
    }

    public function getYears() {
        return $this->_years;
    }
}