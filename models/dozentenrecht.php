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
        $this->has_one['institute'] = array(
            'class_name' => 'Institute',
            'foreign_key' => 'institute_id'
        );
        parent::__construct($id);
    }

}

?>
