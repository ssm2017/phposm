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

class Iar
{
    public $name;
    public $username;
    public $path;
    public $url;

    function __construct($username, $name)
    {
        $this->username = $username;
        $this->name = $name;
        $this->path = '/home/'. $this->username. '/opensimulator/iar/'. $this->name. '.iar';
        $this->url = '';
    }

    function load()
    {
        logWrite('[iar] load called');
        // build path
        $this->path = '/home/'. $this->username. '/opensimulator/iar/'. $this->name. '.iar';

        // check if file exists
        if (!is_file($this->path))
        {
            logWrite('[iar] iar not found : '. $this->path);
            return False;
        }

        // build url
        $this->url = createArchiveUrl($this->path);
        return True;
    }

    function delete()
    {
        logWrite('[iar] delete called');
        // check if file exists
        if (!is_file($this->path))
        {
            logWrite('[iar] iar not found : '. $this->path);
            return False;
        }
        return unlink($this->path);
    }

    function osLoadIar($sim_path, $params)
    {
        /*
              load iar [-m|--merge] <first> <last> <inventory path> <password> [<IAR path>]
              params are in a dict as :
              merge = False
              first = ''
              last = ''
              inventory_path = '/'
              password = ''
        */
        logWrite('[iar] osLoadIar called');
        // check if the file exists
        if (!is_file($this->path))
        {
            logWrite('[iar] file not found');
            return False;
        }

        // check if the sim exists
        if (!is_dir($sim_path))
        {
            logWrite('[iar] sim not found');
            return False;
        }

        $command = 'load iar';

        // add params

        if (isset($params['merge']) && $params['merge'])
        {
            $command .= ' --merge';
        }
        if (isset($params['first']) && $params['first'] != '')
        {
            $command .= ' '. $params['first'];
        }
        else
        {
            return False;
        }
        if (isset($params['last']) && $params['last'] != '')
        {
            $command .= ' '. $params['last'];
        }
        else
        {
            return False;
        }
        if (isset($params['inventory_path']) && $params['inventory_path'] != '')
        {
            $command .= ' '. $params['inventory_path'];
        }
        else
        {
            $command .= '/';
        }
        if (isset($params['password']) && $params['password'] != '')
        {
            $command .= ' '. $params['password'];
        }
        else
        {
            return False;
        }

        $command .= ' '. $this->path;
        // send the command to the console
        $sim = new Sim($sim_path);
        $sim->sendCommand($command);

    }

    function osSaveIar($sim_path, $params)
    {
        /*
            save iar [-h|--home=<url>] [--noassets] <first> <last> <inventory path> <password> [<IAR path>] [-c|--creators] [-e|--exclude=<name/uuid>] [-f|--excludefolder=<foldername/uuid>] [-v|--verbose]
            params are in a dict as :
            home = ''
            noassets = False
            first = ''
            last = ''
            inventory_path = '/'
            password = ''
            creators = True
            exclude = ''
            excludefolder = ''
            verbose = False
        */
        logWrite('[iar] osLoadIar called');

        // check the sim path
        if (!is_dir($sim_path))
        {
            logWrite('[iar] sim not found');
            return False;
        }

        // check if the folder is writable
        if (!is_writable('/home/'. $this->username. '/opensimulator/iar/'))
        {
            logWrite('[iar] folder not writable : '. '/home/'. $this->username. '/opensimulator/iar/');
            return False;
        }

        $command = 'save iar';

        // add the parameters
        if (isset($params['home']) && $params['home'] != '')
        {
            $command .= ' --home='. $params['home'];
        }
        if (isset($params['noassets']) && $params['noassets'])
        {
            $command .= ' --noassets';
        }
        if (isset($params['first']) && $params['first'] != '')
        {
            $command .= ' '. $params['first'];
        }
        else
        {
            return False;
        }
        if (isset($params['last']) && $params['last'] != '')
        {
            $command .= ' '. $params['last'];
        }
        else
        {
            return False;
        }
        if (isset($params['inventory_path']) && $params['inventory_path'] != '')
        {
            $command .= ' '. $params['inventory_path'];
        }
        else
        {
            $command .= '/';
        }
        if (isset($params['password']) && $params['password'] != '')
        {
            $command .= ' '. $params['password'];
        }
        else
        {
            return False;
        }
        if (isset($params['creators']) && $params['creators'])
        {
            $command .= ' --creators';
        }
        if (isset($params['exclude']) && $params['exclude'] != '')
        {
            $command .= ' --exclude='. $params['exclude'];
        }
        if (isset($params['excludefolder']) && $params['excludefolder'] != '')
        {
            $command .= ' --excludefolder='. $params['excludefolder'];
        }

        $this->name = $params['first']. '.'. $params['last']. '-'. strftime("%y%m%d-%H%M");
        $this->path = '/home/'. $this->username. '/opensimulator/iar/'. $this->name. '.iar';

        $command .= ' '. $this->path;

        // send the command to the console
        $sim = new Sim($sim_path);
        $sim->sendCommand($command);
    }
}
