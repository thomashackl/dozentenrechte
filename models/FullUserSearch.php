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

    /**
     * returns a sql-string appropriate for the searchtype of the current class
     *
     * @return string
     */
    private function getSQL() {
        return "SELECT DISTINCT `auth_user_md5`.`user_id`, CONCAT(`auth_user_md5`.`Vorname`, \" \", `auth_user_md5`.`Nachname`, \" (\", `auth_user_md5`.`username`,\")\") " .
                "FROM `auth_user_md5` LEFT JOIN `user_info` ON (`user_info`.`user_id` = `auth_user_md5`.`user_id`) " .
                "WHERE (CONCAT(`auth_user_md5`.`Vorname`, \" \", `auth_user_md5`.`Nachname`) LIKE :input " .
                    "OR (CONCAT(`auth_user_md5`.`Nachname`, \" \", `auth_user_md5`.`Vorname`) LIKE :input " .
                    "OR `auth_user_md5`.`username` LIKE :input) " .
                    "AND `auth_user_md5`.`visible` != 'never' " .
                "ORDER BY `Nachname`, `Vorname`";
    }

}
