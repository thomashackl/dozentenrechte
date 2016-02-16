<?php

class Cleanup extends DBMigration {

    function up() {

        // Delete applications for non-existing users.
        DBManager::get()->execute("DELETE FROM `dozentenrechte` WHERE `for_id` NOT IN (SELECT `user_id` FROM `auth_user_md5`)");

        // Fetch duplicates.
        $duplicates = DBManager::get()->fetchAll("SELECT `from_id`, `for_id`, `institute_id`, COUNT(*) AS applications
            FROM `dozentenrechte`
            WHERE `status` < 3
            GROUP BY `from_id`, `for_id`, `institute_id`
            HAVING applications > 1");

        $stmt = DBManager::get()->prepare();

        foreach ($duplicates as $d) {
            $entries = DBManager::get()->fetchAll("SELECT *
                FROM `dozentenrechte`
                WHERE `from_id` = :from
                    AND `for_id` = :for
                    AND `institute_id` = :inst
                    AND `verify` = 1
                    AND `status` < 3",
                array('from' => $d['from_id'], 'for' => $d['for_id'], 'inst' => $d['institute_id']));
            $minStart = 0;
            $maxEnd = 0;
            $firstId = 0;
            // Find min and max validity dates for current right.
            foreach ($entries as $entry) {
                if (!$firstId) {
                    $firstId = $entry['id'];
                }
                $minStart = min($minStart, $entry['begin']);
                $maxEnd = max($maxEnd, $entry['end']);
            }

            // Update first found entry with new min and max times.
            DBManager::get()->execute("UPDATE `dozentenrechte` SET `begin` = :begin, `end` = :end WHERE `id` = :id",
                array('begin' => $minStart, 'end' => $maxEnd, 'id' => $firstId));

            // Delete all other entries for given person at given institute.
            DBManager::get()->execute("DELETE FROM `dozentenrechte`
                WHERE `from_id` = :from AND `for_id` = :for AND `institute_id` = :inst AND `id` != :id",
                array('from' => $d['from_id'], 'for' => $d['for_id'], 'inst' => $d['institute_id'], 'id' => $firstId));
        }
    }

    function down() {
    }

}
