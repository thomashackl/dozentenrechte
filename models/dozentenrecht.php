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
        parent::__construct($id);
    }
}

?>
