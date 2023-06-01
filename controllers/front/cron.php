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

require_once _PS_MODULE_DIR_.'everpsclickandcollect/models/EverpsclickandcollectStoreStock.php';

class EverpsclickandcollectcronModuleFrontController extends ModuleFrontController
{
    public $controller_name = 'cron';

    public function init()
    {
        $this->smileys = array(
            '😀',
            '😁',
            '😃',
            '😄',
            '😇',
            '😉',
            '😊',
            '😋',
            '😌',
            '😎',
            '😏',
            '😗',
            '😘',
            '😙',
            '😚',
            '😛',
            '😜',
            '😝',
            '😬',
            '😶',
            '🙂',
            '🙃',
            '🙄',
            '🤐',
            '🤫',
            '🧐'
        );
        $this->randSmiley = array_rand($this->smileys);
        if (!Tools::getValue('token')
            || Tools::encrypt('everpsclickandcollect/cron') != Tools::getValue('token')
            || !Module::isInstalled('everpsclickandcollect')
        ) {
            Tools::redirect('index.php');
        }
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::init();
    }

    public function initContent()
    {
        if (!Tools::getValue('token')
            || Tools::encrypt('everpsclickandcollect/cron') != Tools::getValue('token')
            || !Module::isInstalled('everpsclickandcollect')
        ) {
            Tools::redirect('index.php');
        }
        $stock_file = _PS_MODULE_DIR_.'everpsclickandcollect/views/import/store_stock.csv';
        if (!file_exists($stock_file)) {
            Tools::redirect('index.php');
        }
        $csvData = array_map(
            'str_getcsv',
            file($stock_file)
        );
        $result = true;
        foreach ($csvData as $key => $line) {
            if ($key == 0) {
                continue;
            }
            $line_datas = explode(';', $line[0]);
            $result &= EverpsclickandcollectStoreStock::importStoreStock(
                $line_datas,
                (int)Context::getContext()->shop->id
            );
        }
        // 🍩
        if ((bool)$result === true) {
            unlink($stock_file);
            die(
                $this->smileys[$this->randSmiley]
                .' All store stock has been updated '
                .$this->smileys[$this->randSmiley]
            );
        } else {
            die(
                'An error has occured, please contact us'
            );
        }
        Tools::redirect('index.php');
    }
}
