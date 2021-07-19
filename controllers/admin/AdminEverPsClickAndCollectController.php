<?php
/**
 * Project : everpsclickandcollect
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link http://team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'everpsclickandcollect/models/EverpsclickandcollectStore.php';

/**
 * @property Order $object
 */
class AdminEverPsClickAndCollectController extends ModuleAdminController
{
    public $toolbar_title;
    private $html;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'everpsclickandcollect_store';
        $this->className = 'EverpsclickandcollectStore';
        $this->identifier = 'id_everpsclickandcollect_store';
        $this->_orderBy = 'id_everpsclickandcollect_store';
        $this->_orderWay = 'DESC';
        $this->_use_found_rows = true;
        $this->allow_export = true;
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->context = Context::getContext();
        $this->_select = 'sl.name AS store_name';
        $this->_join = '
        LEFT JOIN '._DB_PREFIX_.'store_lang sl ON (
            a.`id_store` = sl.`id_store`
            AND sl.`id_lang` = '.(int)Context::getContext()->language->id.'
        )';
        $this->_group = 'GROUP BY a.id_store';
        $this->fields_list = array(
            'id_store' => array(
                'title' => $this->l('ID Store'),
                'align' => 'center'
            ),
            'store_name' => array(
                'title' => $this->l('Store name'),
                'align' => 'center'
            ),
            'monday_open' => array(
                'title' => $this->l('Monday opening'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'monday_close' => array(
                'title' => $this->l('Monday closing'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'tuesday_open' => array(
                'title' => $this->l('Tuesday opening'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'tuesday_close' => array(
                'title' => $this->l('Tuesday closing'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'wednesday_open' => array(
                'title' => $this->l('Wednesday opening'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'wednesday_close' => array(
                'title' => $this->l('Wednesday closing'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'thursday_open' => array(
                'title' => $this->l('Thursday opening'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'thursday_close' => array(
                'title' => $this->l('Thursday closing'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'friday_open' => array(
                'title' => $this->l('Friday opening'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'friday_close' => array(
                'title' => $this->l('Friday closing'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'saturday_open' => array(
                'title' => $this->l('Saturday opening'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'saturday_close' => array(
                'title' => $this->l('Saturday closing'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'sunday_open' => array(
                'title' => $this->l('Sunday opening'),
                'align' => 'left',
                'width' => 'auto',
            ),
            'sunday_close' => array(
                'title' => $this->l('Sunday closing'),
                'align' => 'left',
                'width' => 'auto',
            ),
        );
        $moduleConfUrl  = 'index.php?controller=AdminModules&configure=everpsclickandcollect&token=';
        $moduleConfUrl .= Tools::getAdminTokenLite('AdminModules');
        $this->toolbar_title = $this->l('Click and collect');
        $this->context->smarty->assign(array(
            'moduleConfUrl' => $moduleConfUrl,
            'everpsclickandcollect_dir' => _MODULE_DIR_ . '/everpsclickandcollect/'
        ));
        parent::__construct();
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->isSeven) {
            return Context::getContext()->getTranslator()->trans(
                $string,
                [],
                'Modules.Everpsclickandcollect.Admineverpsclickandcollectcontroller'
            );
        }

        return parent::l($string, $class, $addslashes, $htmlentities);
    }

    /**
     * Toolbar management
     */
    public function initPageHeaderToolbar()
    {
        //Bouton d'ajout
        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new element'),
            'icon' => 'process-icon-new'
        );
        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {
        $this->initToolbar();
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected items'),
                'confirm' => $this->l('Delete selected items ?')
            ),
        );
        if (Tools::isSubmit('submitBulkdelete'.$this->table)) {
            $this->processBulkDelete();
        }
        $lists = parent::renderList();
        $html = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everpsclickandcollect/views/templates/admin/header.tpl');
        $html .= $lists;
        $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/everpsclickandcollect/views/templates/admin/footer.tpl');

        return $html;
    }

    public function renderForm()
    {
        if (count($this->errors)) {
            return false;
        }

        // Building the Add/Edit form
        $this->fields_form = array(
            'tinymce' => true,
            'description' => $this->l('Click and collect settings'),
            'submit' => array(
                'name' => 'save',
                'title' => $this->l('Save'),
                'class' => 'btn button pull-right'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Monday opening hour'),
                    'desc' => $this->l('Monday open at'),
                    'hint' => $this->l('Shop opens monday at'),
                    'name' => 'monday_open',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Monday closing hour'),
                    'desc' => $this->l('Monday close at'),
                    'hint' => $this->l('Shop closes monday at'),
                    'name' => 'monday_close',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Tuesday opening hour'),
                    'desc' => $this->l('Tuesday open at'),
                    'hint' => $this->l('Shop opens tuesday at'),
                    'name' => 'tuesday_open',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Tuesday closing hour'),
                    'desc' => $this->l('Tuesday close at'),
                    'hint' => $this->l('Shop closes tuesday at'),
                    'name' => 'tuesday_close',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Wednesday opening hour'),
                    'desc' => $this->l('Wednesday open at'),
                    'hint' => $this->l('Shop opens wednesday at'),
                    'name' => 'wednesday_open',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Wednesday closing hour'),
                    'desc' => $this->l('Wednesday close at'),
                    'hint' => $this->l('Shop closes wednesday at'),
                    'name' => 'wednesday_close',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Thursday opening hour'),
                    'desc' => $this->l('Thursday open at'),
                    'hint' => $this->l('Shop opens thursday at'),
                    'name' => 'thursday_open',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Thursday closing hour'),
                    'desc' => $this->l('Thursday close at'),
                    'hint' => $this->l('Shop closes thursday at'),
                    'name' => 'thursday_close',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Friday opening hour'),
                    'desc' => $this->l('Friday open at'),
                    'hint' => $this->l('Shop opens friday at'),
                    'name' => 'friday_open',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Friday closing hour'),
                    'desc' => $this->l('Friday close at'),
                    'hint' => $this->l('Shop closes friday at'),
                    'name' => 'friday_close',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Saturday opening hour'),
                    'desc' => $this->l('Saturday open at'),
                    'hint' => $this->l('Shop opens saturday at'),
                    'name' => 'saturday_open',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Saturday closing hour'),
                    'desc' => $this->l('Saturday close at'),
                    'hint' => $this->l('Shop closes saturday at'),
                    'name' => 'saturday_close',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Sunday opening hour'),
                    'desc' => $this->l('Sunday open at'),
                    'hint' => $this->l('Shop opens sunday at'),
                    'name' => 'sunday_open',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Sunday closing hour'),
                    'desc' => $this->l('Sunday close at'),
                    'hint' => $this->l('Shop closes sunday at'),
                    'name' => 'sunday_close',
                ),
            )
        );
        $lists = parent::renderForm();

        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'everpsclickandcollect/views/templates/admin/header.tpl'
        );
        $this->html .= $lists;
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $this->html .= Tools::displayError($error);
            }
        }
        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'everpsclickandcollect/views/templates/admin/footer.tpl'
        );

        return $this->html;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('deleteeverpsclickandcollect')) {
            $clickandcollect_store = new EverpsclickandcollectStore(
                (int)Tools::getValue('id_everpsclickandcollect_store')
            );
            if (!$clickandcollect_store->delete()) {
                    $this->errors[] = Tools::displayError('An error has occurred: Can\'t update the current object');
            }
        }
        if (Tools::isSubmit('save')) {
            $clickandcollect_store = new EverpsclickandcollectStore(
                (int)Tools::getValue('id_everpsclickandcollect_store')
            );
            if (Tools::getValue('monday_open')
                && !Validate::isString(Tools::getValue('monday_open'))
            ) {
                $this->errors[] = $this->l('Monday open is not valid');
            } else {
                $clickandcollect_store->monday_open = Tools::getValue('monday_open');
            }
            if (Tools::getValue('tuesday_open')
                && !Validate::isString(Tools::getValue('tuesday_open'))
            ) {
                $this->errors[] = $this->l('Tuesday open is not valid');
            } else {
                $clickandcollect_store->tuesday_open = Tools::getValue('tuesday_open');
            }
            if (Tools::getValue('wednesday_open')
                && !Validate::isString(Tools::getValue('wednesday_open'))
            ) {
                $this->errors[] = $this->l('Wednesday open is not valid');
            } else {
                $clickandcollect_store->wednesday_open = Tools::getValue('wednesday_open');
            }
            if (Tools::getValue('thursday_open')
                && !Validate::isString(Tools::getValue('thursday_open'))
            ) {
                $this->errors[] = $this->l('Thursday open is not valid');
            } else {
                $clickandcollect_store->thursday_open = Tools::getValue('thursday_open');
            }
            if (Tools::getValue('friday_open')
                && !Validate::isString(Tools::getValue('friday_open'))
            ) {
                $this->errors[] = $this->l('Friday open is not valid');
            } else {
                $clickandcollect_store->friday_open = Tools::getValue('friday_open');
            }
            if (Tools::getValue('saturday_open')
                && !Validate::isString(Tools::getValue('saturday_open'))
            ) {
                $this->errors[] = $this->l('Saturday open is not valid');
            } else {
                $clickandcollect_store->saturday_open = Tools::getValue('saturday_open');
            }
            if (Tools::getValue('sunday_open')
                && !Validate::isString(Tools::getValue('sunday_open'))
            ) {
                $this->errors[] = $this->l('Sunday open is not valid');
            } else {
                $clickandcollect_store->sunday_open = Tools::getValue('sunday_open');
            }
            if (!count($this->errors)) {
                $clickandcollect_store->save();
            } else {
                $this->errors[] = $this->l('Can\'t update the current object');
            }
        }
        return parent::postProcess();
    }

    protected function processBulkDelete()
    {
        foreach (Tools::getValue($this->table.'Box') as $idEverObj) {
            $everpsclickandcollect = new EverpsclickandcollectStore((int)$idEverObj);

            if (!$everpsclickandcollect->delete()) {
                $this->errors[] = $this->l('An error has occurred: Can\'t delete the current object');
            }
        }
    }
}
