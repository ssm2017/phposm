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

include('../../../etc/config.php');
include('../includes/common.php');
include('../includes/archive.php');
$query = $_GET['q'];
$file_path = parseArchiveUrl('/archive/'. $query);
if (is_null($file_path))
{
    echo 'file not found';
    exit;
}

if (file_exists($file_path))
{
    $size = filesize($file_path);
    header("Content-Type: application/force-download; name=\"" . basename($file_path) . "\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: $size");
    header("Content-Disposition: attachment; filename=\"" . basename($file_path) . "\"");
    header("Expires: 0");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    readfile($file_path);
}
?>
