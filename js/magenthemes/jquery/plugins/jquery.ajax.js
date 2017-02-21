/**
 *
 * ------------------------------------------------------------------------------
 * @category Mato
 * @package Mato Framework
 * ------------------------------------------------------------------------------
 * @copyright    Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license      GNU General Public License version 2 or later;
 * @author       ZooExtension.com
 * @email        magento@cleversoft.co
 * ------------------------------------------------------------------------------
 *
 */
$mt(function($){
    $('.success button.close').live('click', function() {
        $(this).parent().fadeOut('slow', function() {
            $(this).remove();
        });
    });
});
function showBox(message,wait){
    $mt('#notification').html('<div class="success" style="display: none;">' +
        '<i class="fa fa-check"></i>' +
        '' + message + '' +
        '<button class="close"><i class="fa fa-times-circle"></i></button></div>');
    $mt('.success').fadeIn('slow');
    if(wait){
        $mt('.success').addClass('wait-loading');
        $mt('.success .fa-check').hide();
        $mt('.success .close').hide();
    }else{
        $mt('.success').removeClass('wait-loading');
        $mt('.success .fa-check').show();
        $mt('.success .close').show();
    }
    $mt('.success').fadeIn('slow');
    if(!wait){
        setTimeout(function() {
            $mt('.success').delay(500).fadeOut(1000);
        }, 4000);
    }
}

function addtowishlist(el)
{
	var url = jQuery(el).attr('href');
    var checkUpdateUrl = (url.indexOf('wishlist/index/updateItemOptions') > -1);
    if(checkUpdateUrl){
        url = url.replace("wishlist/index/updateItemOptions","ajaxcart/index/updateWishlist");
    }else{
        url = url.replace("wishlist/index/add","ajaxcart/index/wishlist");
    }
    data = '&isAjax=1';
    showBox(datatext.load,true);
	try {
		jQuery.ajax( {
			url : url,
			dataType : 'jsonp',
			data: data,
			type: 'post',
			success : function(data) {
				showBox(data.message,false);
				if (data.status != 'ERROR'){
					jQuery('.header .links').replaceWith(data.toplink);
				}
				if (data.status != 'ERROR' && jQuery('.block-wishlist').length){
					jQuery('.block-wishlist').replaceWith(data.sidebar);
				}
			}
		});
	} catch (e) {
	} 
} 
function removetowishlist(el)
{
	var url = jQuery(el).attr('href');
	data = '&isAjax=1';
	url = url.replace("wishlist/index","ajaxcart/index");
    showBox(datatext.load,true);
	try {
		jQuery.ajax( {
			url : url,
			dataType : 'jsonp',
			data: data,
			type: 'post',
			success : function(data) {
				showBox(data.message,false);
				if (data.status != 'ERROR'){
					jQuery('.header .links').replaceWith(data.toplink);
				}
				if (data.status != 'ERROR' && jQuery('.block-wishlist').length){ 
					jQuery('.block-wishlist').replaceWith(data.sidebar);
					if(data.sidebar==''){
						jQuery('.block-wishlist').remove();
					}
				}
			}
		});
	} catch (e) {
	} 
} 
function addtocompare(el)
{ 
	var url = jQuery(el).attr('href');
	data = '&isAjax=1';
	url = url.replace("catalog/product_compare/add","ajaxcart/index/compare");
    showBox(datatext.load,true);
	try {
		jQuery.ajax( {
			url : url,
			dataType : 'jsonp',
			data: data,
			type: 'post',
			success : function(data) {
				showBox(data.message,false);
				if (data.status != 'ERROR' && jQuery('.block-compare').length) {  
					jQuery('.block-compare').replaceWith(data.output);
				}
				if (data.status != 'ERROR'){
					jQuery('.block-top-compare').html();
					jQuery('.block-top-compare').html(data.topcompare);
				}
			}
		});
	} catch (e) {
	} 
}  
function removecompare(el)
{ 
	var url = jQuery(el).attr('href');
	data = '&isAjax=1';
	url = url.replace("catalog/product_compare/remove","ajaxcart/index/rmcompare");
    showBox(datatext.load,true);
	try {
		jQuery.ajax( {
			url : url,
			dataType : 'jsonp',
			data: data,
			type: 'post',
			success : function(data) {
				showBox(data.message,false);
				if (data.status != 'ERROR' && jQuery('.block-compare').length) {  
					jQuery('.block-compare').replaceWith(data.output);
				}
				if (data.status != 'ERROR'){
					jQuery('.block-top-compare').html();
					jQuery('.block-top-compare').html(data.topcompare);
				}
			}
		});
	} catch (e) {
	} 
} 
function clearallcompare(el)
{ 
	var url = jQuery(el).attr('href');
	data = '&isAjax=1';
	url = url.replace("catalog/product_compare/clear","ajaxcart/index/clearall");
    showBox(datatext.load,true);
	try {
		jQuery.ajax( {
			url : url,
			dataType : 'jsonp',
			data: data,
			type: 'post',
			success : function(data) {
				showBox(data.message,false);
				if (data.status != 'ERROR' && jQuery('.block-compare').length) {  
					jQuery('.block-compare').replaceWith(data.output);
				}
				if (data.status != 'ERROR'){
					jQuery('.block-top-compare').html();
					jQuery('.block-top-compare').html(data.topcompare);
				}
			}
		});
	} catch (e) {
	} 
}