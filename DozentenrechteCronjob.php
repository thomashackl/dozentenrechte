<?php
require_once 'models/Dozentenrecht.php';

class DozentenrechteCronjob extends CronJob {

    public static function getName() {
        // Localization
        bindtextdomain('roomplannerplugin', __DIR__ . '/locale');
        return dgettext('dozentenrechte', 'Dozentenrechte');
    }

    public static function getDescription() {
        // Localization
        bindtextdomain('roomplannerplugin', __DIR__ . '/locale');
        return dgettext('dozentenrechte', 'Aktualisiert Dozentenrechte');
    }

    public static function getParameters() {
        // Localization
        bindtextdomain('roomplannerplugin', __DIR__ . '/locale');
        return [
            'verbose' => [
                'type' => 'boolean',
                'default' => false,
                'status' => 'optional',
                'description' => dgettext('dozentenrechte', 'Sollen Ausgaben erzeugt werden'),
            ],
        ];
    }

    public function setUp() {
        
    }

    public function execute($last_result, $parameters = []) {
        //echo "Dozentenrechte update begonnen um ".strftime("%a, %d %b %Y %H:%M:%S %z");
        Dozentenrecht::update();
        //echo "Dozentenrechte update beendet um ".strftime("%a, %d %b %Y %H:%M:%S %z");
    }

    public function tearDown() {
        
    }

}
?>