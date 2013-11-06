<?php

class ShowController extends StudipController {

    public function before_filter(&$action, &$args) {
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'));
    }

    public function index_action() {
        $this->rights = Dozentenrecht::findByFor_id($GLOBALS['user']->id);
    }

    public function new_action() {
        if (Request::submitted('save')) {
            
            //security checks
            if (!Request::submitted('user')) {
                $errorStack[] = _('Benutzername angeben');
            }
            if (!Request::submitted('inst')) {
                $errorStack[] = _('Einrichtung angeben');
            } else if (!$GLOBALS['perm']->have_perm('root') && !$GLOBALS['perm']->have_studip_perm(Request::get('inst'), 'dozent')) {
                $inst = new Institute(Request::get('inst'));
                $errorStack[] = _('Sie haben keine Berechtigung an der Einrichtung') . ' ' . $inst->name . ' ' . _('Dozentenrechte zu beantragen');
            }
            if (Request::get('from_type') == 2 && !Request::submitted('from')) {
                $errorStack[] = _('Bitte wählen sie den Beginn des Antrags aus');
            }
            if (Request::get('to_type') == 2 && !Request::submitted('to')) {
                $errorStack[] = _('Bitte wählen sie das Ende des Antrags aus');
            }
            if ($errorStack) {
                $this->msg = MessageBox::error(_('Bitte überprüfen sie ihren Antrag'), $errorStack);
            } else {
                
                // set rights
                $right = new Dozentenrecht();
                $right->from_id = $GLOBALS['user']->id;
                $right->for_id = Request::get('user');
                $right->begin = Request::get('from_type') ? strtotime(Request::get('from')) : 0;
                $right->end = Request::get('to_type') ? strtotime(Request::get('to')) : PHP_INT_MAX;
                $right->institute_id = Request::get('inst');
                
                // if a root user puts a request it is automaticly verified
                if ($GLOBALS['perm']->have_perm('root')) {
                    $right->verify = 1;
                }
                $right->store();
            }
        }
    }

    public function given_action() {
        $this->rights = Dozentenrecht::findByFrom_id($GLOBALS['user']->id);
    }

    // customized #url_for for plugins
    function url_for($to) {
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
