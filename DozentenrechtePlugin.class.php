<?php

require 'bootstrap.php';

/**
 * DozentenrechtePlugin.class.php
 *
 * ...
 *
 * @author  Florian Bieringer <florian.bieringer@uni-passau.de>
 * @version 0.1a
 */
class DozentenrechtePlugin extends StudIPPlugin implements SystemPlugin {

    const CRON = "DozentenrechteCronjob.php";
    const ROOT_NAME = "Dozentenrechte - Root";

    public function __construct() {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(realpath(__DIR__.'/models'));

        // Localization
        bindtextdomain('dozentenrechte', __DIR__ . '/locale');
        if ($this->have_perm('dozent')) {
            $navigation = new Navigation(dgettext('dozentenrechte', 'Dozentenrechte'));
            if ($this->have_perm('root')) {
                $navigation->setURL(PluginEngine::GetURL($this, [], 'show/accept'));
            } else if ($this->have_perm('admin')) {
                $navigation->setURL(PluginEngine::GetURL($this, [], 'show/given/99'));
            } else {
                $navigation->setURL(PluginEngine::GetURL($this, [], 'show'));
            }

            if ($this->have_perm('root')) {
                $subnavigation = new Navigation(dgettext('dozentenrechte', 'Anträge bestätigen'));
                $subnavigation->setURL(PluginEngine::GetURL($this, [], 'show/accept'));
                $navigation->addSubNavigation('accept', $subnavigation);
            }

            if (!$GLOBALS['perm']->have_perm('admin')) {
                $subnavigation = new Navigation(dgettext('dozentenrechte', 'Für mich gestellte Anträge'));
                $subnavigation->setURL(PluginEngine::GetURL($this, [], 'show'));
                $navigation->addSubNavigation('self', $subnavigation);
            }

            $subnavigation = new Navigation(dgettext('dozentenrechte', 'Von mir gestellte Anträge'));
            $subnavigation->setURL(PluginEngine::GetURL($this, [], 'show/given'));
            $navigation->addSubNavigation('given', $subnavigation);

            $subnavigation = new Navigation(dgettext('dozentenrechte', 'Neuer Antrag'));
            $subnavigation->setURL(PluginEngine::GetURL($this, [], 'show/new'));
            $navigation->addSubNavigation('new', $subnavigation);

            if ($this->have_perm('root')) {
                $subnavigation = new Navigation(dgettext('dozentenrechte', 'Suche'));
                $subnavigation->setURL(PluginEngine::GetURL($this, [], 'show/search'));
                $navigation->addSubNavigation('search', $subnavigation);

                // Trigger action if two users are merged.
                NotificationCenter::addObserver($this, 'updateAppliances', 'UserDidMigrate');
            }

            Navigation::addItem('tools/dozentenrechteplugin', $navigation);
        }
    }

    public function initialize() {
        $navigation = Navigation::getItem('tools/dozentenrechteplugin');

    }

    public function perform($unconsumed_path) {
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
                $this->getPluginPath(), rtrim(PluginEngine::getLink($this, [], null), '/'), 'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    private function setupAutoload() {
        if (class_exists("StudipAutoloader")) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }

    public static function onEnable($pluginId) {
        parent::onEnable($pluginId);
        $task_id = CronjobScheduler::registerTask(self::getCronName(), true);
        //CronjobScheduler::scheduleOnce($task_id, strtotime('+1 minute'));
        CronjobScheduler::schedulePeriodic($task_id, 30, 0);
    }

    public static function onDisable($pluginId) {
        $task_id = CronjobTask::findByFilename(self::getCronName());
        CronjobScheduler::unregisterTask($task_id[0]->task_id);
        parent::onDisable($pluginId);
    }

    private static function getCronName() {
        return "public/plugins_packages/intelec/DozentenrechtePlugin/" . DozentenrechtePlugin::CRON;
        $plugin = PluginEngine::getPlugin(__CLASS__);
        $path = $plugin->getPluginPath();
        return dirname($path) . "/" . DozentenrechtePlugin::CRON;
    }

    public static function have_perm($perm) {
        return $GLOBALS['perm']->have_perm($perm) || (defined("static::ROOT_NAME") && RolePersistence::isAssignedRole($GLOBALS['user']->id, static::ROOT_NAME));
    }
    
    public static function check($perm) {
        if (!static::have_perm($perm)) {
            throw new AccessDeniedException;
        }
    }

    /**
     * Update appliances on user merge.
     * @param $event UserDidMigrate
     * @param $old_id old user ID
     * @param $new_id new user ID
     */
    public function updateAppliances($event, $old_id, $new_id)
    {
        $appliances = Dozentenrecht::findBySQL("`for_id` = :user OR `from_id` = :user ORDER BY `mkdate` DESC",
            ['user' => $old_id]);
        foreach ($appliances as $a) {
            try {
                if ($a->from_id == $old_id) {
                    $a->from_id = $new_id;
                } else {
                    $a->for_id = $new_id;
                }
                if (!$a->store()) {
                    $a->delete();
                }
            } catch (Exception $e) {
                $a->delete();
            }
        }
    }

    /*
     * Returns the tables containing user data.
     * the array consists of the tables containing user data
     * the expected format for each table is:
     * $array[ table display name ] = [ 'table_name' => name of the table, 'table_content' => array of db rows containing userdata]
     * @param string $user_id
     * @return array
     */
    public static function getUserdataInformation($user_id)
    {

        $data = [];

        $for_me = DBManager::get()->fetchAll("SELECT
                        IFNULL(i.`Name`, d.`institute_id`) AS institute,
                        FROM_UNIXTIME(d.`begin`, '%Y-%m-%d %H:%i') AS begin,
                        FROM_UNIXTIME(d.`end`, '%Y-%m-%d %H:%i') AS end,
                        d.`status`,
                        d.`rights`,
                        FROM_UNIXTIME(d.`mkdate`, '%Y-%m-%d %H:%i') AS mkdate,
                        FROM_UNIXTIME(d.`chdate`, '%Y-%m-%d %H:%i') AS chdate
                    FROM `dozentenrechte` d
                        LEFT JOIN `Institute` i ON (i.`Institut_id` = d.`institute_id`)
                    WHERE d.`for_id` = ?
                    ORDER BY d.`chdate`", [$user_id]);

        if (count($for_me) > 0) {
            $data['Für mich gestellte Dozentenrechtsanträge'] =
                ['table_name' => 'dozentenrechte', 'table_content' => $for_me];
        }

        $by_me = DBManager::get()->fetchAll("SELECT
                        IFNULL(i.`Name`, d.`institute_id`) AS institute,
                        FROM_UNIXTIME(d.`begin`, '%Y-%m-%d %H:%i') AS begin,
                        FROM_UNIXTIME(d.`end`, '%Y-%m-%d %H:%i') AS end,
                        d.`status`,
                        d.`rights`,
                        FROM_UNIXTIME(d.`mkdate`, '%Y-%m-%d %H:%i') AS mkdate,
                        FROM_UNIXTIME(d.`chdate`, '%Y-%m-%d %H:%i') AS chdate
                    FROM `dozentenrechte` d
                        LEFT JOIN `Institute` i ON (i.`Institut_id` = d.`institute_id`)
                    WHERE d.`from_id` = ?
                    ORDER BY d.`chdate`", [$user_id]);

        if (count($by_me) > 0) {
            $data['Von mir gestellte Dozentenrechtsanträge'] =
                ['table_name' => 'dozentenrechte', 'table_content' => $by_me];
        }

        return $data;
    }

    /**
     * Returns the filerefs of given user.
     * @param string $user_id
     * @return array
     */
    public static function getUserFileRefs($user_id)
    {
        return [];
    }

    /**
     * Deletes the table content containing user data.
     * @param string $user_id
     * @return boolean
     */
    public static function deleteUserdata($user_id)
    {
        return false;
    }

}
