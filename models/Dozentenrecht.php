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

    const NOT_STARTED = 0;
    const STARTED = 1;
    const NOTIFIED = 2;
    const FINISHED = 3;
    
    // notify 7 days before end
    const TIME_TO_NOTIFY = 604800;
    const INFINITY = 2147483647;
    
    public static function configure($config = array()) {
        $config['db_table'] = 'dozentenrechte';
        $config['belongs_to']['user'] = array(
            'class_name' => 'User',
            'foreign_key' => 'for_id'
        );
        $config['belongs_to']['owner'] = array(
            'class_name' => 'User',
            'foreign_key' => 'from_id'
        );
        $config['belongs_to']['institute'] = array(
            'class_name' => 'Institute',
            'foreign_key' => 'institute_id'
        );
        parent::configure($config);
    }

    public static function getUnfinished() {
        return SimpleORMapCollection::createFromArray(self::findBySQL('status < ?', array(self::FINISHED)));
    }
    
    public static function update() {
        $rights = self::getUnfinished();
        $rights->sendMessage('work');
    }

    public function getStatusMessage() {
        if (!$this->verify) {
            return dgettext('dozentenrechte', 'Wartend');
        }
        switch ($this->status) {
            case self::NOT_STARTED:
                return dgettext('dozentenrechte', 'Best�tigt');
            case self::STARTED:
                return dgettext('dozentenrechte', 'L�uft');
            case self::NOTIFIED:
                return dgettext('dozentenrechte', 'Auslaufend');
            case self::FINISHED:
                return dgettext('dozentenrechte', 'Beendet');
        }
    }
    
    public function verify($to = TRUE) {
        $this->verify = (int) $to;
        
        // Send notification to users
        $message = dgettext('dozentenrechte', 'Ihr Dozentenrechteantrag wurde best�tigt');
        PersonalNotifications::add($this->for_id, PluginEngine::GetURL('dozentenrechteplugin', array(), 'show'), $message);
        $message = sprintf(dgettext('dozentenrechte', 'Ihr Antrag auf erweiterte Rechte f�r %s (Kennung: %s) an der Einrichtung %s von %s bis %s wurde soeben best�tigt. %1$s kann nun als Dozent in die jeweiligen Veranstaltungen eingetragen werden.'), $this->user->getFullname(), $this->user->username, $this->institute->name, $this->getBeginMessage(), $this->getEndMessage());
        PersonalNotifications::add($this->from_id, PluginEngine::GetURL('dozentenrechteplugin', array(), 'show/given'), $message);

        // Check if they need to be activated
        $this->work();
        $this->store();
    }
    
    public function getEndMessage($style = 'd.m.Y') {
        return $this->end >= self::INFINITY ? dgettext('dozentenrechte', 'Unbegrenzt') : date($style, $this->end);
    }
    
    public function getBeginMessage($style = 'd.m.Y') {
        return $this->begin ? date($style, $this->begin) : dgettext('dozentenrechte', 'Unbegrenzt');
    }
    
    public function getRequestDate($style = 'd.m.Y') {
        return date($style, $this->mkdate);
    }

    public function work() {
        if ($this->verify && $this->status != self::FINISHED) {
            if ($this->end < time()) {
                $this->revoke();
            } else if ($this->begin < time()) {
                $this->grant();
                if ($this->end - self::TIME_TO_NOTIFY < time()) {
                    $this->notify();
                }
            }
        }
    }

    private function grant() {
        if ($this->user->id && $this->user->username) {
            $um = new UserManagement($this->user->id);
            $um->changeUser(array('auth_user_md5.perms' => 'dozent'));
            $um->storeToDatabase();
            $instMember = new InstituteMember(array($this->for_id, $this->institute_id));
            $instMember->inst_perms = 'dozent';
            $instMember->store();
            $this->status = self::STARTED;
            $this->store();
        }
    }

    public function revoke() {
        if ($this->isLongestRunning()) {
            if (InstituteMember::exists($this->for_id, $this->institute_id)) {
                $instMember = new InstituteMember(array($this->for_id, $this->institute_id));
                $instMember->inst_perms = 'autor';
                $instMember->store();
            }
            if (!InstituteMember::countBySql('user_id = ? AND inst_perms = ?', array($this->for_id, 'dozent'))
                    && !CourseMember::countBySql('user_id = ? AND status = ?', array($this->for_id, 'dozent'))) {
                $this->user->perms = 'autor';
                $this->user->store();
            }
        }
        $this->status = self::FINISHED;
        $this->store();
    }

    private function notify() {
        if ($this->status == self::STARTED) {
            $msg = new messaging();

            // message for the expiring user
            $message = dgettext('dozentenrechte', 'Ihr Dozentenrechteantrag endet in K�rze');
            PersonalNotifications::add($this->for_id, PluginEngine::GetURL('dozentenrechteplugin', array(), 'show'), $message);
            //$msg->insert_message($message, get_username($this->for_id), "____%system%____", FALSE, FALSE, "1", FALSE, dgettext('dozentenrechte', "Systemnachricht:") . " " . dgettext('dozentenrechte', "Dozentenrechte"), TRUE);

            // message for the user that gave the request for the expiring user
            $message = dgettext('dozentenrechte', 'Ein von Ihnen gestellter Dozentenrechteantrag endet in K�rze');
            PersonalNotifications::add($this->from_id, PluginEngine::GetURL('dozentenrechteplugin', array(), 'show/given'), $message);
            //$msg->insert_message($message, get_username($this->from_id), "____%system%____", FALSE, FALSE, "1", FALSE, dgettext('dozentenrechte', "Systemnachricht:") . " " . dgettext('dozentenrechte', "Dozentenrechte"), TRUE);

            $this->status = self::NOTIFIED;
            $this->store();
        }
    }

    private function isLongestRunning() {
        return !self::countBySql('for_id = ? AND end > ? AND status = ?', array($this->for_id, $this->end, self::STARTED));
    }

}

?>
