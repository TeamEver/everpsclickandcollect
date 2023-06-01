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

class EverpsclickandcollectStore extends ObjectModel
{
    public $id;
    public $id_store;
    public $monday_open;
    public $monday_close;
    public $tuesday_open;
    public $tuesday_close;
    public $wednesday_open;
    public $wednesday_close;
    public $thursday_open;
    public $thursday_close;
    public $friday_open;
    public $friday_close;
    public $saturday_open;
    public $saturday_close;
    public $sunday_open;
    public $sunday_close;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'everpsclickandcollect_store',
        'primary' => 'id_everpsclickandcollect_store',
        'fields' => array(
            'id_store' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ),
            'monday_open' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'monday_close' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'tuesday_open' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'tuesday_close' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'wednesday_open' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'wednesday_close' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'thursday_open' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'thursday_close' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'saturday_open' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'saturday_close' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'sunday_open' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
            'sunday_close' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isString',
                'required' => false
            ),
        ),
    );

    /**
     * Get store hours object for click and collect
     * @param int id_store
     * @return false | full object
    */
    public static function getByIdStore($id_store, $today_only = false)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('everpsclickandcollect_store');
        $sql->where('id_store = '.(int)$id_store);
        $return = new self(
            (int)Db::getInstance()->getValue($sql)
        );
        if (Validate::isLoadedObject($return)) {
            if ((bool)$today_only === false) {
                switch (date('l')) {
                    case 'Monday':
                        # code...
                        break;

                    case 'Tuesday':
                        # code...
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }
            // die(var_dump(date('l')));
            return $return;
        }
        return false;
    }

    /**
     * Get store hours object for click and collect
     * @param int id_store
     * @return false | full object
    */
    public static function getTodayHoursByIdStore($id_store)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('everpsclickandcollect_store');
        $sql->where('id_store = '.(int)$id_store);
        $return = new self(
            (int)Db::getInstance()->getValue($sql)
        );
        if (Validate::isLoadedObject($return)) {
            return $return;
        }
        return false;
    }
}
