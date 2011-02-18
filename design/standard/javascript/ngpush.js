jQuery(document).ready(function(){

//

jQuery("#ngpush-list textarea[class^='maxlength']").keyup(function(){maxlength(this);});
jQuery("#ngpush-list textarea[class^='maxlength']").each(function(){maxlength(this);});


var bitly_login = jQuery('#ngpush-bitly-login').val();
var bitly_apikey = jQuery('#ngpush-bitly-apikey').val();
var node_url = jQuery('#ngpush-node-url-full').val();

//jQuery.getJSON('http://api.bit.ly/v3/shorten?login=' + bitly_login + '&apiKey=' + bitly_apikey + '&uri=' + node_url + '&format=json',
//	function(data) {
//		console.log(data);
//	});

jQuery.ajax({
	type: "GET",
	url: 'http://api.bit.ly/v3/shorten?login=' + ngpush_bitly_login + '&apiKey=' + ngpush_bitly_apikey + '&uri=' + ngpush_node_url_full + '&format=json',
	dataType: "jsonp",
	jsonpCallback: 'shorten_bitly',
	success: function(data){},
	complete: function(xhr, status) {}
});

jQuery('input.insert-link').click(function(){
	var url = (jQuery(this).attr('name') == 'short' ? ngpush_node_url_short : ngpush_node_url_full);
	var textarea = jQuery(this).parents('.ngpush-block').find('textarea').get(0);
	var startPos = textarea.selectionStart;
	var endPos = textarea.selectionEnd;
	textarea.value = textarea.value.substring(0, startPos) + url + textarea.value.substring(endPos, textarea.value.length);
	textarea.selectionStart = textarea.selectionEnd = startPos + url.length;
	maxlength(textarea);
});

jQuery('#ngpush-list form input[type=reset]').click(function(e){
	e.preventDefault();
	jQuery(this).parents('form').get(0).reset();
	jQuery(this).parents('form').find('textarea.maxlength').each(function(){maxlength(jQuery(this).get(0));});
});

jQuery('.ngpush-block').each(function(){
	var id = jQuery(this).attr('id');
	jQuery(this).find('th').click(function(){
		jQuery('#' + id).find('.account-body').toggle().toggleClass('active');
	});
});

jQuery('#ngpush-list .block input.push-all').click(function(){
	jQuery('#ngpush-list .account-body.active').each(function(){
		var account_id = jQuery(this).find('input.ngpush-account-id').val();
		var account_type = jQuery(this).find('input.ngpush-account-type').val();
		ngpush_process_account(account_type, account_id);
	});
});

jQuery('#ngpush-list .account-body form input.push').click(function(){
	var account_id = jQuery(this).parents('form').find('input.ngpush-account-id').val();
	var account_type = jQuery(this).parents('form').find('input.ngpush-account-type').val();
	ngpush_process_account(account_type, account_id);
});

//

});

function ngpush_process_account(account_type, account_id) {	
	switch (account_type) {
		case 'twitter':
			
			if (!ngpush_check_status(account_id)) return;
			if (!ngpush_check_maxlength(account_id)) return;
			
			ngpush_set_status_active(account_id);
			
			var tw_status = jQuery('#ngpush-' + account_id).find('textarea[name="tw_status"]').val();
			jQuery.ez('ezjscngpush::push',
				{nodeID: ngpush_node_id, accountID: account_id, tw_status: tw_status},
				function(data) {ngpush_process_response(data, account_id);});
			
			break;
		case 'facebook_feed':
			
			if (!ngpush_check_status(account_id)) return;
			ngpush_set_status_active(account_id);
			
			var facebook_appid			= jQuery('#ngpush-' + account_id).find('input.ngpush-facebook-appid').val();
			var facebook_entitytype	= jQuery('#ngpush-' + account_id).find('input.ngpush-facebook-entitytype').val();

			var fb_name					= jQuery('#ngpush-' + account_id).find('input[type="checkbox"][name="fb_name"]:checked').parent().next('input[type="text"][name="fb_name"]').val();
			var fb_description	= jQuery('#ngpush-' + account_id).find('input[type="checkbox"][name="fb_description"]:checked').parent().next('textarea[name="fb_description"]').val();
			var fb_message			= jQuery('#ngpush-' + account_id).find('input[type="checkbox"][name="fb_message"]:checked').parent().next('textarea[name="fb_message"]').val();
			var fb_link					= jQuery('#ngpush-' + account_id).find('input[type="checkbox"][name="fb_link"]:checked').parent().next('input[type="text"][name="fb_link"]').val();
			var fb_picture			= jQuery('#ngpush-' + account_id).find('input[type="checkbox"][name="fb_picture"]:checked').parent().next('input[type="hidden"][name="fb_picture"]').val();
			jQuery.ez('ezjscngpush::push',
				{nodeID: ngpush_node_id, accountID: account_id, fb_description: fb_description, fb_message: fb_message, fb_name: fb_name, fb_link: fb_link, fb_picture: fb_picture},
				function(data) {ngpush_process_response(data, account_id);});
			
			break;
		case 'facebook_event':
			
			var fb_name					= jQuery(this).find('input[type="checkbox"][name="fb_name"]:checked').parent().next('input[type="text"][name="fb_name"]').val();
			var fb_start_time		= jQuery(this).find('input[type="checkbox"][name="fb_start_time"]:checked').parent().next('input[type="text"][name="fb_start_time"]').val();
			var fb_end_time			= jQuery(this).find('input[type="checkbox"][name="fb_end_time"]:checked').parent().next('input[type="text"][name="fb_end_time"]').val();
			
			jQuery('#ngpush-' + account_id).find('.status .indicator div').removeClass().addClass('indicator-active');
			
			jQuery.ez( 'ezjscngpush::push', {nodeID: ngpush_node_id, accountID: account_id, fb_name: fb_name, fb_start_time: fb_start_time, fb_end_time: fb_end_time}, function(data) {
				
				var fb = eval('(' + data.content + ')');
				if (fb.error) {
					
					jQuery('#ngpush-' + account_id).find('.status .message').html(fb.error.type + ': ' + fb.error.message);
					jQuery('#ngpush-' + account_id).find('.status .control').html('<a href="#" onclick="var mywindow = window.open(\'https://graph.facebook.com/oauth/authorize?redirect_uri=http://demo43.netgen.biz&client_id=' + facebook_appid + '&scope=' + (facebook_entitytype == 'page' ? 'manage_pages%2C' : '') + 'publish_stream\',\'mywindow\',\'location=0,status=0,scrollbars=0,width=990,height=640\');mywindow.moveTo(0,0); return false;">authorize</a>');
					
					jQuery('#ngpush-' + account_id).find('.status .message').html(fb.error.message);
					jQuery('#ngpush-' + account_id).find('.status .indicator div').removeClass().addClass('indicator-error');
					
				}
				
				else {
					
					jQuery('#ngpush-' + account_id).find('.status .message').html(data.content.message);
					jQuery('#ngpush-' + account_id).find('.status .indicator div').removeClass().addClass('indicator-ok');
					
				}
				
			});
			
			break;
		case 'ezrest':
			
			if (!ngpush_check_status(account_id)) return;
			ngpush_set_status_active(account_id);
	
			var params = '';
			jQuery('#ngpush-' + account_id).find('input[type="checkbox"][name^="param_"]:checked').each(function(){
				var param_name = jQuery(this).attr('name');
				var param_value = jQuery(this).parent().next('[name="params[' + param_name + ']"]').val();
				params += (params != '' ? '::' : '') + param_name.substring(6) + '%%' + param_value;
			});
			jQuery.ez('ezjscngpush::push',
				{nodeID: ngpush_node_id, accountID: account_id, params: params},
				function(data) {ngpush_process_response(data, account_id);});
			
			break;
		default:
			break;
	}
}

function ngpush_process_response(data, account_id) {
	//jscore error
	if (data.error_text) {
		jQuery('#ngpush-' + account_id).find('.account-body').data('status', 'error');
		jQuery('#ngpush-' + account_id).find('.status .indicator div').removeClass().addClass('indicator-error');
		jQuery('#ngpush-' + account_id).find('.status .message').html(data.error_text);
	}
	else {
		if (data.content.status == 'success') {
			jQuery('#ngpush-' + account_id).find('.account-body').data('status', 'success');
			jQuery('#ngpush-' + account_id).find('.status .indicator div').removeClass().addClass('indicator-ok');
		}
		else if (data.content.status == 'error') {
			jQuery('#ngpush-' + account_id).find('.account-body').data('status', 'error');
			jQuery('#ngpush-' + account_id).find('.status .indicator div').removeClass().addClass('indicator-error');
		}
		else {
			jQuery('#ngpush-' + account_id).find('.account-body').data('status', 'error');
			jQuery('#ngpush-' + account_id).find('.status .indicator div').removeClass().addClass('indicator-error');
		}
		jQuery('#ngpush-' + account_id).find('.status .message, .status .control').empty();
		for (var i = 0; i < data.content.messages.length; i++) {
			jQuery('#ngpush-' + account_id).find('.status .message').append(data.content.messages[i]);
		}
		if (data.content.RequestPermissionsUrl) {
			jQuery('#ngpush-' + account_id).find('.status .control').html(
				'<a href="#" onclick="var mywindow = window.open(\'' + data.content.RequestPermissionsUrl + '\',\'mywindow\',\'location=0,status=0,menubar=0,toolbar=0,scrollbars=0,width=800,height=420,top=50,left=50\'); return false;">Request access permissions</a>');
		}
	}
}

function ngpush_check_status(account_id) {	
	switch (jQuery('#ngpush-' + account_id).find('.account-body').data('status')) {
		case 'in-progress':
			return false;
		case 'success':
			jQuery('#ngpush-' + account_id).find('.status .message').html(ngpush_text_processed);
			return false;
		default:
			return true;
	}
}

function ngpush_check_maxlength(account_id) {	
	var error = false;
	jQuery('#ngpush-' + account_id).find('textarea.maxlength').each(function(){
		var max = parseInt(jQuery(this).attr('class').split('-')[1]);
		if (jQuery(this).val().length > max) {
			jQuery('#ngpush-' + account_id).find('.status .message').html(ngpush_text_maxlength_error);
			jQuery('#ngpush-' + account_id).find('.status .indicator div').removeClass().addClass('indicator-error');
			error = true;
		}
	});
	if (error) return false;
	return true;
}

function ngpush_set_status_active(account_id) {	
	jQuery('#ngpush-' + account_id).find('.account-body').data('status', 'in-progress');
	jQuery('#ngpush-' + account_id).find('.status .message').html(ngpush_text_requesting);
	jQuery('#ngpush-' + account_id).find('.status .control').empty();
	jQuery('#ngpush-' + account_id).find('.status .indicator div').removeClass().addClass('indicator-active');
}

function maxlength(el) {
	var max = parseInt(jQuery(el).attr('class').split('-')[1]);
	jQuery(el).parent().find('.maxlength span').html(max - jQuery(el).val().length);
}

function shorten_bitly(bitly_response) {
	//var bitly_response_json = json_parse(bitly_response);
	if (bitly_response.status_code == 200) {
		jQuery('input.insert-link[name="short"]').removeAttr('disabled');
		ngpush_node_url_short = bitly_response.data.url;
	}
	else {
	}
}
