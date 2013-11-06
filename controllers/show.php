<?php
class ShowController extends StudipController {

    public function before_filter(&$action, &$args) {

		$this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'));
//		PageLayout::setTitle('');

    }

    public function index_action() {
        $this->rights = Dozentenrecht::findByFor_id($GLOBALS['user']->id);
    }
    
    public function new_action() {
        
    }
    
    public function given_action() {
        
    }

    // customized #url_for for plugins
    function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map("urlencode", $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join("/", $args));
    } 
}
