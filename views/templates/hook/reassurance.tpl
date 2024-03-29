{*
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
*}
{if isset($shipping_stores) && $shipping_stores}
<div class="card card-block">
	{if isset($only_one) && $only_one}
	<p class="text-center">{l s='This product is available for click and collect on this store' mod='everpsclickandcollect'}</p>
	<select class="text-center select-center">
	{foreach from=$shipping_stores item=store}
		<option>
			{if isset($store.attribute_designation) && $store.attribute_designation}{$store.attribute_designation} : {/if}
			{if isset($manage_stock) && $manage_stock} {$store.qty} {l s='in stock' mod='everpsclickandcollect'},{/if}
			{l s='available at' mod='everpsclickandcollect'} {$store.name|escape:'htmlall':'UTF-8'} - {$store.address.city nofilter}
		</option>
	{/foreach}
	</select>
	{else}
	<p class="text-center">{l s='This product is available for click and collect on these stores' mod='everpsclickandcollect'}</p>
	<select class="text-center select-center">
	{foreach from=$shipping_stores item=store}
		<option>
			{if isset($store.attribute_designation) && $store.attribute_designation}{$store.attribute_designation} : {/if}
			{if isset($manage_stock) && $manage_stock} {$store.qty} {l s='in stock' mod='everpsclickandcollect'},{/if}
			{l s='available at' mod='everpsclickandcollect'} {$store.name|escape:'htmlall':'UTF-8'} - {$store.address.city nofilter}
		</option>
	{/foreach}
	</select>
	{/if}
</div>
{/if}