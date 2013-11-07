<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of dozentenrecht
 *
 * @author intelec
 */
class Dozentenrecht extends SimpleORMap {

    public function __construct($id = null) {
        $this->db_table = 'dozentenrechte';
        $this->has_one['user'] = array(
            'class_name' => 'User',
            'foreign_key' => 'for_id'
        );
        $this->has_one['owner'] = array(
            'class_name' => 'User',
            'foreign_key' => 'from_id'
        );
        $this->has_one['institute'] = array(
            'class_name' => 'Institute',
            'foreign_key' => 'institute_id'
        );
        $this->has_one['member'] = array(
            'class_name' => 'InstituteMember',
            'foreign_key' => array('for_id', 'institute_id')
        );
        parent::__construct($id);
    }

    public function work() {
        if ($this->verify) {
            if ($this->end < time()) {
                $this->revoke();
            } else if ($this->begin < time()) {
                $this->grant();
                if ($this->end - 7 * 24 * 60 * 60 < time()) {
                    $this->notify();
                }
            }
        }
    }

    private function grant() {
        $this->user->perms = 'dozent';
        $this->user->store();
        $instMember = new InstituteMember(array($this->for_id, $this->institute_id));
        $instMember->inst_perms = 'dozent';
        $instMember->store();
    }

    private function revoke() {
        $instMember = new InstituteMember(array('for_id', 'institute_id'));
        $instMember->inst_perms = 'autor';
        $instMember->store();
        if (!InstituteMember::countBySql('user_id = ?', array($this->for_id))) {
            $this->user->perms = 'autor';
            $this->user->store();
        }
    }

    private function notify() {
        if (!$this->notify) {
            $msg = new messaging();

            // message for the expiring user
            $message = _('Ihr Dozentenrechteantrag endet in Kürze');
            PersonalNotifications::add($this->for_id, PluginEngine::GetURL('dozentenrechteplugin', array(), 'show'), $message);
            $msg->insert_message($message, get_username($this->for_id), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:") . " " . _("Dozentenrechte"), TRUE);

            // message for the user that gave the request for the expiring user
            $message = _('Ein von Ihnen gestellter Dozentenrechteantrag endet in Kürze');
            PersonalNotifications::add($this->from_id, PluginEngine::GetURL('dozentenrechteplugin', array(), 'show/given'), $message);
            $msg->insert_message($message, get_username($this->from_id), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:") . " " . _("Dozentenrechte"), TRUE);
            
            $this->notify = 1;
            $this->store();
        }
    }

}

?>
