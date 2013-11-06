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

    public function __construct() {
        parent::__construct();

        $toolnavi = Navigation::getItem('tools');
        $navigation = new AutoNavigation(_('Dozentenrechte'));
        $navigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
        Navigation::addItem('tools/dozentenrechteplugin', $navigation);

        $subnavigation = new AutoNavigation(_('Meine Anträge'));
        $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
        $navigation->addSubNavigation('self', $subnavigation);

        $subnavigation = new AutoNavigation(_('Gestellte Anträge'));
        $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/given'));
        $navigation->addSubNavigation('given', $subnavigation);

        $subnavigation = new AutoNavigation(_('Neuer Antrag'));
        $subnavigation->setURL(PluginEngine::GetURL($this, array(), 'show/new'));
        $navigation->addSubNavigation('new', $subnavigation);
    }

    public function initialize() {
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

}
