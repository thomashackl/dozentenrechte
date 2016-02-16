<?php

class ExtendRights extends DBMigration {

    function up() {
        DBManager::get()->exec("ALTER TABLE `dozentenrechte` ADD `ref_id` INT NULL DEFAULT NULL REFERENCES `id` AFTER `status`");
        SimpleORMap::expireTableScheme();
    }

    function down() {
        DBManager::get()->exec("ALTER TABLE `dozentenrechte` DROP `ref_id`");
        SimpleORMap::expireTableScheme();
    }

}
