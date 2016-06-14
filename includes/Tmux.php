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

class Tmux
{
    public $user_name;
    public $sessions;
    public $last_response;
    public $last_code;

    function __construct($user_name)
    {
        $this->user_name = $user_name;
    }

    function exec($command)
    {
        $this->last_response = array();
        $this->last_code = Null;
        exec('sudo -u '. $this->user_name. ' tmux '. $command. ' 2>&1', $this->last_response, $this->last_code);
        if ($this->last_code != 0) {
            logWrite('[last_response] : '. print_r($this->last_response, True));
            logWrite('[last_code] : '. $this->last_code);
        }
    }

    /*
        Sessions
    */
    function listSessions()
    {
        $this->exec('list-sessions -F "#{session_name}"');
        if ($this->last_code === 0 && !empty($this->last_response))
        {
            $this->sessions = $this->last_response;
        }
        else
        {
            $this->sessions = array();
        }
    }

    function newSession($params)
    {
        $command = '';
        if (isset($params['session-name']))
        {
            $command .= ' -s '. $params['session-name'];
        }
        if (isset($params['window-name']))
        {
            $command .= ' -n '. $params['window-name'];
        }
        if (isset($params['start-directory']))
        {
            $command .= ' -c '. $params['start-directory'];
        }
        $this->exec("new-session -dP". $command);
    }

    function killSession($session_name)
    {
        $this->exec("kill-session -t ". $session_name);
    }

    function killAllSessions()
    {
        $this->listSessions();
        if (count($this->sessions))
        {
            foreach ($this->sessions as $session_name)
            {
                $this->killSession($session_name);
            }
        }
    }

    /*
        Send keys
    */
    function sendKeys($params)
    {
        $command = '';
        if (isset($params['session-name']))
        {
            $command .= ' -t '. $params['session-name'];
        }
        if (isset($params['window-name']))
        {
            $command .= ':'. $params['window-name'];
        }
        else
        {
            $command .= ':0';
        }
        if (isset($params['keys']))
        {
            $command .= ' "'. $params['keys']. '"'. ' Enter';
        }
        $this->exec('send-keys'. $command);
    }

}
