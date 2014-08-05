<?php

/**
 * FullUserSearch.php - Search functions needed for searching ALL users,
 * including ones with visibility "unknown" or "no".
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 */

class FullUserSearch extends StandardSearch {

    private $search;

    /**
     *
     * @param string $search
     *
     * @return void
     */
    public function __construct($search) {
        $this->avatarLike = $this->search = $search;
        $this->sql = $this->getSQL();
    }

    /**
     * returns an object of type SQLSearch with parameters to constructor
     *
     * @param string $search
     *
     * @return SQLSearch
     */
    static public function get($search) {
        return new FullUserSearch($search);
    }

    /**
     * returns the title/description of the searchfield
     *
     * @return string title/description
     */
    public function getTitle() {
        return _("Nutzer suchen");
    }

    /**
     * returns a sql-string appropriate for the searchtype of the current class
     *
     * @return string
     */
    private function getSQL() {
        return "SELECT DISTINCT `auth_user_md5`.`user_id`, CONCAT(`auth_user_md5`.`Vorname`, \" \", `auth_user_md5`.`Nachname`, \" (\", `auth_user_md5`.`username`,\")\") " .
                "FROM `auth_user_md5` LEFT JOIN `user_info` ON (`user_info`.`user_id` = `auth_user_md5`.`user_id`) " .
                "WHERE (CONCAT(`auth_user_md5`.`Vorname`, \" \", `auth_user_md5`.`Nachname`) LIKE :input " .
                    "OR `auth_user_md5`.`username` LIKE :input) " .
                "ORDER BY `Nachname`, `Vorname`";
    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     *
     * @return: path to this class
     */
    public function includePath() {
        return __file__;
    }
}
