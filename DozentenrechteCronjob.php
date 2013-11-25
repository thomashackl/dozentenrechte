<?php
require_once 'models/dozentenrecht.php';

class DozentenrechteCronjob extends CronJob {

    public static function getName() {
        return _('Dozentenrechte');
    }

    public static function getDescription() {
        return _('Aktualisiert Dozentenrechte');
    }

    public static function getParameters() {
        return array(
            'verbose' => array(
                'type' => 'boolean',
                'default' => false,
                'status' => 'optional',
                'description' => _('Sollen Ausgaben erzeugt werden'),
            ),
        );
    }

    public function setUp() {
        
    }

    public function execute($last_result, $parameters = array()) {
        echo "Dozentenrechte update begonnen um ".strftime("%a, %d %b %Y %H:%M:%S %z");
        Dozentenrecht::update();
        echo "Dozentenrechte update beendet um ".strftime("%a, %d %b %Y %H:%M:%S %z");
    }

    public function tearDown() {
        
    }

}
?>