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

    public function __construct() {
        parent::__construct();
        
        if ($GLOBALS['perm']->have_perm('dozent')) {
            $navigation = new AutoNavigation(_('Dozentenrechte'));
            if ($GLOBALS['perm']->have_perm('root')) {
                $navigation->setURL(PluginEngine::GetURL($this, array(), 'show/accept'));
            } else {
                $navigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
            }
            Navigation::addItem('tools/dozentenrechteplugin', $navigation);
        }
    }

    public function initialize() {
        $navigation = Navigation::getItem('tools/dozentenrechteplugin');

        if ($GLOBALS['perm']->have_perm('root')) {
            $subnavigation = new AutoNavigation(_('Anträge bestätigen'));
            $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/accept'));
            $navigation->addSubNavigation('accept', $subnavigation);
        }

        $subnavigation = new AutoNavigation(_('Meine Anträge'));
        $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
        $navigation->addSubNavigation('self', $subnavigation);

        $subnavigation = new AutoNavigation(_('Gestellte Anträge'));
        $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/given'));
        $navigation->addSubNavigation('given', $subnavigation);

        $subnavigation = new AutoNavigation(_('Neuer Antrag'));
        $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/new'));
        $navigation->addSubNavigation('new', $subnavigation);

        if ($GLOBALS['perm']->have_perm('root')) {
            $subnavigation = new AutoNavigation(_('Suche'));
            $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/search'));
            $navigation->addSubNavigation('search', $subnavigation);
        }

        PageLayout::addStylesheet($this->getPluginURL() . '/assets/style.css');
        PageLayout::addScript($this->getPluginURL() . '/assets/application.js');
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

}
