<?php
class TutorRights extends DBMigration
{
    function up(){
        DBManager::get()->exec("ALTER TABLE `dozentenrechte` ADD `rights` ENUM( 'dozent', 'tutor' ) NOT NULL DEFAULT 'dozent'");
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `dozentenrechte` DROP `rights`");
    }

}