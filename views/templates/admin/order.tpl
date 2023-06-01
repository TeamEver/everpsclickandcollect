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
<div class="card col-lg-12 p-3">
    <h3 class="bootstrap cardheader everpsclickandcollect">
        {l s='Store information for click & collect' mod='everpsclickandcollect'}
    </h3>
    <div class="bootstrap cardbody everpsclickandcollect">
        <div class="panel-heading">

<div class="panel everheader">
    <div class="panel-body">
        <div class="col-md-12">
            <div class="table-responsive">
                <table id="everpsclickandcollect" class="display responsive nowrap dataTable no-footer dtr-inline collapsed table">
                    <thead>
                        <tr class="center small grey bold center">
                            <th class="text-center">{l s='Store name' mod='everpsclickandcollect'}</th>
                            <th class="text-center">{l s='Store address' mod='everpsclickandcollect'}</th>
                            {if isset($clickncollect.delivery_date) && $clickncollect.delivery_date}
                            <th class="text-center">{l s='Selected date' mod='everpsclickandcollect'}</th>
                            {/if}
                            <th class="text-center">{l s='Opening hours' mod='everpsclickandcollect'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="option_name center small text-center">
                                {$store.name|escape:'htmlall':'UTF-8'}<br>
                                <img src="{$store.image.bySize.stores_default.url|escape:'htmlall':'UTF-8'}" alt="{$store.image.legend|escape:'htmlall':'UTF-8'}" title="{$store.image.legend|escape:'htmlall':'UTF-8'}">
                            </td>
                            <td class="option_value center small text-center">
                                {$store.address.formatted nofilter}
                            </td>
                            {if isset($clickncollect.delivery_date) && $clickncollect.delivery_date}
                            <td class="option_value center small text-center">
                            	{l s='Next' mod='everpsclickandcollect'} {$clickncollect.delivery_date|escape:'htmlall':'UTF-8'}
                            </td>
                            {/if}
                            <td class="option_value center small text-center">
                            {foreach $store.business_hours as $day}
                                {if $day.day == $clickncollect.delivery_date}
                                    <p> 
                                        {$day.day|escape:'htmlall':'UTF-8'}
                                        ({foreach $day.hours as $h}
                                        {$h|escape:'htmlall':'UTF-8'}
                                        {/foreach})
                                    </p>
                                {/if}
                            {/foreach}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>
