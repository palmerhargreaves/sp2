<?php
/**
 * Created by PhpStorm.
 * User: kostet
 * Date: 02.08.2017
 * Time: 15:16
 */

class DiscussionOnlineFactory {
    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DiscussionOnlineFactory();
        }

        return self::$_instance;
    }

    /**
     * Create discussion online class by user type | dealer | importer | admin
     * @param User $user
     * @param sfWebRequest $request
     * @return mixed
     */
    public function createClass(User $user, sfWebRequest $request, sfWebResponse $response) {
        $cls = ucfirst($this->getCurrentUserType($user)).'DiscussionOnline';
        //$cls = 'AdminDiscussionOnline';
        //$cls = 'DealerDiscussionOnline';
        //$cls = 'ImporterDiscussionOnline';

        return new $cls($user, $request, $response);
    }

    private function getCurrentUserType($user) {
        if ($user->isSuperAdmin()) {
            return 'admin';
        }

        if (!$user->isSuperAdmin() && $user->isDealerUser()) {
            return 'dealer';
        }

        if ($user->isImporter() || $user->isSpecialist())  {
            return 'importer';
        }
    }

}
