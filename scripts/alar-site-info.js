/**
 * Th is is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * See http://www.gnu.org/licenses/gpl-2.0.txt.
 *
 * © 2018, John Alarcon
 * https://twitter.com/realJohnAlarcon
 * https://www.linkedin.com/in/alarconjohn
 * 
 */
jQuery(document).ready(function($) {
	$(function() {
		$(document).tooltip({
			position: {
				my: 'center bottom-20',
				at: 'center top',
				using: function(position, feedback) {
					$(this).css(position);
					$('<div>').addClass('arrow').appendTo(this);
				}
			}
		});
	});
	$('a.site-info').click(function(e){
		e.preventDefault();
	});		
	$("#alar-site-info-show-full-php-info").click(function() {
		$(".alar-site-info-full-php-info").toggle("fast", function() {});
	});
});