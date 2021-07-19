{*
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
*}
<div class="alert alert-info col-12" id="everclickncollect_id" data-evercncurl="{$ajax_url|escape:'htmlall':'UTF-8'}" data-evercnccarrier="{$everclickncollect_id|escape:'htmlall':'UTF-8'}">
	{if isset($custom_msg) && $custom_msg}
	<div id="everclickncollect_msg" class="text-center">
		{$custom_msg nofilter}
	</div>
	{/if}
	<table id="store_depot_list" class="table table-striped table-bordered table-labeled table-responsive">
		<th class="text-center">{l s='Store' mod='everpsclickandcollect'}</th>
		{if isset($ask_date) && $ask_date}
		<th class="text-center">{l s='Choose date' mod='everpsclickandcollect'}</th>
		{/if}
		<th class="text-center">{l s='Select' mod='everpsclickandcollect'}</th>
		{foreach from=$stores item=store}
		<tr class="carrier_depot_item">
			<td class="text-center">
				{if isset($show_store_img) && $show_store_img}
				<img src="{$store.image.bySize.stores_default.url|escape:'htmlall':'UTF-8'}" alt="{$store.image.legend|escape:'htmlall':'UTF-8'}" title="{$store.image.legend nofilter}"><br>
				{$store.name|escape:'htmlall':'UTF-8'}<br>
				{/if}
				{$store.address.formatted nofilter}
			</td>
			{if isset($ask_date) && $ask_date}
			<td class="text-center">
				<select class="store_date store_date_{$store.id|escape:'htmlall':'UTF-8'}" data-idstore="{$store.id|escape:'htmlall':'UTF-8'}">
				{foreach $store.business_hours as $day}
				{if $day.hours && !empty($day.hours)}
					<option value="{$day.day|escape:'htmlall':'UTF-8'}" {if $everclickncollect_date == $day.day}selected{/if}>
						{$day.day|escape:'htmlall':'UTF-8'}
						({foreach $day.hours as $h}
						{$h|escape:'htmlall':'UTF-8'}
						{/foreach})
					</option>
				{/if}
				{/foreach}
				</select>
			</td>
			{/if}
			<td class="text-center">
				<input type="radio" name="everpsclickandcollect" id="{$store.id|escape:'htmlall':'UTF-8'}" value="{$store.id|escape:'htmlall':'UTF-8'}" {if isset($store.selected) && $store.selected}checked{/if} {if isset($only_one) && $only_one}disabled{/if}>
			</td>
		</tr>
		{/foreach}
	</table>
</div>