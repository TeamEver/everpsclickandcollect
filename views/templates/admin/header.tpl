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
<div class="panel">
    <div class="panel-body">
        <div class="col-xs-12 col-lg-6">
			<h3><i class="icon icon-smile"></i> {l s='Ever PS Click & Collect' mod='everpsclickandcollect'}</h3>
			<img id="everlogo" src="{$everpsclickandcollect_dir|escape:'htmlall':'UTF-8'}logo.png" style="max-width: 120px;">
			<p>
				<strong>{l s='Welcome to Ever PS Click & Collect !' mod='everpsclickandcollect'}</strong><br />
				{l s='Thanks for using Team Ever\'s module' mod='everpsclickandcollect'}</a><br />
			</p>
        </div>
        <div class="col-xs-12 col-lg-6">
            <p class="alert alert-warning">
                {l s='This module is free and will always be ! You can support our free modules by making a donation by clicking the button below' mod='everpsclickandcollect'}
            </p>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="3LE8ABFYJKP98" />
            <input type="image" src="https://www.team-ever.com/wp-content/uploads/2019/06/appel_a_dons-1.jpg" border="0" name="submit" title="{l s='This module is free and will always be ! You can support our free modules by making a donation by clicking the button below' mod='everpsblog'}" alt="{l s='This module is free and will always be ! You can support our free modules by making a donation by clicking the button below' mod='everpsblog'}" />
            <img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
            </form>
        </div>
    </div>
</div>