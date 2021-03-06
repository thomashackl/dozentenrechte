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
    
    public static function configure($config = []) {
        $config['db_table'] = 'dozentenrechte';
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'for_id'
        ];
        $config['belongs_to']['owner'] = [
            'class_name' => 'User',
            'foreign_key' => 'from_id'
        ];
        $config['belongs_to']['institute'] = [
            'class_name' => 'Institute',
            'foreign_key' => 'institute_id'
        ];
        parent::configure($config);
    }

    public static function getUnfinished() {
        return SimpleORMapCollection::createFromArray(self::findBySQL('status < ?', [self::FINISHED]));
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
                return dgettext('dozentenrechte', 'Bestätigt');
            case self::STARTED:
                return dgettext('dozentenrechte', 'Läuft');
            case self::NOTIFIED:
                return dgettext('dozentenrechte', 'Auslaufend');
            case self::FINISHED:
                return dgettext('dozentenrechte', 'Beendet');
        }
    }
    
    public function verify($to = TRUE) {
        $this->verify = (int) $to;
        
        // Extend existing rights.
        if ($this->ref_id) {
            // Send notification to users
            $for_message = sprintf(dgettext('dozentenrechte',
                'Ihre erweiterten Rechte an der Einrichtung %s wurden verlängert.'),
                $this->institute->name);
            $by_message = sprintf(dgettext('dozentenrechte',
                'Ihr Antrag auf Verlängerung der erweiterten Rechte von %s (%s) an der Einrichtung %s bis %s wurde soeben bestätigt.'),
                $this->user->getFullname(), $this->user->username, $this->institute->name, $this->getEndMessage());

            $ref = Dozentenrecht::find($this->ref_id);
            if ($ref) {
                $ref->end = $this->end;
                $ref->status = self::STARTED;
                $ref->work();
                $ref->store();
                $id = $this->ref_id;
                $this->delete();
            } else {
                $this->work();
                $this->store();
                $id = $this->id;
            }
        // New application for rights.
        } else {
            // Send notification to users
            $for_message = sprintf(dgettext('dozentenrechte',
                'Ihr Antrag auf erweiterte Rechte an der Einrichtung %s wurde bestätigt.'),
                $this->institute->name);
            $by_message = sprintf(dgettext('dozentenrechte',
                'Ihr Antrag auf erweiterte Rechte für %s (%s) an der Einrichtung %s von %s bis %s wurde soeben bestätigt.'),
                $this->user->getFullname(), $this->user->username, $this->institute->name, $this->getBeginMessage(), $this->getEndMessage());
            // Check if they need to be activated
            $this->work();
            $this->store();
            $id = $this->id;
        }
        PersonalNotifications::add($this->for_id, PluginEngine::GetURL('dozentenrechteplugin', [], 'show/index/'.$id), $for_message, '', Icon::create('roles2', 'clickable'));
        PersonalNotifications::add($this->from_id, PluginEngine::GetURL('dozentenrechteplugin', [], 'show/given/1/'.$id), $by_message, '', Icon::create('roles2', 'clickable'));

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

    public function grant() {
        if ($this->user->id && $this->user->username && $this->user->visible != 'never') {
            $this->user->perms = 'dozent';
            $this->user->visible = 'yes';
            $this->user->store();
            $instMember = InstituteMember::find([$this->for_id, $this->institute_id]);
            if (!$instMember) {
                $instMember = new InstituteMember([$this->for_id, $this->institute_id]);
            }
            $instMember->inst_perms = 'dozent';
            $instMember->store();
            $this->status = self::STARTED;
            $this->store();
        }
    }

    public function revoke() {
        if ($this->isLongestRunning()) {
            if (InstituteMember::exists([$this->for_id, $this->institute_id])) {
                $instMember = new InstituteMember([$this->for_id, $this->institute_id]);
                $instMember->inst_perms = 'autor';
                $instMember->store();
            }
            if (!InstituteMember::countBySql('user_id = ? AND inst_perms = ?', [$this->for_id, 'dozent'])
                    && !CourseMember::countBySql('user_id = ? AND status = ?', [$this->for_id, 'dozent'])) {
                $this->user->perms = 'autor';
                $this->user->store();
            }
        }
        $this->status = self::FINISHED;
        $this->store();
    }

    public function notify() {
        if ($this->status == self::STARTED) {

            // message for the expiring user
            $message = sprintf(dgettext('dozentenrechte',
                'Ihr Antrag auf erweiterte Rechte an der Einrichtung %s endet in Kürze.'),
                $this->institute->name);
            PersonalNotifications::add($this->for_id,
                PluginEngine::GetURL('dozentenrechteplugin',
                [], 'show/index/'.$this->id), $message, '',
                Icon::create('roles2', 'clickable'));

            // message for the user that gave the request for the expiring user
            $message = sprintf(dgettext('dozentenrechte',
                'Ein von Ihnen gestellter Antrag auf erweiterte Rechte für %s (%s) an der Einrichtung %s endet in Kürze.'),
                $this->user->getFullname(), $this->user->username, $this->institute->name);
            PersonalNotifications::add($this->from_id,
                PluginEngine::GetURL('dozentenrechteplugin',
                [], 'show/given/1/'.$this->id), $message, '',
                Icon::create('roles2', 'clickable'));

            $this->status = self::NOTIFIED;
            $this->store();
        }
    }

    private function isLongestRunning() {
        return !self::countBySql('for_id = ? AND end > ? AND status = ?', [$this->for_id, $this->end, self::STARTED]);
    }

}

?>
