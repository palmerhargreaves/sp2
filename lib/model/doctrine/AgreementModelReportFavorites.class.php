<?php

/**
 * AgreementModelReportFavorites
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    Servicepool2.0
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class AgreementModelReportFavorites extends BaseAgreementModelReportFavorites
{
    private $allowed_ext = array('jpg', 'jpeg', 'gif', 'png');

    public function isImage($file) {
        if (empty($file)) {
            return false;
        }

        $pathinfo = pathinfo($file);
        if (in_array($pathinfo['extension'], $this->allowed_ext)) {
            return true;
        }

        return false;
    }
}