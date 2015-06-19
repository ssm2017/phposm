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

class Oar
{
    public $name;
    public $username;
    public $path;
    public $url;

    function __construct($username, $name)
    {
        $this->username = $username;
        $this->name = $name;
        $this->path = '/home/'. $this->username. '/opensimulator/oar/'. $this->name. '.oar';
        $this->url = '';
    }

    function load()
    {
        logWrite('[oar] load called');
        // build path
        $this->path = '/home/'. $this->username. '/opensimulator/oar/'. $this->name. '.oar';

        // check if file exists
        if (!is_file($this->path))
        {
            logWrite('[oar] oar not found : '. $this->path);
            return False;
        }

        // build url
        $this->url = createArchiveUrl($this->path);
        return True;
    }

    function delete()
    {
        logWrite('[oar] delete called');
        // check if file exists
        if (!is_file($this->path))
        {
            logWrite('[oar] oar not found : '. $this->path);
            return False;
        }
        return unlink($this->path);
    }

    function osLoadOar($sim_path, $params)
    {
        /*
            load oar [--merge] [--skip-assets] [<OAR path>]
            merge = False
            skip_assets = False
            diplacement = ""
            force_terrain = False
            force_parcels = False
            rotation = ""
            rotation_center = ""
            no_objects = False
        */
        logWrite('[oar] osLoadOar called');

        // check if the file exists
        if (!is_file($this->path))
        {
            return False;
        }

        // check if the sim exists
        if (!is_dir($sim_path))
        {
            return False;
        }

        $command = 'load oar';

        // add params
        if (isset($params['merge']) && $params['merge'])
        {
            $command .= ' --merge';
        }
        if (isset($params['skip_assets']) && $params['skip_assets'])
        {
            $command .= ' --skip-assets';
        }
        if (isset($params['diplacement']) && $params['diplacement'])
        {
            $command .= ' --diplacement';
        }
        if (isset($params['force_terrain']) && $params['force_terrain'])
        {
            $command .= ' --force-terrain';
        }
        if (isset($params['force_parcels']) && $params['force_parcels'])
        {
            $command .= ' --force-parcels';
        }
        if (isset($params['rotation']) && $params['rotation'])
        {
            $command .= ' --rotation';
        }
        if (isset($params['rotation_center']) && $params['rotation_center'])
        {
            $command .= ' --rotation-center';
        }
        if (isset($params['no_objects']) && $params['no_objects'])
        {
            $command .= ' --no-objects';
        }
        $command .= ' '. $this->path;

        // send the command to the console
        $sim = new Sim($sim_path);
        $sim->sendCommand($command);
    }

    function osSaveOar($sim_path, $params)
    {
        /*
            save oar [-h|--home=<url>] [--noassets] [--publish] [--perm=<permissions>] [--all] [<OAR path>]
            home = ''
            noassets = False
            publish = False
            perm = ''
            all_regions = False
        */
        logWrite('[oar] osSaveOar called');

        // check the name
        if ($this->name == '')
        {
            logWrite('[oar] no oar name defined');
            return False;
        }

        // check the sim path
        if (!is_dir($sim_path))
        {
            logWrite('[oar] no sim path');
            return False;
        }

        $command = 'save oar';

        // add the parameters

        if (isset($params['noassets']) && $params['noassets'])
        {
            $command .= ' --noassets';
        }
        if (isset($params['publish']) && $params['publish'])
        {
            $command .= ' --publish';
        }
        if (isset($params['perm']) && $params['perm'])
        {
            $command .= ' --perm';
        }
        if (isset($params['all_regions']) && $params['all_regions'])
        {
            $command .= ' --all';
        }
        $this->name = basename($sim_path, '.sim'). strftime("%y%m%d-%H%M");
        $this->path = '/home/'. $this->username. '/opensimulator/oar/'. $this->name. '.oar';

        $command .= ' '. $this->path;

        // send the command to the console
        $sim = new Sim($sim_path);
        $sim->sendCommand($command);
    }
}
