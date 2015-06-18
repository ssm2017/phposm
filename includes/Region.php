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

class Region
{
    public $name;
    public $sim_path;
    public $username;
    public $region_uuid;
    public $location;
    public $sizex;
    public $sizey;
    public $internal_address;
    public $internal_port;
    public $allow_alternate_ports;
    public $external_host_name;
    public $nonphysical_prim_max;
    public $physical_prim_max;
    public $clamp_primSize;
    public $max_prims;
    public $max_agents;
    public $scope_id;
    public $region_type;
    public $maptile_static_uuid;
    public $alive;

    function __construct($sim_path, $name, $load = False, $load_all = False)
    {
        $this->name = $name;
        $this->sim_path = $sim_path;
        $this->username = getUserFromPath($sim_path);

        $this->region_uuid = "";
        $this->location = "";
        $this->sizex = 256;
        $this->sizey = 256;
        $this->internal_address = "";
        $this->internal_port = "";
        $this->allow_alternate_ports = "";
        $this->external_host_name = "";
        $this->nonphysical_prim_max = "";
        $this->physical_prim_max = "";
        $this->clamp_primSize = False;
        $this->max_prims = "";
        $this->max_agents = "";
        $this->scope_id = "";
        $this->region_type = "";
        $this->maptile_static_uuid = "";

        $this->alive = False;
        if ($load || $load_all)
        {
            $this->load();
            if ($load_all)
            {
                $this->isAlive();
            }
        }

    }

    function load()
    {
        logWrite('[region] load called');
        // check if regions.ini exists
        if (!is_file($this->sim_path. '/bin/Regions/Regions.ini'))
        {
            return False;
        }
        $regions_ini = parse_ini_file($this->sim_path. '/bin/Regions/Regions.ini', True);
        if (!isset($regions_ini[$this->name]))
        {
            return False;
        }
        $values = $regions_ini[$this->name];

        // fill values
        if (isset($values['RegionUUID']))
        {
            $this->region_uuid = $values['RegionUUID'];
        }

        if (isset($values['Location']))
        {
            $this->location = $values['Location'];
        }

        if (isset($values['SizeX']))
        {
            $this->sizex = $values['SizeX'];
        }

        if (isset($values['SizeY']))
        {
            $this->sizey = $values['SizeY'];
        }

        if (isset($values['InternalAddress']))
        {
            $this->internal_address = $values['InternalAddress'];
        }

        if (isset($values['InternalPort']))
        {
            $this->internal_port = $values['InternalPort'];
        }

        if (isset($values['AllowAlternatePorts']))
        {
            $this->allow_alternate_ports = $values['AllowAlternatePorts'];
        }

        if (isset($values['ExternalHostName']))
        {
            $this->external_host_name = $values['ExternalHostName'];
        }

        if (isset($values['NonphysicalPrimMax']))
        {
            $this->non_pysical_prim_max = $values['NonphysicalPrimMax'];
        }

        if (isset($values['PhysicalPrimMax']))
        {
            $this->physical_prim_max = $values['PhysicalPrimMax'];
        }

        if (isset($values['MaxPrims']))
        {
            $this->max_prims = $values['MaxPrims'];
        }

        if (isset($values['MaxAgents']))
        {
            $this->max_agents = $values['MaxAgents'];
        }

        if (isset($values['ScopeID']))
        {
            $this->scope_id = $values['ScopeID'];
        }

        if (isset($values['RegionType']))
        {
            $this->region_type = $values['RegionType'];
        }

        if (isset($values['MaptileStaticUUID']))
        {
            $this->maptile_static_uuid = $values['MaptileStaticUUID'];
        }
    }

    function exists()
    {
        logWrite('[region] exists called');
        // check if regions.ini exists
        if (!is_file($this->sim_path. '/bin/Regions/Regions.ini'))
        {
            return False;
        }
        $regions_ini = parse_ini_file($this->sim_path. '/bin/Regions/Regions.ini', True);
        if (!isset($regions_ini[$this->name]))
        {
            return False;
        }
        return True;
    }

    function setActive()
    {
        logWrite('[region] setActive called');
        $sim = new Sim($this->sim_path, True);
        if ($sim->isAlive())
        {
            $sim->sendCommand('change region '. $this->name);
            return True;
        }
        return False;
    }

    function osSaveOar($params)
    {
        /*
            home = ''
            noassets = False
            publish = False
            perm = ''
            all_regions = False
        */
        logWrite('[region] osSaveOar called');
        if ($this->setActive())
        {
            $oar = new Oar($this->username, $this->name);
            $oar->osSaveOar($this->sim_path, $params);
        }
    }

    function osLoadOar($oar_name, $params)
    {
        /*
            merge = False
            skip_assets = False
        */
        logWrite('[region] osLoadOar called');
        $oar = new Oar($this->username, $oar_name);
        if ($oar->load())
        {
            if ($this->setActive())
            {
                $oar->osLoadOar($this->sim_path, $params);
            }
        }
    }

    function isAlive()
    {
        logWrite('[region] isAlive called');
        // get the sim
        $sim = new Sim($this->sim_path);

        // check if sim is alive
        if ($sim->isAlive())
        {
            $this->alive = False;
            return False;
        }
        // check if the region is alive
        $headers = getHeaders('http://127.0.0.1:'. $this->internal_port. '/monitorstats/'. $this->region_uuid);
        $codes = explode(' ', $headers[0]);
        if (isset($codes[1]) && $codes[1] == 200)
        {
            $this->alive = True;
            return True;
        }

        return False;
    }
}
