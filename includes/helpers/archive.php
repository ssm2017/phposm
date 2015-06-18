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

function createArchiveUrl($file_path)
{
    logWrite('[archive] createArchiveUrl called');
    // check if file exists
    if (!is_file($file_path))
    {
        logWrite('[archive] file not found : '. $file_path);
        return False;
    }

    // parse the path
    $splitted = explode('/', $file_path);
    if ($splitted[1] != 'home' || $splitted[3] != 'opensimulator' or ($splitted[4] != 'iar' && $splitted[4] != 'oar'))
    {
        logWrite('[archive] wrong file path : '. $file_path);
        return False;
    }

    $username = $splitted[2];
    $archive_type = $splitted[4];
    $expiration = time() + 300;
    $url = '/archive?q='. $archive_type. '/'. $username. '/'. md5($expiration. ':'. $GLOBALS['config']['password']). '/'. $expiration. '/'. $splitted[5];
    return $url;
}

function parseArchiveUrl($url)
{
    logWrite('[archive] parseArchiveUrl called');
    $splitted = explode('/', $url);
    if ($splitted[1] != 'archive' || ($splitted[2] != 'oar' && $splitted[2] != 'iar'))
    {
        logWrite('[archive] wrong url : '. $url);
        return Null;
    }

    $filename = $splitted[6];
    $username = $splitted[3];

    // check expiration
    $expiration = $splitted[5];
    $now = time();
    if ($now > $expiration)
    {
        logWrite('[archive] url expired : '. $url);
        return Null;
    }

    // check password
    if ($splitted[4] != md5($expiration. ':'. $GLOBALS['config']['password']))
    {
        logWrite('[archive] wrong password');
        return Null;
    }

    // check if file exists
    $file_path = '/home/'. $username. '/opensimulator/'. $splitted[2]. '/'. $filename;
    if (!is_file($file_path))
    {
        logWrite('[archive] file not found');
        return Null;
    }

    return $file_path;
}

