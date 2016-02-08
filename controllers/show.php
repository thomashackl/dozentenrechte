<?php

class ShowController extends StudipController {

    public function before_filter(&$action, &$args) {
        $GLOBALS['perm']->check('dozent');
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'));
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/person-sidebar.png');
    }

    public function index_action() {
        $this->rights = SimpleCollection::createFromArray(Dozentenrecht::findByFor_id($GLOBALS['user']->id));
    }

    public function new_action() {
        PageLayout::addScript($this->dispatcher->plugin->getPluginURL() . '/assets/application.js');
        if (Request::submitted('save')) {

            CSRFProtection::verifyUnsafeRequest();

            //security checks
            if (!Request::submitted('user')) {
                $errorStack[] = dgettext('dozentenrechte',
                    'Es wurde niemand angegeben, dem die Rechte erteilt werden sollen.');
            }
            if (!Request::get('inst')) {
                $errorStack[] = dgettext('dozentenrechte',
                    'Es wurde keine Einrichtung angegeben, an denen die Rechte gelten sollen.');
            } else if (!DozentenrechtePlugin::have_perm('root') && !$GLOBALS['perm']->have_studip_perm('dozent', Request::get('inst'))) {
                $inst = new Institute(Request::get('inst'));
                $errorStack[] = dgettext('dozentenrechte',
                    'Sie haben keine Berechtigung an der Einrichtung') . ' ' .
                    dgettext('dozentenrechte', ', Dozentenrechte zu beantragen');
            }
            if (Request::get('from_type') && !Request::get('from')) {
                $errorStack[] = dgettext('dozentenrechte',
                    'Bitte geben Sie den Beginn der beantragten Rechte an.');
            }
            if (Request::get('to_type') && !Request::get('to')) {
                $errorStack[] = dgettext('dozentenrechte',
                    'Bitte geben Sie das Ende der beantragten Rechte an.');
            }
            if (strtotime(Request::get('from')) > strtotime(Request::get('to'))) {
                $errorStack[] = dgettext('dozentenrechte',
                    'Enddatum liegt vor Beginndatum.');
            }
            if (Request::get('to') && strtotime(Request::get('to')) < time()) {
                $errorStack[] = dgettext('dozentenrechte',
                    'Ihr Antrag endet in der Vergangenheit.');
            }
            if ($errorStack) {
                PageLayout::postError(dgettext('dozentenrechte',
                    'Bitte überprüfen sie ihren Antrag:'), $errorStack);
            } else {
                // set rights
                $users = Request::get('user') ? array(Request::get('user')) : Request::getArray('user');
                foreach ($users as $user) {
                    if ($user) {
                        $right = new Dozentenrecht();
                        $right->rights = Request::get('rights');
                        $right->from_id = $GLOBALS['user']->id;
                        $right->for_id = $user;
                        $right->begin = Request::get('from_type') ? strtotime(Request::get('from')) : 0;
                        $right->end = Request::get('to_type') ? strtotime(Request::get('to')) : PHP_INT_MAX;
                        $right->institute_id = Request::get('inst');
                        $right->store();

                        // if a root user puts a request it is automaticly verified
                        if (DozentenrechtePlugin::have_perm('root')) {
                            $right->verify();
                        }
                    }
                }

                $this->redirect('show/given');
            }
        }
        if ($GLOBALS['perm']->have_perm('root')) {
            $this->institutes = array();
        } else {
            $this->institutes = array_filter(Institute::getMyInstitutes(), function($i) {
                return in_array($i['inst_perms'], array('dozent', 'admin'));
            });
        }

        $this->userSearch = new PermissionSearch('user', 'Person hinzufügen', 'user_id',
            array(
                'permission' => array('user', 'autor', 'tutor', 'dozent'),
                'exclude_user' => array()
            ));

        $this->right = Request::option('right', 'dozent');
        $this->inst = Request::option('inst', '');
        $this->users = User::findMany(Request::optionArray('user', array()));
        $this->from_type = Request::int('from_type', 0);
        $this->from = Request::get('from', '');
        $this->to_type = Request::int('to_type', 0);
        $this->to = Request::get('to', '');
    }

    public function userinput_action() {
        $this->set_layout(null);
    }

    public function given_action() {
        $this->checkRejected();
        $this->rights = SimpleCollection::createFromArray(Dozentenrecht::findByFrom_id($GLOBALS['user']->id));
    }

    public function accept_action() {
        DozentenrechtePlugin::check('root');
        if (Request::submitted('accept')) {
            $rights = SimpleORMapCollection::createFromArray(Dozentenrecht::findMany(array_keys(Request::getArray('verify'))));
            $rights->verify();
        }
        $this->rights = SimpleCollection::createFromArray(Dozentenrecht::findByVerify(0));
    }

    public function search_action() {
        DozentenrechtePlugin::check('root');
        $this->checkEnded();
        $sql = "SELECT d.* FROM dozentenrechte d
            JOIN auth_user_md5 a ON (for_id = user_id) 
            JOIN Institute i ON (d.institute_id = i.Institut_id) 
            WHERE a.username LIKE :input 
            OR a.vorname LIKE :input 
            OR a.nachname LIKE :input 
            OR CONCAT(a.vorname, ' ',a.nachname) LIKE :input 
            OR i.name LIKE :input
            LIMIT 50";
        $statement = DBManager::get()->prepare($sql);
        $search = "%" . Request::get('search') . "%";
        $statement->bindParam(':input', $search);
        $statement->execute();
        while ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
            $rights[] = Dozentenrecht::import($result);
        }
        if ($rights) {
            $this->rights = SimpleCollection::createFromArray($rights);
        }
    }

    public function multipersonsearch_action() {
        $mp = MultiPersonSearch::load('add_dozentenrecht_' . $GLOBALS['user']->id);

        $this->flash['users'] = array_unique(array_merge($mp->getDefaultSelectedUsersIDs(), $mp->getAddedUsers()));

        $this->relocate('show/new');
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

    private function checkRejected() {
        if (Request::submitted('reject')) {
            $right = new Dozentenrecht(Request::get('reject'));
            if (DozentenrechtePlugin::have_perm('root') || $right->from_id == $GLOBALS['user']->id) {
                $right->delete();
            }
        }
    }

    private function checkEnded() {
        if (Request::submitted('end')) {
            $right = new Dozentenrecht(Request::get('end'));
            $right->revoke();
        }
    }

}
