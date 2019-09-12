/**
 * Custom scripts needed for the colorpicker, image button selectors,
 * and navigation tabs.
 */

jQuery(document).ready(function($) {
	// Loads tabbed sections if they exist
	if ( $('.mp-nav-tab-wrapper').length > 0 ) {
		miniprogram_options_tabs();
	}
	// Tabs
	function miniprogram_options_tabs() {
		var $group = $('.miniprogram-group'),
			$navtabs = $('.mp-nav-tab-wrapper a'),
			is_active = '';
		// Hides all the .group sections to start
		$group.hide();
		// Find if a selected tab is saved in localStorage
		if ( typeof(localStorage) != 'undefined' ) {
			is_active = localStorage.getItem('is_actived');
		}
		// If active tab is saved and exists, load it's .group
		if ( is_active != '' && $(is_active).length ) {
			$(is_active).fadeIn();
			$(is_active + '-tab').addClass('mp-nav-tab-active');
		} else {
			$('.miniprogram-group:first').fadeIn();
			$('.mp-nav-tab-wrapper a:first').addClass('mp-nav-tab-active');
		}
		// Bind tabs clicks
		$navtabs.click(function(e) {
			e.preventDefault();
			// Remove active class from all tabs
			$navtabs.removeClass('mp-nav-tab-active');
			$(this).addClass('mp-nav-tab-active').blur();
			if (typeof(localStorage) != 'undefined' ) {
				localStorage.setItem('is_actived', $(this).attr('href') );
			}
			var selected = $(this).attr('href');
			$group.hide();
			$(selected).fadeIn();
		});
	}
	// 返回顶部与底部
	$("#goTop").click(function() {
		$("html, body").animate({ scrollTop: "0px" }, "500");
		return false
	});
	$("#down").click(function() {
		$("html, body").animate({ scrollTop: $('#wpfooter').offset().top }, "500");
		return false
	});
	// 媒体库调用
	$('.upload-button').click(function(e) {
		//console.log(e);
		var mediaUploader;
		e.preventDefault();
		//var upload = e.currentTarget.id;
		var upload = $(this).attr('id');
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}
		mediaUploader = wp.media({
			title: '选择图片',
			button: {
				text: '选择'
			},
			multiple: false
		});	
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			var value_id = '#' + upload.replace(/-btn/, "")
			$(value_id).attr({value:attachment.url});
		});
		mediaUploader.open();
	});
	
	// 广告设置
	if (jQuery('#ad_i_open:checked').val() !== undefined) {
		jQuery('#ad_i_type_select').show();
		jQuery('#ad_i_args_text').show();
		jQuery('#ad_i_platform_mu_check').show();
		if (jQuery('#ad_i_type').val() !== 'unit') {
        	jQuery('#ad_i_image_upload').show();
        }
	} else {
		jQuery('#ad_i_type_select').hide();
		jQuery('#ad_i_args_text').hide();
		jQuery('#ad_i_platform_mu_check').hide();
		if (jQuery('#ad_i_type').val() !== 'unit') {
        	jQuery('#ad_i_image_upload').hide();
        }
	}
	jQuery('#ad_i_open').click(function() {
		jQuery('#ad_i_type_select').fadeToggle(400);
		jQuery('#ad_i_args_text').fadeToggle(400);
		jQuery('#ad_i_platform_mu_check').fadeToggle(400);
		if (jQuery('#ad_i_type').val() !== 'unit') {
			jQuery('#ad_i_image_upload').fadeToggle(400);
		}
	});
	if (jQuery('#ad_t_open:checked').val() !== undefined) {
		jQuery('#ad_t_open_checkbox').show();
		jQuery('#ad_t_args_text').show();
		jQuery('#ad_t_platform_mu_check').show();
		if (jQuery('#ad_i_type').val() !== 'unit') {
        	jQuery('#ad_i_image_upload').show();
        }
	} else {
		jQuery('#ad_t_type_select').hide();
		jQuery('#ad_t_args_text').hide();
		jQuery('#ad_t_platform_mu_check').hide();
		if (jQuery('#ad_t_type').val() !== 'unit') {
        	jQuery('#ad_t_image_upload').hide();
        }
	}
	jQuery('#ad_t_open').click(function() {
		jQuery('#ad_t_type_select').fadeToggle(400);
		jQuery('#ad_t_args_text').fadeToggle(400);
		jQuery('#ad_t_platform_mu_check').fadeToggle(400);
		if (jQuery('#ad_t_type').val() !== 'unit') {
        	jQuery('#ad_t_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#ad_d_open:checked').val() !== undefined) {
		jQuery('#ad_d_type_select').show();
		jQuery('#ad_d_args_text').show();
		jQuery('#ad_d_platform_mu_check').show();
		if (jQuery('#ad_d_type').val() !== 'unit') {
        	jQuery('#ad_d_image_upload').show();
        }
	} else {
		jQuery('#ad_d_type_select').hide();
		jQuery('#ad_d_args_text').hide();
		jQuery('#ad_d_platform_mu_check').hide();
		if (jQuery('#ad_d_type').val() !== 'unit') {
        	jQuery('#ad_d_image_upload').hide();
        }
	}
	jQuery('#ad_d_open').click(function() {
		jQuery('#ad_d_type_select').fadeToggle(400);
		jQuery('#ad_d_args_text').fadeToggle(400);
		jQuery('#ad_d_platform_mu_check').fadeToggle(400);
		if (jQuery('#ad_d_type').val() !== 'unit') {
        	jQuery('#ad_d_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#ad_p_open:checked').val() !== undefined) {
		jQuery('#ad_p_type_select').show();
		jQuery('#ad_p_args_text').show();
		jQuery('#ad_p_platform_mu_check').show();
		if (jQuery('#ad_p_type').val() !== 'unit') {
        	jQuery('#ad_p_image_upload').show();
        }
	} else {
		jQuery('#ad_p_type_select').hide();
		jQuery('#ad_p_args_text').hide();
		jQuery('#ad_p_platform_mu_check').hide();
		if (jQuery('#ad_p_type').val() !== 'unit') {
        	jQuery('#ad_p_image_upload').hide();
        }
	}
	jQuery('#ad_p_open').click(function() {
		jQuery('#ad_p_type_select').fadeToggle(400);
		jQuery('#ad_p_args_text').fadeToggle(400);
		jQuery('#ad_p_platform_mu_check').fadeToggle(400);
		if (jQuery('#ad_p_type').val() !== 'unit') {
        	jQuery('#ad_p_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#qq_applets:checked').val() !== undefined) {
		jQuery('#qq_appid_text').show();
		jQuery('#qq_secret_text').show();
	} else {
		jQuery('#qq_appid_text').hide();
		jQuery('#qq_secret_text').hide();
	}
	jQuery('#qq_applets').click(function() {
		jQuery('#qq_appid_text').fadeToggle(400);
		jQuery('#qq_secret_text').fadeToggle(400);
	});
	if (jQuery('#bd_applets:checked').val() !== undefined) {
		jQuery('#bd_appkey_text').show();
		jQuery('#bd_secret_text').show();
	} else {
		jQuery('#bd_appkey_text').hide();
		jQuery('#bd_secret_text').hide();
	}
	jQuery('#bd_applets').click(function() {
		jQuery('#bd_appkey_text').fadeToggle(400);
		jQuery('#bd_secret_text').fadeToggle(400);
	});
	if (jQuery('#tt_applets:checked').val() !== undefined) {
		jQuery('#tt_appid_text').show();
		jQuery('#tt_secret_text').show();
	} else {
		jQuery('#tt_appid_text').hide();
		jQuery('#tt_secret_text').hide();
	}
	jQuery('#tt_applets').click(function() {
		jQuery('#tt_appid_text').fadeToggle(400);
		jQuery('#tt_secret_text').fadeToggle(400);
	});
	// 广告选择
	function miniprogram_adsense_switch(){
		var switcheds 	= ['i_image_upload','t_image_upload','d_image_upload','p_image_upload'];
		var ads_types	= ['ad_i_type','ad_t_type','ad_d_type','ad_p_type'];
		
		$.each(switcheds, function(index,switched){
			$('#ad_'+switched).hide();
		});

		$.each(ads_types, function(index,ads_type){
			var select = $('select#'+ads_type).val();
			var image_upload = '#' + ads_type.replace(/type/, "image_upload")
			if(select != 'unit'){
				$(image_upload).show();
			}
		});
	}
	miniprogram_adsense_switch();
	$('select#ad_i_type').change(function(){
		miniprogram_adsense_switch();
	});
	$('select#ad_t_type').change(function(){
		miniprogram_adsense_switch();
	});
	$('select#ad_d_type').change(function(){
		miniprogram_adsense_switch();
	});
	$('select#ad_p_type').change(function(){
		miniprogram_adsense_switch();
	});
	$('body').on('click', 'a.mp-mu-text', function(){
		var prev_input	= $(this).prev("input");
		var prev_value 	= prev_input.val();
		prev_input.val('');
		$(this).parent().after($(this).parent().clone());
		prev_input.val(prev_value).after(wp.template('mp-del-item'));
		$(this).remove();
		return false;
	});
	$('body').on('click', '.del-item', function(){
		var next_input	= $(this).parent().next("input");
		if(next_input.length > 0){
			next_input.val('');
		}
		$(this).parent().fadeOut(300, function(){
			$(this).remove();
		});
		return false;
	});
	$('.mu-texts').sortable({
		handle: '.dashicons-menu'
	});
});