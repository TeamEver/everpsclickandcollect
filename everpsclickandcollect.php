<?php
/**
 * 2019-2021 Team Ever
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
 *  @copyright 2019-2021 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Everpsclickandcollect extends CarrierModule
{
    private $html;
    private $postErrors = array();
    private $postSuccess = array();

    public function __construct()
    {
        $this->name = 'everpsclickandcollect';
        $this->tab = 'shipping_logistics';
        $this->version = '2.1.0';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Ever PS Click And Collect');
        $this->description = $this->l('Click and Collect delivery method for Prestashop');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->siteUrl = Tools::getHttpHost(true).__PS_BASE_URI__;
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        // Install SQL
        include(dirname(__FILE__).'/sql/install.php');
        $this->addCarrier();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayCarrierExtraContent') &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('displayPDFDeliverySlip') &&
            $this->registerHook('displayPDFInvoice') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('updateCarrier');
    }

    public function uninstall()
    {
        // Install SQL
        include(dirname(__FILE__).'/sql/uninstall.php');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitEverpsclickandcollectModule')) == true) {
            $this->postValidation();

            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }
        if (count($this->postErrors)) {
            foreach ($this->postErrors as $error) {
                $this->html .= $this->displayError($error);
            }
        }
        if (count($this->postSuccess)) {
            foreach ($this->postSuccess as $success) {
                $this->html .= $this->displayConfirmation($success);
            }
        }
        $this->context->smarty->assign(array(
            'everpsclickandcollect_dir' => $this->_path,
        ));

        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/header.tpl');

        if ($this->checkLatestEverModuleVersion($this->name, $this->version)) {
            $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/upgrade.tpl');
        }
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/footer.tpl');

        return $this->html;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEverpsclickandcollectModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $stores = Store::getStores(
            (int)Context::getContext()->language->id
        );
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Allowed click and collect stores'),
                        'desc' => $this->l('If no stores available, please add at least one'),
                        'hint' => $this->l('Please choose at least one store'),
                        'name' => 'EVERPSCLICKANDCOLLECT_STORES_IDS[]',
                        'class' => 'chosen',
                        'identifier' => 'name',
                        'multiple' => true,
                        'required' => true,
                        'options' => array(
                            'query' => $stores,
                            'id' => 'id_store',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show date select'),
                        'desc' => $this->l('Set yes to ask customer to select a date'),
                        'hint' => $this->l('Else no date will be asked'),
                        'name' => 'EVERPSCLICKANDCOLLECT_ASK_DATE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'EVERPSCLICKANDCOLLECT_STORES_IDS[]' => json_decode(
                Configuration::get(
                    'EVERPSCLICKANDCOLLECT_STORES_IDS'
                )
            ),
            'EVERPSCLICKANDCOLLECT_ASK_DATE' => Configuration::get(
                'EVERPSCLICKANDCOLLECT_ASK_DATE'
            ),
        );
    }

    public function postValidation()
    {
        if (((bool)Tools::isSubmit('submitEverpsclickandcollectModule')) == true) {
            if (Tools::getValue('EVERPSCLICKANDCOLLECT_ASK_DATE')
                && !Validate::isBool(Tools::getValue('EVERPSCLICKANDCOLLECT_ASK_DATE'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Ask for date" is not valid'
                );
            }
            if (!Tools::getValue('EVERPSCLICKANDCOLLECT_STORES_IDS')
                || !Validate::isArrayWithIds(Tools::getValue('EVERPSCLICKANDCOLLECT_STORES_IDS'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Stores" is not valid'
                );
            }
        }
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        Configuration::updateValue(
            'EVERPSCLICKANDCOLLECT_ASK_DATE',
            Tools::getValue('EVERPSCLICKANDCOLLECT_ASK_DATE')
        );
        Configuration::updateValue(
            'EVERPSCLICKANDCOLLECT_STORES_IDS',
            json_encode(Tools::getValue('EVERPSCLICKANDCOLLECT_STORES_IDS')),
            true
        );
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        return 0;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    public function isAllowedStore($id_store)
    {
        $allowed_stores = $this->getAllowedStores();
        if (in_array($id_store, $allowed_stores)) {
            return true;
        }
        return false;
    }

    private function getAllowedStores()
    {
        $allowed_stores = json_decode(
            Configuration::get(
                'EVERPSCLICKANDCOLLECT_STORES_IDS'
            )
        );
        if (!is_array($allowed_stores)) {
            $allowed_stores = array($allowed_stores);
        }
        return $allowed_stores;
    }

    protected function addCarrier()
    {
        $result = false;
        $carrier = new Carrier();
        $carrier->name = $this->l('Click and collect');
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Pick your order on store');
        }

        if ($carrier->add() == true) {
            // Copy logo img as carrier logo
            @copy(
                dirname(__FILE__).'/views/img/carrier_image.jpg',
                _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg'
            );
            Configuration::updateValue(
                'EVERPSCLICKANDCOLLECT_CARRIER_ID',
                (int)$carrier->id
            );
            $result &= $this->addZones($carrier);
            $result &= $this->addGroups($carrier);
            $result &= $this->addRanges($carrier);
        }
        return $result;
    }

    protected function addGroups($carrier)
    {
        $groups_ids = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->setGroups($groups_ids);
    }

    protected function addRanges($carrier)
    {
        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();

        $range_weight = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '10000';
        $range_weight->add();
    }

    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $controller_name = Tools::getValue('controller');
        if ($controller_name == 'order') {
            $this->context->controller->addJS($this->_path.'/views/js/order.js');
        }
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookUpdateCarrier($params)
    {
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
        */
    }

    public function hookDisplayCarrierExtraContent($params)
    {
        $stores = $this->getTemplateVarStores();
        $evercnc_id_store = Context::getContext()->cookie->__get('everclickncollect_id');
        $everclickncollect_date = Context::getContext()->cookie->__get('everclickncollect_date');
        $shipping_stores = array();
        foreach ($stores as $store) {
            if ((bool)$this->isAllowedStore((int)$store['id_store']) === false) {
                continue;
            }
            if ((int)$store['id_store'] == (int)$evercnc_id_store) {
                $store['selected'] = true;
            } else {
                $store['selected'] = false;
            }
            $shipping_stores[] = $store;
        }
        $link = new Link();
        $ajax_url = $link->getModuleLink(
            $this->name,
            'ajaxEverShippingStore'
        );
        if ($stores && count($stores) > 0) {
            $only_one = count($stores) > 1 ? false : true;
            $this->smarty->assign(
                array(
                    'everclickncollect_date' => $everclickncollect_date,
                    'ask_date' => Configuration::get('EVERPSCLICKANDCOLLECT_ASK_DATE'),
                    'only_one' => $only_one,
                    'ajax_url' => $ajax_url,
                    'stores' => $shipping_stores,
                    'everclickncollect_id' => Configuration::get('EVERPSCLICKANDCOLLECT_CARRIER_ID')
                )
            );
            return $this->display(__FILE__, 'extra_carrier.tpl', $this->getCacheId());
        }
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $id_order = (int)$params['order']->id;
        $order = new Order(
            (int)$id_order
        );
        if ((int)$order->id_carrier != (int)Configuration::get('EVERPSCLICKANDCOLLECT_CARRIER_ID')) {
            return;
        }
        $evercnc_id_store = Context::getContext()->cookie->__get('everclickncollect_id');
        $everclickncollect_date = Context::getContext()->cookie->__get('everclickncollect_date');
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from(
            'everpsclickandcollect'
        );
        $sql->where(
            'id_cart = '.(int)$order->id_cart
        );
        $clickncollect = Db::getInstance()->getRow($sql);
        if (!$clickncollect) {
            Db::getInstance()->insert(
                'everpsclickandcollect',
                array(
                    'id_cart' => (int)$order->id_cart,
                    'evercnc_id_store' => (int)$evercnc_id_store,
                    'delivery_date' => (string)$everclickncollect_date
                ),
                false,
                true,
                Db::REPLACE
            );
        }
        $stores = $this->getTemplateVarStores();
        foreach ($stores as $tpl_store) {
            if ((int)$tpl_store['id_store'] == (int)$clickncollect['id_store']) {
                $store = $tpl_store;
            }
        }
        $this->context->smarty->assign(array(
            'store' => $store,
            'clickncollect' => $clickncollect
        ));
        Context::getContext()->cookie->__unset('everclickncollect_id');
        Context::getContext()->cookie->__unset('everclickncollect_date');
        Context::getContext()->cookie->__unset('everclickncollect_hour');
        return $this->display(__FILE__, 'views/templates/hook/order.tpl');
    }

    public function hookDisplayAdminOrder($params)
    {
        $id_order = (int)$params['id_order'];
        $order = new Order(
            (int)$id_order
        );
        if ((int)$order->id_carrier != (int)Configuration::get('EVERPSCLICKANDCOLLECT_CARRIER_ID')) {
            return;
        }
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from(
            'everpsclickandcollect'
        );
        $sql->where(
            'id_cart = '.(int)$order->id_cart
        );
        $clickncollect = Db::getInstance()->getRow($sql);
        if ($clickncollect) {
            $stores = $this->getTemplateVarStores();
            foreach ($stores as $tpl_store) {
                if ((int)$tpl_store['id_store'] == (int)$clickncollect['id_store']) {
                    $store = $tpl_store;
                }
            }
            $this->context->smarty->assign(array(
                'store' => $store,
                'clickncollect' => $clickncollect
            ));
            return $this->display(__FILE__, 'views/templates/hook/order.tpl');
        }
    }

    public function hookDisplayPDFDeliverySlip($params)
    {
        return $this->hookDisplayPDFInvoice($params);
    }

    public function hookDisplayPDFInvoice($params)
    {
        $id_order = (int)$params['object']->id_order;
        $order = new Order(
            (int)$id_order
        );
        if ((int)$order->id_carrier != (int)Configuration::get('EVERPSCLICKANDCOLLECT_CARRIER_ID')) {
            return;
        }
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from(
            'everpsclickandcollect'
        );
        $sql->where(
            'id_cart = '.(int)$order->id_cart
        );
        $clickncollect = Db::getInstance()->getRow($sql);
        if ($clickncollect) {
            $stores = $this->getTemplateVarStores();
            foreach ($stores as $tpl_store) {
                if ((int)$tpl_store['id_store'] == (int)$clickncollect['id_store']) {
                    $store = $tpl_store;
                }
            }
            $this->context->smarty->assign(array(
                'store' => $store,
                'clickncollect' => $clickncollect
            ));
            $this->context->smarty->assign(array(
                'store' => $store,
                'clickncollect' => $clickncollect
            ));
            return $this->display(__FILE__, 'views/templates/hook/invoice.tpl');
        }
    }

    public function getTemplateVarStores()
    {
        $stores = Store::getStores($this->context->language->id);

        $imageRetriever = new \PrestaShop\PrestaShop\Adapter\Image\ImageRetriever($this->context->link);

        foreach ($stores as &$store) {
            unset($store['active']);
            // Prepare $store.address
            $address = new Address();
            $store['address'] = [];
            $attr = ['address1', 'address2', 'postcode', 'city', 'id_state', 'id_country'];
            foreach ($attr as $a) {
                $address->{$a} = $store[$a];
                $store['address'][$a] = $store[$a];
                unset($store[$a]);
            }
            $store['address']['formatted'] = AddressFormat::generateAddress($address, [], '<br />');

            // Prepare $store.business_hours
            // Required for trad
            $temp = json_decode($store['hours'], true);
            unset($store['hours']);
            $store['business_hours'] = [
                [
                    'day' => $this->trans('Monday', [], 'Shop.Theme.Global'),
                    'hours' => $temp[0],
                ], [
                    'day' => $this->trans('Tuesday', [], 'Shop.Theme.Global'),
                    'hours' => $temp[1],
                ], [
                    'day' => $this->trans('Wednesday', [], 'Shop.Theme.Global'),
                    'hours' => $temp[2],
                ], [
                    'day' => $this->trans('Thursday', [], 'Shop.Theme.Global'),
                    'hours' => $temp[3],
                ], [
                    'day' => $this->trans('Friday', [], 'Shop.Theme.Global'),
                    'hours' => $temp[4],
                ], [
                    'day' => $this->trans('Saturday', [], 'Shop.Theme.Global'),
                    'hours' => $temp[5],
                ], [
                    'day' => $this->trans('Sunday', [], 'Shop.Theme.Global'),
                    'hours' => $temp[6],
                ],
            ];
            $store['image'] = $imageRetriever->getImage(new Store($store['id_store']), $store['id_store']);
            if (is_array($store['image'])) {
                $store['image']['legend'] = $store['image']['legend'][$this->context->language->id];
            }
        }

        return $stores;
    }

    public function checkLatestEverModuleVersion($module, $version)
    {
        $upgrade_link = 'https://upgrade.team-ever.com/upgrade.php?module='
        .$module
        .'&version='
        .$version;
        $handle = curl_init($upgrade_link);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            curl_close($handle);
            return false;
        }
        curl_close($handle);
        $module_version = Tools::file_get_contents(
            $upgrade_link
        );
        if ($module_version && $module_version > $version) {
            return true;
        }
        return false;
    }
}
