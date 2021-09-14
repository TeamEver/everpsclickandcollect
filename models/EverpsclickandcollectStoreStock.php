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

class EverpsclickandcollectStoreStock extends ObjectModel
{
    public $id;
    public $id_store;
    public $id_product;
    public $id_product_attribute;
    public $id_shop;
    public $qty;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'everpsclickandcollect_store_stock',
        'primary' => 'id_everpsclickandcollect_store_stock',
        'fields' => array(
            'id_store' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'id_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'id_product_attribute' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
            'qty' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId'
            ),
        ),
    );

    /**
     * Set stock quantity per store for one product
     *
     * @param int $id_store
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $qty
     * @param int $id_shop Optionnal
     *
     * @return int
     */
    public static function setQuantity($id_store, $id_product, $id_product_attribute, $qty, $id_shop = null)
    {
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }
        if (!Validate::isUnsignedId($id_store)) {
            return false;
        }
        $context = Context::getContext();
        // if there is no $id_shop, gets the context one
        if ($id_shop === null && Shop::getContext() != Shop::CONTEXT_GROUP) {
            $id_shop = (int) $context->shop->id;
        }

        // Get stock obj per store for this product
        $stock_obj = self::getStockAvailableObjByProductId(
            (int)$id_store,
            (int)$id_product,
            (int)$id_product_attribute,
            (int)$id_shop
        );
        // Old stock, used for hook
        $old_qty = self::getStoreStockAvailableByProductId(
            (int)$id_store,
            (int)$id_product,
            (int)$id_product_attribute,
            (int)$id_shop
        );
        // Trigger hook before (っ▀¯▀)つ
        Hook::exec(
            'actionUpdateStoreQuantityBefore',
            [
                'id_store' => (int)$id_store,
                'id_product' => (int)$id_product,
                'id_product_attribute' => (int)$id_product_attribute,
                'old_qty' => (int)$old_qty,
                'qty' => (int)$qty
            ]
        );
        if (!Validate::isLoadedObject($stock_obj)) {
            $stock_obj = new self();
        }
        $stock_obj->id_store = (int)$id_store;
        $stock_obj->id_product = (int)$id_product;
        $stock_obj->id_product_attribute = (int)$id_product_attribute;
        $stock_obj->id_shop = (int)$id_shop;
        $stock_obj->qty = (int)$qty;
        $saved = $stock_obj->save();

        // Trigger hook after ¯\_(ツ)_/¯
        Hook::exec(
            'actionUpdateStoreQuantityAfter',
            [
                'id_store' => (int)$id_store,
                'id_product' => (int)$id_product,
                'id_product_attribute' => (int)$id_product_attribute,
                'old_qty' => (int)$old_qty,
                'qty' => (int)$qty
            ]
        );
        return $saved;
    }

    /**
     * Get stock per store for one product
     *
     * @param int $id_store
     * @param int $id_product
     * @param int $id_product_attribute Optionnal
     * @param int $id_shop Optionnal
     *
     * @return int
     */
    public static function getStoreStockAvailableByProductId(
        $id_store,
        $id_product,
        $id_product_attribute = null,
        $id_shop = null
    ) {
        if ((bool)Configuration::get('EVERPSCLICKANDCOLLECT_STOCK') === false) {
            return 99999;
        }
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }
        $context = Context::getContext();
        // if there is no $id_shop, gets the context one
        if ($id_shop === null && Shop::getContext() != Shop::CONTEXT_GROUP) {
            $id_shop = (int) $context->shop->id;
        }

        // Trigger (•_•)
        // Hook ( •_•)>⌐■-■
        // Before (⌐■_■)
        Hook::exec(
            'actionGetStoreStockAvailableIdByProductIdBefore',
            [
                'id_store' => (int)$id_store,
                'id_product' => (int)$id_product,
                'id_product_attribute' => (int)$id_product_attribute
            ]
        );

        $sql = new DbQuery();
        $sql->select(
            'qty'
        );
        $sql->from(
            'everpsclickandcollect_store_stock'
        );
        $sql->where(
            'id_product = '.(int)$id_product
        );
        $sql->where(
            'id_product_attribute = '.(int)$id_product_attribute
        );
        $sql->where(
            'id_store = '.(int)$id_store
        );
        $sql->where(
            'id_shop = '.(int)$id_shop
        );
        $qty = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        // Trigger (⌐■_■)
        // Hook ( •_•)>⌐■-■
        // After (•_•)
        Hook::exec(
            'actionGetStoreStockAvailableByProductIdAfter',
            [
                'id_store' => (int)$id_store,
                'id_product' => (int)$id_product,
                'id_product_attribute' => (int)$id_product_attribute,
                'qty' => (int)$qty,
            ]
        );
        return $qty;
    }

    public static function getStockAvailableObjByProductId(
        $id_store,
        $id_product,
        $id_product_attribute = null,
        $id_shop = null
    ) {
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select(
            'id_everpsclickandcollect_store_stock'
        );
        $sql->from(
            'everpsclickandcollect_store_stock'
        );
        $sql->where(
            'id_store = '.(int)$id_store
        );
        $sql->where(
            'id_product = '.(int)$id_product
        );
        $sql->where(
            'id_product_attribute = '.(int)$id_product_attribute
        );
        $sql->where(
            'id_shop = '.(int)$id_shop
        );
        $stock_id = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($stock_id > 0) {
            return new self(
                (int)$stock_id
            );
        }
        // ......_________________________
        // ....../ `---___________--------    | ============= FALSE-BULLET !
        // ...../_==o;;;;;;;;______________|
        // .....), ---.(_(__) /
        // .......// (..) ), /--
        // ... //___//---
        // .. //___//
        // .//___//
        // //___//
        return false;
    }

    public static function getStoreStockObjectByIdProduct($id_product, $id_product_attribute = null, $id_shop = null)
    {
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select(
            'id_everpsclickandcollect_store_stock'
        );
        $sql->from(
            'everpsclickandcollect_store_stock'
        );
        $sql->where(
            'id_product = '.(int)$id_product
        );
        $sql->where(
            'id_product_attribute = '.(int)$id_product_attribute
        );
        $sql->where(
            'id_shop = '.(int)$id_shop
        );
        $stock_id = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($stock_id > 0) {
            return new self(
                (int)$stock_id
            );
        }
        return false;
    }

    public static function getStoreName($id_store, $id_lang)
    {
        if (!Validate::isUnsignedId($id_store)) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select(
            'name'
        );
        $sql->from(
            'store_lang'
        );
        $sql->where(
            'id_store = '.(int)$id_store
        );
        $sql->where(
            'id_lang = '.(int)$id_lang
        );
        $store_name = (string)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        return $store_name;
    }

    public static function getStoresStocksObjects()
    {
        $all_stock = array();
        $sql = new DbQuery();
        $sql->select(
            'id_product'
        );
        $sql->from(
            'product_shop'
        );
        $sql->where(
            'id_shop = '.(int)Context::getContext()->shop->id
        );
        $id_products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        foreach ($id_products as $prod) {
            $product = new Product(
                (int)$prod['id_product'],
                false,
                (int)Context::getContext()->language->id,
                (int)Context::getContext()->shop->id
            );
            if ($product->hasCombinations()) {
                $attr_resumes = $product->getAttributesResume(
                    (int)Context::getContext()->language->id
                );
                foreach ($attr_resumes as $attr_resume) {
                    $store_stock_obj = self::getStoreStockObjectByIdProduct(
                        (int)$product->id,
                        (int)$attr_resume['id_product_attribute'],
                        (int)Context::getContext()->shop->id
                    );
                    if (!Validate::isLoadedObject($store_stock_obj)) {
                        $store_stock_obj = new self();
                        $store_stock_obj->id_store = (int)Configuration::get('EVERPSCLICKANDCOLLECT_DEFAULT_STORE');
                        $store_stock_obj->id_product = (int)$product->id;
                        $store_stock_obj->id_product_attribute = (int)$attr_resume['id_product_attribute'];
                        $store_stock_obj->id_shop = (int)Context::getContext()->shop->id;
                        $store_stock_obj->qty = 0;
                        $store_stock_obj->save();
                    }
                    $store_stock_obj->store_name = self::getStoreName(
                        (int)$store_stock_obj->id_store,
                        (int)Context::getContext()->language->id
                    );
                    $store_stock_obj->product_name = $product->name;
                    // Get attribute name
                    $store_stock_obj->attribute_designation = Db::getInstance()->getValue(
                        'SELECT al.`name`
                        FROM `'._DB_PREFIX_.'product_attribute` pa
                        LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac
                        ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
                        LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (pac.`id_attribute` = al.`id_attribute`)
                        WHERE pa.`id_product` = '.(int)$product->id.'
                        AND al.`id_lang` = '.(int)Context::getContext()->language->id.'
                        AND pa.`id_product_attribute` = '.(int)$attr_resume['id_product_attribute']
                    );
                    // Get attribute reference
                    $sql = new DbQuery();
                    $sql->select(
                        'reference'
                    );
                    $sql->from(
                        'product_attribute'
                    );
                    $sql->where(
                        'id_product = '.(int)$product->id
                    );
                    $sql->where(
                        'id_product_attribute = '.(int)$attr_resume['id_product_attribute']
                    );
                    $store_stock_obj->reference = (string)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                    $all_stock[] = $store_stock_obj;
                }
            } else {
                $store_stock_obj = self::getStoreStockObjectByIdProduct(
                    (int)$product->id,
                    0,
                    (int)Context::getContext()->shop->id
                );
                if (!Validate::isLoadedObject($store_stock_obj)) {
                    $store_stock_obj = new self();
                    $store_stock_obj->id_store = (int)Configuration::get('EVERPSCLICKANDCOLLECT_DEFAULT_STORE');
                    $store_stock_obj->id_product = (int)$product->id;
                    $store_stock_obj->id_product_attribute = 0;
                    $store_stock_obj->id_shop = (int)Context::getContext()->shop->id;
                    $store_stock_obj->qty = 0;
                    $store_stock_obj->save();
                }
                $store_stock_obj->store_name = self::getStoreName(
                    (int)$store_stock_obj->id_store,
                    (int)Context::getContext()->language->id
                );
                $store_stock_obj->product_name = $product->name;
                $store_stock_obj->attribute_designation = '';
                $store_stock_obj->reference = $product->reference;
                $all_stock[] = $store_stock_obj;
            }
        }
        return $all_stock;
    }

    /**
     * Drop all stock from store
     *
     * @param int $id_store
     *
     * @return bool
     */
    public static function dropStoreStock($id_store)
    {
        // Rabbit triggers hook before
        // (\_/)
        // ( •_•)
        // / >
        Hook::exec(
            'actionDropStockBefore',
            [
                'id_store' => (int)$id_store,
            ]
        );

        $where = 'id_store = '.(int)$id_store;
        $delete = Db::getInstance()->delete(
            'everpsclickandcollect_store_stock',
            $where
        );

        // Cute cat triggers hook after
        //   ^~^  ,
        //  ('Y') )
        //  /   \/
        // (\|||/)
        Hook::exec(
            'actionDropStockAfter',
            [
                'id_store' => (int)$id_store,
            ]
        );
        return $delete;
    }

    /**
     * Drop all product stock
     *
     * @param int $id_product
     *
     * @return bool
     */
    public static function dropProductStock($id_product)
    {
        // |￣￣￣￣￣￣￣￣￣ |
        // |      RABBIT      |
        // | TRIGGERING HOOK! |
        // |__________________|
        // (\__/) ||
        // (•ㅅ•) ||
        // / 　 づ"
        Hook::exec(
            'actionDropStockBefore',
            [
                'id_product' => (int)$id_product,
            ]
        );

        $where = 'id_product = '.(int)$id_product;
        $delete = Db::getInstance()->delete(
            'everpsclickandcollect_store_stock',
            $where
        );

        //     (• _ •)   ←← Mandalorian
        // 　＿ノ ヽ ノ＼  __
        //  /　`/ ⌒Ｙ⌒ Ｙ　ヽ
        // ( 　(三ヽ人　 /　　 |
        // |　ﾉ⌒＼ ￣￣ヽ　 ノ
        // ヽ＿＿＿＞､＿＿_／
        // 　　 ｜( 王 ﾉ〈
        // 　　 /ﾐ`ー―彡\  (•◡•)  ←← Baby Yoda
        //
        // ↓↓ And the hook drop after ↓↓
        Hook::exec(
            'actionDropStockAfter',
            [
                'id_product' => (int)$id_product,
            ]
        );
        return $delete;
    }

    public static function importStoreStock($line_datas, $id_shop)
    {
        $id_store = (int)$line_datas[0];
        $id_product = (int)$line_datas[1];
        $id_product_attribute = (int)$line_datas[2];
        $qty = (int)$line_datas[7];
        if (!Validate::isUnsignedId($id_store)
            || !Validate::isUnsignedId($id_product)
            || !Validate::isInt($qty)
        ) {
            return false;
        }
        // D'oh ! Homer sets quantity, please give him a donut !
        // ...___.._____
        // ....‘/,-Y”.............“~-.
        // ..l.Y.......................^.
        // ./\............................_\_
        // i.................... ___/“....“\
        // |.................../“....“\ .....o !
        // l..................].......o !__../
        // .\..._..._.........\..___./...... “~\
        // ..X...\/...\.....................___./
        // .(. \.___......_.....--~~“.......~`-.
        // ....`.Z,--........./.................\
        // .......\__....(......../..........______)
        // ...........\.........l......../-----~~”/
        // ............Y.......\................/
        // ............|........“x______.^
        // ............|.............\
        // ............j...............Y
        $donut = (bool)self::setQuantity(
            (int)$id_store,
            (int)$id_product,
            (int)$id_product_attribute,
            (int)$qty,
            (int)$id_shop
        );
        // Mmmmmmh... Donut....
        return $donut;
    }
    public static function isCartAvailableForStore(Cart $cart, $id_store)
    {
        $cartproducts = $cart->getProducts();
        foreach ($cartproducts as $cartproduct) {
            $stock = self::getStoreStockAvailableByProductId(
                (int)$id_store,
                (int)$cartproduct['id_product'],
                (int)$cartproduct['id_product_attribute'],
                (int)Context::getContext()->shop->id
            );
            if ((int)$stock < 0
                || (int)$cartproduct['cart_quantity'] > (int)$stock
            ) {
                return false;
            }
        }
        return true;
    }
}
