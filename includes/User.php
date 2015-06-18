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

class User
{
    public $name;
    public $sims;
    public $has_iar_folder;
    public $iars;
    public $has_oar_folder;
    public $oars;

    function __construct($name, $load = False, $load_all = False)
    {
        $this->name = $name;
        $this->sims = array();
        $this->has_iar_folder = is_dir('/home/'. $this->name. '/opensimulator/iar/');
        $this->iars = array();
        $this->has_oar_folder = is_dir('/home/'. $this->name. '/opensimulator/oar/');
        $this->oars = array();

        if ($load_all)
        {
            $this->loadSims($load, $load_all);
            if ($this->has_iar_folder)
            {
                $this->loadIars();
            }
            if ($this->has_oar_folder)
            {
                $this->loadOars();
            }
        }
    }

    function loadIars()
    {
        logWrite('[user] loadIars called');
        if (!is_dir('/home/'. $this->name. '/opensimulator/iar/'))
        {
            logWrite('[user] no iar folder found : '. '/home/'. $this->name. '/opensimulator/iar/');
            return False;
        }

        $filenames = array_diff(scandir('/home/'. $this->name. '/opensimulator/iar/'), array('..', '.'));
        if (!count($filenames))
        {
            logWrite('[user] no iar file found : '. '/home/'. $this->name. '/opensimulator/iar/');
            return False;
        }

        foreach($filenames as $filename)
        {
            if (substr($filename, -4) == '.iar')
            {
                $iar = new Iar($this->name, basename($filename, '.iar'));
                if ($iar->load())
                {
                    $this->iars[] = $iar;
                }
            }
        }    
    }

    function loadOars()
    {
        logWrite('[user] loadOars called');
        if (!is_dir('/home/'. $this->name. '/opensimulator/oar/'))
        {
            logWrite('[user] no oar folder found : '. '/home/'. $this->name. '/opensimulator/oar/');
            return False;
        }

        $filenames = array_diff(scandir('/home/'. $this->name. '/opensimulator/oar/'), array('..', '.'));
        if (!count($filenames))
        {
            logWrite('[user] no oar file found : '. '/home/'. $this->name. '/opensimulator/oar/');
            return False;
        }

        foreach($filenames as $filename)
        {
            if (substr($filename, -4) == '.oar')
            {
                $iar = new Oar($this->name, basename($filename, '.oar'));
                if ($iar->load())
                {
                    $this->oars[] = $iar;
                }
            }
        }  
    }

    function loadSims($load = False, $load_all = False)
    {
        logWrite('[user] loadSims called');
        $user_os_folder_path = "/home/". $this->name. "/opensimulator/sims";
        if (!is_dir($user_os_folder_path))
        {
            return False;
        }
        $user_sims = array();
        $sims = array_diff(scandir($user_os_folder_path), array('..', '.'));
        foreach ($sims as $sim)
        {
            if (is_dir($user_os_folder_path. '/'. $sim))
            {
                $new_sim = new Sim($user_os_folder_path. '/'. $sim, $load, $load_all);
                $new_sim->isAlive();
                $user_sims[] = $new_sim;
            }
        }
        if (count($user_sims))
        {
            $this->sims = $user_sims;
            return True;
        }
        return False;
    }
}
