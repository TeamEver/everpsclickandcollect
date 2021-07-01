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

use PrestaShop\PrestaShop\Core\Product\ProductExtraContent;
require_once _PS_MODULE_DIR_.'everpsclickandcollect/models/EverpsclickandcollectStoreStock.php';

class Everpsclickandcollect extends CarrierModule
{
    private $html;
    private $postErrors = array();
    private $postSuccess = array();

    public function __construct()
    {
        $this->name = 'everpsclickandcollect';
        $this->tab = 'shipping_logistics';
        $this->version = '2.1.5';
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
            // $this->installModuleTab(
            //     'AdminEverPsClickAndCollect',
            //     'AdminParentShipping',
            //     $this->l('Click And Collect')
            // ) &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayCarrierExtraContent') &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('displayPDFDeliverySlip') &&
            $this->registerHook('displayPDFInvoice') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('actionEmailSendBefore') &&
            $this->registerHook('actionUpdateQuantity') &&
            $this->registerHook('updateCarrier') &&
            $this->registerHook('displayAdminProductsQuantitiesStepBottom') &&
            $this->registerHook('actionObjectProductUpdateAfter') &&
            $this->registerHook('displayProductExtraContent') &&
            $this->registerHook('actionUpdateQuantity') &&
            $this->registerHook('actionObjectProductDeleteAfter');
    }

    public function uninstall()
    {
        // Install SQL
        include(dirname(__FILE__).'/sql/uninstall.php');
        $carrier = new Carrier(
            (int)Configuration::get('EVERPSCLICKANDCOLLECT_CARRIER_ID')
        );
        $carrier->delete();
        Configuration::deleteByName('EVERPSCLICKANDCOLLECT_CARRIER_ID');
        Configuration::deleteByName('EVERPSCLICKANDCOLLECT_ASK_DATE');
        // return parent::uninstall()
        //     && $this->uninstallModuleTab('AdminEverPsClickAndCollect');
        return parent::uninstall();
    }

    private function installModuleTab($tabClass, $parent, $tabName)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if ($tabClass == 'AdminEverPsBlog' && $this->isSeven) {
            $tab->icon = 'icon-team-ever';
        }

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int)$lang['id_lang']] = $tabName;
        }

        return $tab->add();
    }

    private function uninstallModuleTab($tabClass)
    {
        $tab = new Tab((int)Tab::getIdFromClassName($tabClass));

        return $tab->delete();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $this->registerHook('actionEmailSendBefore');
        $this->registerHook('actionObjectProductDeleteAfter');
        $cron = $this->context->link->getModuleLink(
            $this->name,
            'cron',
            array(
                'token' => Tools::encrypt($this->name.'/cron')
            ),
            true,
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        if (((bool)Tools::isSubmit('submitEverpsclickandcollectModule')) == true) {
            $this->postValidation();

            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }
        if (((bool)Tools::isSubmit('submitImportStock')) == true) {
            $this->importStockFromCsv(
                (int)Context::getContext()->shop->id
            );
        }
        if (((bool)Tools::isSubmit('submitExportStock')) == true) {
            $this->exportStoreStockToCsv(
                (int)Context::getContext()->shop->id
            );
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
            'everpsclickandcollect_cron' => $cron,
            'stock_file' => _PS_MODULE_DIR_.'everpsclickandcollect/views/import/store_stock.csv',
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
                'icon' => 'icon-smile',
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('Import store stock from CSV file'),
                        'desc' => $this->l('Will update stock for each product and store'),
                        'hint' => $this->l('Please export store stock first'),
                        'name' => 'store_stock_file',
                        'display_image' => false,
                        'required' => false
                    ),
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
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Manage stock on each store and product ?'),
                        'desc' => $this->l('Will allow you to manage stock on each store and product'),
                        'hint' => $this->l('Else all store and product will be available for click and collect'),
                        'name' => 'EVERPSCLICKANDCOLLECT_STOCK',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Default decrement stock on this store'),
                        'desc' => $this->l('Store will be decremented on this store by default'),
                        'hint' => $this->l('Please choose at least one store'),
                        'name' => 'EVERPSCLICKANDCOLLECT_DEFAULT_STORE',
                        'identifier' => 'name',
                        'required' => true,
                        'options' => array(
                            'query' => $stores,
                            'id' => 'id_store',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show each store stock on product page ?'),
                        'desc' => $this->l('Will show product store stock as table on product page'),
                        'hint' => $this->l('Else no store stock will be shown'),
                        'name' => 'EVERPSCLICKANDCOLLECT_TAB',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show store image on order tunnel ?'),
                        'desc' => $this->l('Will show each store image on order tunnel'),
                        'hint' => $this->l('Else no store image will be shown'),
                        'name' => 'EVERPSCLICKANDCOLLECT_IMG',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'buttons' => array(
                    'importStock' => array(
                        'name' => 'submitImportStock',
                        'type' => 'submit',
                        'class' => 'btn btn-success pull-right',
                        'icon' => 'process-icon-upload',
                        'title' => $this->l('Import store stock file')
                    ),
                    'exportStoreStock' => array(
                        'name' => 'submitExportStock',
                        'type' => 'submit',
                        'class' => 'btn btn-info pull-right',
                        'icon' => 'process-icon-download',
                        'title' => $this->l('Export store stock to CSV')
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
            'EVERPSCLICKANDCOLLECT_DEFAULT_STORE' => Configuration::get(
                'EVERPSCLICKANDCOLLECT_DEFAULT_STORE'
            ),
            'EVERPSCLICKANDCOLLECT_ASK_DATE' => Configuration::get(
                'EVERPSCLICKANDCOLLECT_ASK_DATE'
            ),
            'EVERPSCLICKANDCOLLECT_STOCK' => Configuration::get(
                'EVERPSCLICKANDCOLLECT_STOCK'
            ),
            'EVERPSCLICKANDCOLLECT_TAB' => Configuration::get(
                'EVERPSCLICKANDCOLLECT_TAB'
            ),
            'EVERPSCLICKANDCOLLECT_IMG' => Configuration::get(
                'EVERPSCLICKANDCOLLECT_IMG'
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
            if (Tools::getValue('EVERPSCLICKANDCOLLECT_STOCK')
                && !Validate::isBool(Tools::getValue('EVERPSCLICKANDCOLLECT_STOCK'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Manage stock" is not valid'
                );
            }
            if (Tools::getValue('EVERPSCLICKANDCOLLECT_TAB')
                && !Validate::isBool(Tools::getValue('EVERPSCLICKANDCOLLECT_TAB'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show stock on product page" is not valid'
                );
            }
            if (Tools::getValue('EVERPSCLICKANDCOLLECT_IMG')
                && !Validate::isBool(Tools::getValue('EVERPSCLICKANDCOLLECT_IMG'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Show stock on product page" is not valid'
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
        Configuration::updateValue(
            'EVERPSCLICKANDCOLLECT_STOCK',
            Tools::getValue('EVERPSCLICKANDCOLLECT_STOCK')
        );
        Configuration::updateValue(
            'EVERPSCLICKANDCOLLECT_DEFAULT_STORE',
            Tools::getValue('EVERPSCLICKANDCOLLECT_DEFAULT_STORE')
        );
        Configuration::updateValue(
            'EVERPSCLICKANDCOLLECT_TAB',
            Tools::getValue('EVERPSCLICKANDCOLLECT_TAB')
        );
        Configuration::updateValue(
            'EVERPSCLICKANDCOLLECT_IMG',
            Tools::getValue('EVERPSCLICKANDCOLLECT_IMG')
        );
        $this->postSuccess[] = $this->l('All settings have been saved');
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
        $carrier->name = 'Click and collect';
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
        if ((int)$params['id_carrier'] == (int)Configuration::get('EVERPSCLICKANDCOLLECT_CARRIER_ID')) {
            Configuration::updateValue(
                'EVERPSCLICKANDCOLLECT_CARRIER_ID',
                $params['carrier']->id
            );
        }
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
        */
    }

    public function hookDisplayCarrierExtraContent($params)
    {
        $cart = Context::getContext()->cart;
        $cartproducts = $cart->getProducts();
        $stores = $this->getTemplateVarStores();
        $evercnc_id_store = Context::getContext()->cookie->__get('everclickncollect_id');
        $everclickncollect_date = Context::getContext()->cookie->__get('everclickncollect_date');
        $shipping_stores = array();
        foreach ($stores as $key => $store) {
            if ((bool)$this->isAllowedStore((int)$store['id_store']) === false) {
                continue;
            }
            if ($evercnc_id_store) {
                if ((int)$store['id_store'] == (int)$evercnc_id_store) {
                    $store['selected'] = true;
                } else {
                    $store['selected'] = false;
                }
            } else {
                if ($key == 0) {
                    $store['selected'] = true;
                } else {
                    $store['selected'] = false;
                }
            }
            if ((bool)$store['selected'] === true) {
                if ((bool)Configuration::get('EVERPSCLICKANDCOLLECT_ASK_DATE') === true) {
                    $delivery_date = $store['business_hours'][0]['day'];
                } else {
                    $delivery_date = null;
                }
                Db::getInstance()->insert(
                    'everpsclickandcollect',
                    array(
                        'id_cart' => (int)$cart->id,
                        'id_store' => (int)$store['id_store'],
                        'delivery_date' => $delivery_date
                    ),
                    false,
                    true,
                    Db::REPLACE
                );
                $this->context->cookie->__set(
                    'everclickncollect_id',
                    (int)$store['id_store']
                );
            }
            // Manage stock on each store and product if allowed
            if ((bool)Configuration::get('EVERPSCLICKANDCOLLECT_STOCK') === true) {
                foreach ($cartproducts as $cartproduct) {
                    $stock = EverpsclickandcollectStoreStock::getStoreStockAvailableByProductId(
                        (int)$store['id_store'],
                        (int)$cartproduct['id_product'],
                        (int)$cartproduct['id_product_attribute'],
                        (int)Context::getContext()->shop->id
                    );
                    if ((int)$stock < 0 || (int)$cartproduct['cart_quantity'] > (int)$stock) {
                        continue 2;
                    } else {
                        $shipping_stores[] = $store;
                    }
                }
            } else {
                $shipping_stores[] = $store;
            }
        }
        $link = new Link();
        $ajax_url = $link->getModuleLink(
            $this->name,
            'ajaxEverShippingStore'
        );
        if (empty($shipping_stores)) {
            $this->smarty->assign(
                array(
                    'everclickncollect_id' => Configuration::get('EVERPSCLICKANDCOLLECT_CARRIER_ID')
                )
            );
            return $this->display(__FILE__, 'no_carrier.tpl', $this->getCacheId());
        }
        if ($stores && count($stores) > 0) {
            $only_one = count($stores) > 1 ? false : true;
            $this->smarty->assign(
                array(
                    'everclickncollect_date' => $everclickncollect_date,
                    'ask_date' => Configuration::get('EVERPSCLICKANDCOLLECT_ASK_DATE'),
                    'show_store_img' => Configuration::get('EVERPSCLICKANDCOLLECT_IMG'),
                    'only_one' => $only_one,
                    'ajax_url' => $ajax_url,
                    'stores' => $shipping_stores,
                    'everclickncollect_id' => Configuration::get('EVERPSCLICKANDCOLLECT_CARRIER_ID')
                )
            );
            return $this->display(__FILE__, 'extra_carrier.tpl', $this->getCacheId());
        }
    }

    public function hookDisplayProductExtraContent($params)
    {
        if ((bool)Configuration::get('EVERPSCLICKANDCOLLECT_TAB') === false) {
            return;
        }
        $product = new Product(
            (int)$params['product']->id,
            false,
            (int)Context::getContext()->language->id,
            (int)Context::getContext()->shop->id
        );
        $stores = $this->getTemplateVarStores();
        $shipping_stores = array();
        foreach ($stores as $key => $store) {
            if ((bool)$this->isAllowedStore((int)$store['id_store']) === false) {
                continue;
            }
            // Manage stock on each store and product if allowed
            if ((bool)Configuration::get('EVERPSCLICKANDCOLLECT_STOCK') === true) {
                // store stock depending on combinations
                if ($product->hasCombinations()) {
                    $attr_resumes = $product->getAttributesResume(
                        (int)Context::getContext()->language->id
                    );
                    $store['has_combinations'] = true;
                    foreach ($attr_resumes as $attr_resume) {
                        $product_stock = EverpsclickandcollectStoreStock::getStoreStockAvailableByProductId(
                            (int)$store['id'],
                            (int)$product->id,
                            (int)$attr_resume['id_product_attribute'],
                            (int)Context::getContext()->shop->id
                        );
                        $store['id_product_attribute'] = (int)$attr_resume['id_product_attribute'];
                        $store['attribute_designation'] = (string)$attr_resume['attribute_designation'];
                        $store['qty'] = $product_stock;
                        if ((int)$store['qty'] > 0) {
                            $shipping_stores[] = $store;
                        }
                    }
                } else {
                    $product_stock = EverpsclickandcollectStoreStock::getStoreStockAvailableByProductId(
                        (int)$store['id'],
                        (int)$product->id,
                        0,
                        (int)Context::getContext()->shop->id
                    );
                    $store['qty'] = $product_stock;
                    if ((int)$store['qty'] > 0) {
                        $shipping_stores[] = $store;
                    }
                }
                if ((int)$store['qty'] <= 0) {
                    continue;
                }
            } else {
                $shipping_stores[] = $store;
            }
        }
        $link = new Link();
        $ajax_url = $link->getModuleLink(
            $this->name,
            'ajaxEverShippingStore'
        );
        if (empty($shipping_stores)) {
            return;
        }
        if ($shipping_stores && count($shipping_stores) > 0) {
            $only_one = count($shipping_stores) > 1 ? false : true;
            $this->context->smarty->assign(
                array(
                    'has_combinations' => $product->hasCombinations(),
                    'only_one' => $only_one,
                    'manage_stock' => Configuration::get('EVERPSCLICKANDCOLLECT_STOCK'),
                    'shipping_stores' => $shipping_stores
                )
            );
            $content = $this->context->smarty->fetch(
                'module:everpsclickandcollect/views/templates/hook/reassurance.tpl'
            );
            $array = array();
            $array[] = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent())
                    ->setTitle($this->l('Click and collect'))
                    ->setContent($content);
            return $array;
        }
    }

    public function hookActionEmailSendBefore($params)
    {
        if (isset($params['templateVars']['{id_order}'])) {
            $id_order = (int)$params['templateVars'] ["{id_order}"];
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
                // Replace carrier var
                $params['templateVars'] ["{carrier}"] = $params['templateVars'] ["{carrier}"]
                .' '
                .$store['name']
                .' '
                .$store['address']['formatted'];
                // Replace shipping number var
                $params['templateVars'] ["{shipping_number}"] = $params['templateVars'] ["{shipping_number}"]
                .' - '
                .$store['name']
                .' '
                .$store['address']['formatted'];
            }
        }
        return $params;
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
        if (!empty($evercnc_id_store)) {
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
        if (!$clickncollect) {
            Db::getInstance()->insert(
                'everpsclickandcollect',
                array(
                    'id_cart' => (int)$order->id_cart,
                    'id_store' => (int)$evercnc_id_store,
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
        // Here we should decrement store stock, but action stock hook is triggered
        if (isset($store)) {
            $this->context->smarty->assign(array(
                'store' => $store,
                'clickncollect' => $clickncollect
            ));
            Context::getContext()->cookie->__unset('everclickncollect_id');
            Context::getContext()->cookie->__unset('everclickncollect_date');
            return $this->display(__FILE__, 'views/templates/hook/order.tpl');
        }
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

    public function hookDisplayAdminProductsQuantitiesStepBottom($params)
    {
        if (!$params['id_product']) {
            return;
        }
        if ((bool)Configuration::get('EVERPSCLICKANDCOLLECT_STOCK') === false) {
            return;
        }
        $product = new Product(
            (int)$params['id_product'],
            false,
            (int)Context::getContext()->language->id,
            (int)Context::getContext()->shop->id
        );
        $shipping_stores = array();
        $stores = $this->getTemplateVarStores();
        foreach ($stores as $store) {
            if ((bool)$this->isAllowedStore((int)$store['id_store']) === false) {
                continue;
            }
            if ($product->hasCombinations()) {
                $attr_resumes = $product->getAttributesResume(
                    (int)Context::getContext()->language->id
                );
                $store['has_combinations'] = true;
                foreach ($attr_resumes as $attr_resume) {
                    $product_stock = EverpsclickandcollectStoreStock::getStoreStockAvailableByProductId(
                        (int)$store['id'],
                        (int)$params['id_product'],
                        (int)$attr_resume['id_product_attribute'],
                        (int)Context::getContext()->shop->id
                    );
                    $store['id_product_attribute'] = (int)$attr_resume['id_product_attribute'];
                    $store['attribute_designation'] = (string)$attr_resume['attribute_designation'];
                    $store['qty'] = $product_stock;
                    $shipping_stores[] = $store;
                }
            } else {
                $product_stock = EverpsclickandcollectStoreStock::getStoreStockAvailableByProductId(
                    (int)$store['id'],
                    (int)$params['id_product'],
                    0,
                    (int)Context::getContext()->shop->id
                );
                $store['qty'] = $product_stock;
            }
            $store['product_name'] = $product->name;
            $store['id_product'] = $params['id_product'];
            
            $shipping_stores[] = $store;
        }
        $this->smarty->assign(array(
            'shipping_stores' => (array)$shipping_stores,
            'default_language' => $this->context->employee->id_lang,
            'id_product' => (int)$params['id_product']
        ));
        return $this->display(__FILE__, 'views/templates/admin/product-tab.tpl');
    }

    public function hookActionObjectProductUpdateAfter($params)
    {
        if ((bool)Configuration::get('EVERPSCLICKANDCOLLECT_STOCK') === false) {
            return;
        }
        $product = new Product(
            (int)Tools::getValue('id_product'),
            false,
            (int)Context::getContext()->language->id,
            (int)Context::getContext()->shop->id
        );
        $stores = $this->getTemplateVarStores();
        foreach ($stores as $store) {
            if ((bool)$this->isAllowedStore((int)$store['id_store']) === false) {
                continue;
            }
            if ($product->hasCombinations()) {
                $attr_resumes = $product->getAttributesResume(
                    (int)Context::getContext()->language->id
                );
                foreach ($attr_resumes as $attr_resume) {
                    $qty_value = 'everpsclickandcollect_qty_'
                    .(int)$store['id']
                    .(int)$attr_resume['id_product_attribute'];
                    EverpsclickandcollectStoreStock::setQuantity(
                        (int)$store['id'],
                        (int)Tools::getValue('id_product'),
                        (int)$attr_resume['id_product_attribute'],
                        (int)Tools::getValue($qty_value),
                        (int)Context::getContext()->shop->id
                    );
                }
            } else {
                $qty_value = 'everpsclickandcollect_qty_'.(int)$store['id'];
                EverpsclickandcollectStoreStock::setQuantity(
                    (int)$store['id'],
                    (int)Tools::getValue('id_product'),
                    0,
                    (int)Tools::getValue($qty_value),
                    (int)Context::getContext()->shop->id
                );
            }
        }
    }

    public function hookActionUpdateQuantity($params)
    {
        $controllerTypes = array('front', 'modulefront');
        if (!in_array(Context::getContext()->controller->controller_type, $controllerTypes)) {
            return;
        }
        // If id store exists on customer cookie, let's lower stock
        $evercnc_id_store = Context::getContext()->cookie->__get('everclickncollect_id');
        if (isset($evercnc_id_store)
            && !empty($evercnc_id_store)
        ) {
            $evercnc_id_store = (int)Configuration::get('EVERPSCLICKANDCOLLECT_DEFAULT_STORE');
        }
        EverpsclickandcollectStoreStock::setQuantity(
            (int)$evercnc_id_store,
            (int)$params['id_product'],
            (int)$params['id_product_attribute'],
            (int)$params['quantity'],
            (int)Context::getContext()->shop->id
        );
    }

    public function hookActionObjectProductDeleteAfter($params)
    {
        EverpsclickandcollectStoreStock::dropProductStock(
            (int)$params['object']->id
        );
    }

    public function hookActionObjectStoreDeleteAfter($params)
    {
        EverpsclickandcollectStoreStock::dropStoreStock(
            (int)$params['object']->id
        );
    }

    public function hookActionAttributeCombinationDelete($params)
    {
        EverpsclickandcollectStoreStock::dropStoreStock(
            (int)$params['object']->id
        );
    }

    private function exportStoreStockToCsv()
    {
        $objects = EverpsclickandcollectStoreStock::getStoresStocksObjects();
        $csv_datas = array();
        foreach ($objects as $obj) {
            $csv_datas[] = array(
                utf8_decode($obj->id_store),
                utf8_decode($obj->id_product),
                utf8_decode($obj->id_product_attribute),
                utf8_decode($obj->reference),
                utf8_decode($obj->product_name),
                utf8_decode($obj->attribute_designation),
                utf8_decode($obj->store_name),
                utf8_decode($obj->qty),
            );
        }
        // output headers so that the file is downloaded rather than displayed
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="store_stock.csv"');
         
        // do not cache the file
        header('Pragma: no-cache');
        header('Expires: 0');
         
        // create a file pointer connected to the output stream
        $file = fopen('php://output', 'w');
        // send the column headers
        fputcsv(
            $file,
            array(
                $this->l('ID store *'),
                $this->l('ID product *'),
                $this->l('ID product attribute *'),
                $this->l('Reference'),
                $this->l('Product name'),
                $this->l('Attribute designation'),
                $this->l('Store name'),
                $this->l('Quantity *')
            ),
            ';'
        );
         
        // output each row of the data
        foreach ($csv_datas as $row) {
            fputcsv($file, $row, ';');
        }
        exit();
    }

    private function importStockFromCsv()
    {
        if (isset($_FILES['store_stock_file'])
            && isset($_FILES['store_stock_file']['tmp_name'])
            && !empty($_FILES['store_stock_file']['tmp_name'])
        ) {
            $csvData = array_map('str_getcsv', file($_FILES['store_stock_file']['tmp_name']));
            foreach ($csvData as $key => $line) {
                if ($key == 0) {
                    continue;
                }
                $line_datas = explode(';', $line[0]);
                EverpsclickandcollectStoreStock::importStoreStock(
                    $line_datas,
                    (int)Context::getContext()->shop->id
                );
            }
        }
        $this->postSuccess[] = $this->l('Stores stock has been updated');
        return true;
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
        curl_close($handle);
        if ($httpCode != 200) {
            return false;
        }
        $module_version = Tools::file_get_contents(
            $upgrade_link
        );
        if ($module_version && $module_version > $version) {
            return true;
        }
        return false;
    }
}
