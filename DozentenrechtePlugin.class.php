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
                $navigation->setURL(PluginEngine::GetURL($this, array(), 'show/accept'));
            } else if ($this->have_perm('admin')) {
                $navigation->setURL(PluginEngine::GetURL($this, array(), 'show/given/99'));
            } else {
                $navigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
            }

            if ($this->have_perm('root')) {
                $subnavigation = new Navigation(dgettext('dozentenrechte', 'Anträge bestätigen'));
                $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/accept'));
                $navigation->addSubNavigation('accept', $subnavigation);
            }

            if (!$GLOBALS['perm']->have_perm('admin')) {
                $subnavigation = new Navigation(dgettext('dozentenrechte', 'Für mich gestellte Anträge'));
                $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
                $navigation->addSubNavigation('self', $subnavigation);
            }

            $subnavigation = new Navigation(dgettext('dozentenrechte', 'Von mir gestellte Anträge'));
            $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/given'));
            $navigation->addSubNavigation('given', $subnavigation);

            $subnavigation = new Navigation(dgettext('dozentenrechte', 'Neuer Antrag'));
            $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/new'));
            $navigation->addSubNavigation('new', $subnavigation);

            if ($this->have_perm('root')) {
                $subnavigation = new Navigation(dgettext('dozentenrechte', 'Suche'));
                $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/search'));
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
                $this->getPluginPath(), rtrim(PluginEngine::getLink($this, array(), null), '/'), 'show'
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
            array('user' => $old_id));
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

}
