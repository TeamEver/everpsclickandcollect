<?php
/**
 * 2019-2023 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2023 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_5()
{
    $result = false;
    $sql = array();
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'everpsclickandcollect_store_data` (
        `everpsclickandcollect_store_data` int(11) NOT NULL AUTO_INCREMENT,
        `id_store` int(11) NOT NULL,
        `monday_open` varchar(255) DEFAULT NULL,
        `monday_close` varchar(255) DEFAULT NULL,
        `tuesday_open` varchar(255) DEFAULT NULL,
        `tuesday_close` varchar(255) DEFAULT NULL,
        `wednesday_open` varchar(255) DEFAULT NULL,
        `wednesday_close` varchar(255) DEFAULT NULL,
        `thursday_open` varchar(255) DEFAULT NULL,
        `thursday_close` varchar(255) DEFAULT NULL,
        `friday_open` varchar(255) DEFAULT NULL,
        `friday_close` varchar(255) DEFAULT NULL,
        `saturday_open` varchar(255) DEFAULT NULL,
        `saturday_close` varchar(255) DEFAULT NULL,
        `sunday_open` varchar(255) DEFAULT NULL,
        `sunday_close` varchar(255) DEFAULT NULL,
        PRIMARY KEY  (`everpsclickandcollect_store_data`,`id_store`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
    
    foreach ($sql as $s) {
        $result &= Db::getInstance()->execute($s);
    }
    return $result;
}
