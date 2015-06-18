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

class Sim
{
    public $username;
    public $path;
    public $name;
    public $port;
    public $has_bin_folder;
    public $pid_file;
    public $has_opensim_exe;
    public $has_opensim_log;
    public $has_opensim_ini;
    public $has_regions_ini;
    public $has_tmux_log;
    public $radmin_ready;
    public $radmin_password;
    public $valid;
    public $regions;
    public $alive;

    function __construct($path, $load = False, $load_all = False)
    {
        $this->username = getUserFromPath($path);
        $this->path = $path;
        $this->name = pathinfo($path)['filename'];
        $this->port = 0;
        $this->has_bin_folder = False;
        $this->pid_file = '';
        $this->has_opensim_exe = False;
        $this->has_opensim_log = False;
        $this->has_opensim_ini = False;
        $this->has_regions_ini = False;
        $this->has_tmux_log = False;
        $this->radmin_ready = False;
        $this->radmin_password = '';
        $this->valid = False;
        $this->regions = array();
        $this->alive = False;

        if ($load || $load_all)
        {
            $this->load();
            if ($load_all)
            {
                if ($this->has_regions_ini)
                {
                    $this->loadRegions($load, $load_all);
                }
            }
        }
    }

    function load()
    {
        // return if not a directory
        if (!is_dir($this->path))
        {
            return False;
        }

        // check if there is a bin folder
        $this->has_bin_folder = is_dir($this->path. '/bin');

        // check if there is an OpenSim.exe file
        $this->has_opensim_exe = is_file($this->path. '/bin/OpenSim.exe');

        // check if there is an OpenSim.ini file
        $this->has_opensim_ini = is_file($this->path. '/bin/OpenSim.ini');
        $opensim_ini = parse_ini_file($this->path. '/bin/OpenSim.ini', True);

        // check if there is an OpenSim log file
        $this->has_opensim_log = is_file($this->path. '/log/OpenSim.log');

        // check if there is a tmux log file
        $this->has_tmux_log = is_file($this->path. '/log/tmux.log');

        // check if there is a Regions.ini file
        $this->has_regions_ini = is_file($this->path. '/bin/Regions/Regions.ini');
        $regions_ini = parse_ini_file($this->path. '/bin/Regions/Regions.ini', True);

        // check if RAdmin is enabled
        if (isset($opensim_ini['RemoteAdmin']))
        {
            if (isset($opensim_ini['RemoteAdmin']['enabled']))
            {
                if ($opensim_ini['RemoteAdmin']['enabled'])
                {
                    if (isset($opensim_ini['RemoteAdmin']['port']))
                    {
                        if (isset($opensim_ini['RemoteAdmin']['access_password']))
                        {
                            $this->radmin_password = $opensim_ini['RemoteAdmin']['access_password'];
                            $this->radmin_ready = True;
                        }
                    }
                }
            }
        }

        // get the network port
        if (isset($opensim_ini['Network']))
        {
            if (isset($opensim_ini['Network']['http_listener_port']))
            {
                $this->port = $opensim_ini['Network']['http_listener_port'];
            }
        }

        // get the pid file path
        if (isset($opensim_ini['Startup']))
        {
            if (isset($opensim_ini['Startup']['PIDFile']))
            {
                $this->pid_file = $opensim_ini['Startup']['PIDFile'];
            }
        }

        // check if valid
        $this->valid = (
            $this->has_bin_folder &&
            ($this->pid_file != "") &&
            $this->has_opensim_exe &&
            $this->has_opensim_log &&
            $this->has_opensim_ini &&
            $this->has_regions_ini &&
            $this->has_tmux_log &&
            $this->radmin_ready
        );

        if ($this->valid)
        {
            return True;
        }
    }

    function radminPasswordChecked($password)
    {

    }

    function showLog($log_type = 'os', $lines = 20)
    {
        if ($log_type == 'tx')
        {
            return implode("", array_slice(file($this->path. '/log/tmux.log'), -$lines));
        }
        else
        {
            return implode("", array_slice(file($this->path. '/log/OpenSim.log'), -$lines));
        }
    }

    function run($action)
    {
        if (!in_array($action, array('start', 'stop', 'kill')))
        {
            return False;
        }
        if (!$this->valid)
        {
            return False;
        }
        $this->isAlive();
        switch ($action)
        {
            case 'start':
                if ($this->alive)
                {
                    return False;
                }
                $this->startSim();
                break;
            case 'stop':
                if (!$this->alive)
                {
                    return False;
                }
                $this->stopSim();
                break;
            case 'kill':
                $this->killSim();
                break;
        }
    }

    function isAlive()
    {
        logWrite('[sim] isAlive called');
        $this->alive = False;

        // check if there is a pid file
        $check_pid = is_file($this->pid_file);

        // check if the sim is responding to simstatus
        $headers = getHeaders('http://127.0.0.1:'. $this->port. '/simstatus');
        $codes = explode(' ', $headers[0]);
        if (isset($codes[1]) && $codes[1] == 200)
        {
            $check_simstatus = True;
        }
        if ($check_pid && $check_simstatus)
        {
            $this->alive = True;
            return True;
        }
        return False;
    }

    function loadRegions($load = False, $load_all = False)
    {
        logWrite('[sim] loadRegions called');
        // check if regions.ini exists
        if (!$this->has_regions_ini)
        {
            return False;
        }
        $regions_ini = parse_ini_file($this->path. '/bin/Regions/Regions.ini', True);
        $region_names = array_keys($regions_ini);
        if (count($region_names))
        {
            foreach($region_names as $region_name)
            {
                $this->regions[] = new Region($this->path, $region_name, $load, $load_all);
            }
        }
    }

    function sendCommand($command)
    {
        logWrite('[sim] sendCommand called');
        $tmux = new Tmux($this->username);
        $tmux->listSessions();
        if (in_array($this->name, $tmux->sessions))
        {
            $tmux->sendKeys(array(
                'session-name' => $this->name,
                'window-name' => 'OpenSimulator',
                'keys' => $command
            ));
        }
    }

    function startSim()
    {
        // get the tmux session
        logWrite('[sim] startSim called');
        // get the tmux session
        $tmux = new Tmux($this->username);
        $tmux->listSessions();
        if (!in_array($this->name, $tmux->sessions))
        {
            $tmux->newSession(array(
                'session-name' => $this->name,
                'window-name' => 'OpenSimulator',
                'start-directory' => $this->path. '/bin'
            ));
        }
        $tmux->sendKeys(array(
            'session-name' => $this->name,
            'window-name' => 'OpenSimulator',
            'keys' => 'mono --server OpenSim.exe'
        ));
    }

    function stopSim()
    {
        logWrite('[sim] stopSim called');
        // get the tmux session
        $tmux = new Tmux($this->username);
        $tmux->listSessions();
        if (in_array($this->name, $tmux->sessions))
        {
            $tmux->sendKeys(array(
                'session-name' => $this->name,
                'window-name' => 'OpenSimulator',
                'keys' => 'quit'
            ));
        }
    }

    function killSim()
    {
        logWrite('[sim] killSim called');
        $tmux = new Tmux($this->username);
        $tmux->listSessions();
        if (in_array($this->name, $tmux->sessions))
        {
            $tmux->killSession($this->name);
        }
    }
}
