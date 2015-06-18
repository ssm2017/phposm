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

function checkPassword($password_given)
{
    if ($password_given == $GLOBALS['config']['password'])
    {
        return True;
    }
    return False;
}
function wrongPassword()
{
    return json_encode(array(
        "accepted" => False,
        "success" => False,
        "error" => "wrong password"
        )
    );
}

function logWrite($message)
{
    if (isset($GLOBALS['config']['log']) && $GLOBALS['config']['log'])
    {
        $filename = '../../../log/xmlrpc.log';
        if (isset($GLOBALS['config']['log_path']) && $GLOBALS['config']['log_path'] != '')
        {
            $filename = $GLOBALS['config']['log_path'];
        }
        $current = file_get_contents($filename);
        $current .= $message. "\n";
        file_put_contents($filename, $current);
    }
}

function getUserFromPath($path)
{
    $items = explode('/', $path);
    return $items[2];
}

function getHeaders($url, $timeout=5)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,            $url);
    curl_setopt($ch, CURLOPT_HEADER,         true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        $timeout);
    curl_setopt($ch, CURLOPT_VERBOSE, true);

    $r = curl_exec($ch);
    $r = split("\n", $r);
    return $r;
} 
