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
<div class="everpsclickandcollect-tab">
	{if isset($only_one) && $only_one}
	<p class="h4 text-center">{l s='This product is available for click and collect on these stores' mod='everpsclickandcollect'}</p>
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
	<p class="h4 text-center">{l s='This product is available for click and collect on this store' mod='everpsclickandcollect'}</p>
	{/if}
	<table id="store_depot_list" class="table table-striped table-bordered table-labeled">
		<th class="text-center">{l s='Store' mod='everpsclickandcollect'}</th>
		{if isset($has_combinations) && $has_combinations}
		<th class="text-center">{l s='Combination' mod='everpsclickandcollect'}</th>
		{/if}
		{if isset($manage_stock) && $manage_stock}
		<th class="text-center">{l s='Quantity available' mod='everpsclickandcollect'}</th>
		{/if}
		{foreach from=$shipping_stores item=store}
		<tr class="carrier_depot_item">
			<td class="text-center">
				{* <img src="{$store.image.bySize.stores_default.url|escape:'htmlall':'UTF-8'}" alt="{$store.image.legend|escape:'htmlall':'UTF-8'}" title="{$store.image.legend nofilter}"><br>
				{$store.name|escape:'htmlall':'UTF-8'}<br> *}
				{$store.address.formatted nofilter}
				{* {foreach $store.business_hours as $day}
					<p>
						{$day.day|escape:'htmlall':'UTF-8'}
						{foreach $day.hours as $h}
						{$h|escape:'htmlall':'UTF-8'}
						{/foreach}
					</p>
				{/foreach} *}
			</td>
			{if isset($store.attribute_designation) && $store.attribute_designation}
			<td class="text-center {$store.id_product_attribute}">
				{$store.attribute_designation}
			</td>
			{/if}
			{if isset($manage_stock) && $manage_stock}
			<td class="text-center">
				{$store.qty}
			</td>
			{/if}
		</tr>
		{/foreach}
	</table>
</div>
{else}
	<p class="text-center">{l s='Unfortunately this product is not currently available in Click And Collect'}</p>
{/if}