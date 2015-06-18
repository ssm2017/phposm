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

include('../../config.php');
include('../includes/helpers/common.php');
include('../includes/helpers/archive.php');

// autoloader source : http://www.grafikart.fr/formations/programmation-objet-php/autoload
class Autoloader
{
    static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    static function autoload($class)
    {
        require '../includes/'. $class . '.php';
    }
}
Autoloader::register();

logWrite('== Request ==');

// create the xmlrpc server
$xmlrpc_server = xmlrpc_server_create();
xmlrpc_server_register_method($xmlrpc_server, "ping", "ping");
xmlrpc_server_register_method($xmlrpc_server, "get_sim_users", "get_sim_users");
xmlrpc_server_register_method($xmlrpc_server, "get_sim_user", "get_sim_user");
xmlrpc_server_register_method($xmlrpc_server, "get_sims_by_user", "get_sims_by_user");
xmlrpc_server_register_method($xmlrpc_server, "get_sim", "get_sim");
xmlrpc_server_register_method($xmlrpc_server, "show_log", "show_log");
xmlrpc_server_register_method($xmlrpc_server, "run_sim", "run_sim");
xmlrpc_server_register_method($xmlrpc_server, "os_save_oar", "os_save_oar");
xmlrpc_server_register_method($xmlrpc_server, "os_load_oar", "os_load_oar");
xmlrpc_server_register_method($xmlrpc_server, "delete_oar", "delete_oar");
xmlrpc_server_register_method($xmlrpc_server, "os_save_iar", "os_save_iar");
xmlrpc_server_register_method($xmlrpc_server, "os_load_iar", "os_load_iar");
xmlrpc_server_register_method($xmlrpc_server, "delete_iar", "delete_iar");

// methods
function ping($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] ping called');
    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True,
        'data' => 'pong'
    ));
    print $response_xml;
}

function get_sim_users($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] get_sim_users called');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    // get system users
    exec("cut -d: -f1,3 /etc/passwd | egrep ':[0-9]{4}$' | cut -d: -f1", $usernames, $code);
    if ($code)
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => 'could not get system users'
        ));
        print $response_xml;
        return;
    }

    $sim_users = array();
    foreach ($usernames as $username)
    {
        $user = new User($username, False, True);
        if (count($user->sims))
        {
            $sim_users[] = $user;
        }
    }

    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True,
        'sim_users' => $sim_users
    ));
    print $response_xml;
}

function get_sim_user($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] get_sim_user called');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $user = new User($params[1], False, True);
    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True,
        'sim_user' => $user
    ));
    print $response_xml;
}

function get_sims_by_user($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] get_sims_by_user called');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $user = new User($params[1], False, True);
    $user->loadSims(False, True);
    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True,
        'sims' => $user->sims
    ));
    print $response_xml;
}

function get_sim($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] get_sim called');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    if (is_dir($params[1]))
    {
        $sim = new Sim($params[1], False, True);
        if ($sim->load())
        {
            $sim->isAlive();
            $response_xml = xmlrpc_encode_request(NULL, array(
                'success' => True,
                'sim' => $sim
            ));
            print $response_xml;
            return;
        }
    }
    
    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => False,
        'message' => 'Sim not found'
    ));
    print $response_xml;
}

function show_log($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] show_log called');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $sim = new Sim($params[1], True);
    $lines = 20;
    if (isset($params[3]))
    {
        $lines = $params[3];
    }
    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True,
        'log' => base64_encode($sim->showLog($params[2], $lines))
    ));
    print $response_xml;
}

function run_sim($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] run_sim called');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $sim = new Sim($params[1], True);
    $sim->run($params[2]);
    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True,
        'run_sim' => 'Action '. $params[2]. ' done'
    ));
    print $response_xml;
}

function os_save_oar($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] os_save_oar');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $sim_path = $params[1];
    $region_name = $params[2];
    $parameters = $params[3];

    $region = new Region($sim_path, $region_name);
    $region->osSaveOar($parameters);

    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True
    ));
    print $response_xml;
}

function os_load_oar($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] os_load_oar');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $sim_path = $params[1];
    $region_name = $params[2];
    $oar_path = $params[3];
    $oar_name = basename($oar_path, '.oar');
    $parameters = $params[4];

    $region = new Region($sim_path, $region_name);
    $region->osLoadOar($oar_name, $parameters);

    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True
    ));
    print $response_xml;
}

function delete_oar($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] delete_oar');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => False,
        'message' => 'not implemented'
    ));
    print $response_xml;
}

function os_save_iar($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] os_save_iar');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $sim_path = $params[1];
    $parameters = $params[2];
    $username = getUserFromPath($sim_path);
    $iar = new Iar($username, '');
    $iar->osSaveIar($sim_path, $parameters);
    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True
    ));
    print $response_xml;
}

function os_load_iar($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] os_load_iar');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $sim_path = $params[1];
    $iar_path = $params[2];
    $parameters = $params[3];
    $username = getUserFromPath($sim_path);
    $iar = new Iar($username, basename($iar_path, '.iar'));
    if (!$iar->load())
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'message' => 'error loading iar'
        ));
    }
    $iar->osLoadIar($sim_path, $parameters);
    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => True
    ));
    print $response_xml;
}

function delete_iar($method_name, $params, $app_data)
{
    logWrite('[xmlrpc] delete_iar');

    // check password
    if (! checkPassword($params[0]))
    {
        $response_xml = xmlrpc_encode_request(NULL, array(
            'success' => False,
            'data' => wrongPassword()
        ));
        print $response_xml;
        return;
    }

    $response_xml = xmlrpc_encode_request(NULL, array(
        'success' => False,
        'message' => 'not implemented'
    ));
    print $response_xml;
}

$request_xml = file_get_contents("php://input");
xmlrpc_server_call_method($xmlrpc_server, $request_xml, '');
xmlrpc_server_destroy($xmlrpc_server);
?>
