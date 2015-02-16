<?php

class SimulatedRoot extends DBMigration {

    function up() {
        $role = new Role();
        $role->rolename = DozentenrechtePlugin::ROOT_NAME;
        RolePersistence::saveRole($role);
    }

    function down() {
        $role = new Role();
        $role->rolename = DozentenrechtePlugin::ROOT_NAME;
        RolePersistence::deleteRole($role);
    }

}
