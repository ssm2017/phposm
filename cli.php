<?php
/**
 * @package   phposm
 * @copyright Copyright (C) 2015 Wene - ssm2017 Binder ( S.Massiaux ). All rights reserved.
 * @link      https://github.com/ssm2017/phposm
 * @license   GNU/GPL, http://www.gnu.org/licenses/gpl-2.0.html
 * Phposm is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
include('../etc/config.php');
include('includes/helpers/common.php');
include('includes/helpers/archive.php');
// autoloader source : http://www.grafikart.fr/formations/programmation-objet-php/autoload
class Autoloader
{
    static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    static function autoload($class)
    {
        require 'includes/'. $class . '.php';
    }
}
Autoloader::register();
logWrite('== CLI Request ==');

function display_help() {
    echo <<<EOD
Usage : php cli.php <check|start|stop|kill> <sim_path>
EOD;
echo PHP_EOL;
}

// check if we have arguments
if ($argc < 3) {
    display_help();
    exit(1);
}

// check the sim_pth
if (!is_dir($argv[2])) {
    echo 'Simulator not found.'. PHP_EOL;
    exit(1);
}

// parse the command
switch ($argv[1]) {
    case 'check':
        check_sim($argv[2]);
        break;
    case 'start':
    case 'stop':
    case 'kill':
        run_sim($argv[2], $argv[1]);
        break;
    default:
        display_help();
        exit(1);
}

function check_sim($sim_path) {
    logWrite('[cli] check_sim called');
    // get the sim
    $sim = new Sim($sim_path, False, True);
    if (!$sim->load())
    {
        echo 'Unable to load sim.'. PHP_EOL;
        exit(1);
    }
    $alive = False;
    $alive = $sim->isAlive();
    if (!$alive) {
        echo 'Sim is not alive.'. PHP_EOL;
        exit(1);
    }
    // check all the regions
    if (count($sim->regions)) {
        foreach ($sim->regions as $region) {
            if ($region->alive) {
                $alive = True;
            }
            else {
                $alive = False;
            }
        }
    }
    if (!$alive) {
        echo 'Sim is alive but some regions are not alive.'. PHP_EOL;
        exit(1);
    }
    exit(0);
}

function run_sim($sim_path, $action) {
    logWrite('[cli] run_sim called');
    $sim = new Sim($sim_path, True);
    $sim->run($action);
    print_r($sim);
    exit(0);
}

exit(0);
?>
