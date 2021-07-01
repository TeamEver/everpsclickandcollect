{*
* Project : everpsclickandcollect
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

<!-- Module Ever Listing Text -->
<div class="panel">
    <h3><i class="icon icon-smile"></i> {l s='Store stock management' mod='everpsclickandcollect'}</h3>
</div>
<div id="everpsclickandcollect-form" class="panel">
    {foreach from=$shipping_stores item=shipping_store}
        <div class="form-group">
            <label for="everpsclickandcollect_qty_{$shipping_store.id|escape:'htmlall':'UTF-8'}">
            	{l s='Stock available ' mod='everpsclickandcollect'} 
            	{if isset($shipping_store.has_combinations) && $shipping_store.has_combinations}
            	{l s='for combination : ' mod='everpsclickandcollect'} <strong>{$shipping_store.attribute_designation|escape:'htmlall':'UTF-8'}</strong>
            	{/if}
            	{l s='on store ' mod='everpsclickandcollect'} 
            	<strong>{$shipping_store.name|escape:'htmlall':'UTF-8'}</strong>
            </label>
        	{if isset($shipping_store.has_combinations) && $shipping_store.has_combinations}
        	<input type="hidden" class="form-control" id="everpsclickandcollect_idattribute_{$shipping_store.id|escape:'htmlall':'UTF-8'}" name="everpsclickandcollect_idattribute_{$shipping_store.id_product_attribute|escape:'htmlall':'UTF-8'}" value="">
            <input type="number" class="form-control" id="everpsclickandcollect_qty_{$shipping_store.id|escape:'htmlall':'UTF-8'}{$shipping_store.id_product_attribute|escape:'htmlall':'UTF-8'}" name="everpsclickandcollect_qty_{$shipping_store.id|escape:'htmlall':'UTF-8'}{$shipping_store.id_product_attribute|escape:'htmlall':'UTF-8'}" aria-describedby="emailHelp" placeholder="{l s='Stock available' mod='everpsclickandcollect'}" value="{$shipping_store.qty|escape:'htmlall':'UTF-8'}">
        	{else}
            <input type="number" class="form-control" id="everpsclickandcollect_qty_{$shipping_store.id|escape:'htmlall':'UTF-8'}" name="everpsclickandcollect_qty_{$shipping_store.id|escape:'htmlall':'UTF-8'}" aria-describedby="emailHelp" placeholder="{l s='Stock available' mod='everpsclickandcollect'}" value="{$shipping_store.qty|escape:'htmlall':'UTF-8'}">
        	{/if}
            <small id="emailHelp" class="form-text text-muted">{l s='Type 0 or leave empty to disable this click and collect method' mod='everpsclickandcollect'}</small>
        </div>
    {/foreach}
</div>
<!-- /Module Ever Listing Text -->
