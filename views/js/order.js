/**
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
 */
$(document).ready(function() {
	var ajax_url = $('#everclickncollect_id').data('evercncurl');
	var everclickncollect_id = $('#everclickncollect_id').data('evercnccarrier');	
	// On select date, check input radio to trigger ajax process
	if ($('#everclickncollect_id .store_date' ).length) {
		$('#everclickncollect_id .store_date' ).change(function (){
			var current_store = $(this).data('idstore');
			$('#everclickncollect_id #store_depot_list input[type=radio]#' + current_store).click();
			
		});
	}
	// TODO
	// if (!$('#everclickncollect_id #store_depot_list input[type=radio]').length) {
	// 	$('#delivery_option_' + everclickncollect_id).parent().parent().parent().remove();
	// 	$('#everclickncollect_id').remove();
	// 	var next_method = $('.delivery-options .delivery-option').first().find('custom-radio input').hide();
	// }
	$('#everclickncollect_id #store_depot_list input[type=radio]').click(function(e){
		if ($('#everclickncollect_id .store_date_' + $(this).val() ).length) {
			var selected_date = $('#everclickncollect_id .store_date_' + $(this).val() ).val();
		} else {
			var selected_date = '';
		}
	    $.ajax({
	        type: 'POST',
	        url: ajax_url,
	        cache: false,
	        dataType: 'JSON',
	        data: {
	            action: 'SaveShippingStore',
	            ajax: true,
	            everclickncollect_id : $(this).val(),
	            everclickncollect_date : selected_date,
	        },
	        success: function(data) {
	            if (data.return) {
	            	// prestashop.emit('updateCart', {reason: {linkAction: 'refresh'}, resp: {}});
	            }
	        },
	        error: function(jqXHR, textStatus, errorThrown) {
	            console.log(textStatus + ' ' + errorThrown);
	        }
	    });		
	})
});