<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle_alias_behat';
$CFG->dbuser    = 'postgres';
$CFG->dbpass    = '123456';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
    'dbpersist' => 0,
    'dbport' => 5432,
    'dbsocket' => '',
);

//$CFG->wwwroot   = 'http://localhost/moodle-alias';
$CFG->dataroot  = 'D:\\logs\\moodle_alias';
$CFG->admin     = 'admin';

$CFG->behat_wwwroot = 'http://localhost/moodle-alias';
$CFG->behat_dataroot = 'D:\logs\moodle_alias_behat';
$CFG->behat_prefix = 'behat_';
$CFG->behat_profiles = [
    'default' => [
        'browser' => 'chrome',
        'extensions' => [
            'Behat\MinkExtension' => [
                'selenium2' => [
                    'browser' => 'chrome',
                ]
            ]
        ]
    ]
];

$CFG->behat_faildump_path = 'D:/logs/behat_dump';


$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
