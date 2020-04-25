/**
 * Custom scripts needed for the colorpicker, image button selectors,
 * and navigation tabs.
 */

jQuery(document).ready(function($) {
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
	// 广告选项
	if (jQuery('#we_i_open:checked').val() !== undefined) {
		jQuery('#we_i_type_select').show();
		jQuery('#we_i_args_text').show();
		if (jQuery('#we_i_type').val() !== 'unit') {
        	jQuery('#we_i_image_upload').show();
        }
	} else {
		jQuery('#we_i_type_select').hide();
		jQuery('#we_i_args_text').hide();
		if (jQuery('#we_i_type').val() !== 'unit') {
        	jQuery('#we_i_image_upload').hide();
        }
	}
	jQuery('#we_i_open').click(function() {
		jQuery('#we_i_type_select').fadeToggle(400);
		jQuery('#we_i_args_text').fadeToggle(400);
		if (jQuery('#we_i_type').val() !== 'unit') {
			jQuery('#we_i_image_upload').fadeToggle(400);
		}
	});
	if (jQuery('#we_t_open:checked').val() !== undefined) {
		jQuery('#we_t_open_checkbox').show();
		jQuery('#we_t_args_text').show();
		if (jQuery('#we_i_type').val() !== 'unit') {
        	jQuery('#we_i_image_upload').show();
        }
	} else {
		jQuery('#we_t_type_select').hide();
		jQuery('#we_t_args_text').hide();
		if (jQuery('#we_t_type').val() !== 'unit') {
        	jQuery('#we_t_image_upload').hide();
        }
	}
	jQuery('#we_t_open').click(function() {
		jQuery('#we_t_type_select').fadeToggle(400);
		jQuery('#we_t_args_text').fadeToggle(400);
		if (jQuery('#we_t_type').val() !== 'unit') {
        	jQuery('#we_t_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#we_d_open:checked').val() !== undefined) {
		jQuery('#we_d_type_select').show();
		jQuery('#we_d_args_text').show();
		if (jQuery('#we_d_type').val() !== 'unit') {
        	jQuery('#we_d_image_upload').show();
        }
	} else {
		jQuery('#we_d_type_select').hide();
		jQuery('#we_d_args_text').hide();
		if (jQuery('#we_d_type').val() !== 'unit') {
        	jQuery('#we_d_image_upload').hide();
        }
	}
	jQuery('#we_d_open').click(function() {
		jQuery('#we_d_type_select').fadeToggle(400);
		jQuery('#we_d_args_text').fadeToggle(400);
		if (jQuery('#we_d_type').val() !== 'unit') {
        	jQuery('#we_d_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#we_p_open:checked').val() !== undefined) {
		jQuery('#we_p_type_select').show();
		jQuery('#we_p_args_text').show();
		if (jQuery('#we_p_type').val() !== 'unit') {
        	jQuery('#we_p_image_upload').show();
        }
	} else {
		jQuery('#we_p_type_select').hide();
		jQuery('#we_p_args_text').hide();
		if (jQuery('#we_p_type').val() !== 'unit') {
        	jQuery('#we_p_image_upload').hide();
        }
	}
	jQuery('#we_p_open').click(function() {
		jQuery('#we_p_type_select').fadeToggle(400);
		jQuery('#we_p_args_text').fadeToggle(400);
		if (jQuery('#we_p_type').val() !== 'unit') {
        	jQuery('#we_p_image_upload').fadeToggle(400);
        }
	});

	if (jQuery('#qq_i_open:checked').val() !== undefined) {
		jQuery('#qq_i_type_select').show();
		jQuery('#qq_i_args_text').show();
		if (jQuery('#qq_i_type').val() !== 'unit') {
        	jQuery('#qq_i_image_upload').show();
        }
	} else {
		jQuery('#qq_i_type_select').hide();
		jQuery('#qq_i_args_text').hide();
		if (jQuery('#qq_i_type').val() !== 'unit') {
        	jQuery('#qq_i_image_upload').hide();
        }
	}
	jQuery('#qq_i_open').click(function() {
		jQuery('#qq_i_type_select').fadeToggle(400);
		jQuery('#qq_i_args_text').fadeToggle(400);
		if (jQuery('#qq_i_type').val() !== 'unit') {
			jQuery('#qq_i_image_upload').fadeToggle(400);
		}
	});
	if (jQuery('#qq_t_open:checked').val() !== undefined) {
		jQuery('#qq_t_open_checkbox').show();
		jQuery('#qq_t_args_text').show();
		if (jQuery('#qq_i_type').val() !== 'unit') {
        	jQuery('#qq_i_image_upload').show();
        }
	} else {
		jQuery('#qq_t_type_select').hide();
		jQuery('#qq_t_args_text').hide();
		if (jQuery('#qq_t_type').val() !== 'unit') {
        	jQuery('#qq_t_image_upload').hide();
        }
	}
	jQuery('#qq_t_open').click(function() {
		jQuery('#qq_t_type_select').fadeToggle(400);
		jQuery('#qq_t_args_text').fadeToggle(400);
		if (jQuery('#qq_t_type').val() !== 'unit') {
        	jQuery('#qq_t_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#qq_d_open:checked').val() !== undefined) {
		jQuery('#qq_d_type_select').show();
		jQuery('#qq_d_args_text').show();
		if (jQuery('#qq_d_type').val() !== 'unit') {
        	jQuery('#qq_d_image_upload').show();
        }
	} else {
		jQuery('#qq_d_type_select').hide();
		jQuery('#qq_d_args_text').hide();
		if (jQuery('#qq_d_type').val() !== 'unit') {
        	jQuery('#qq_d_image_upload').hide();
        }
	}
	jQuery('#qq_d_open').click(function() {
		jQuery('#qq_d_type_select').fadeToggle(400);
		jQuery('#qq_d_args_text').fadeToggle(400);
		if (jQuery('#qq_d_type').val() !== 'unit') {
        	jQuery('#qq_d_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#qq_p_open:checked').val() !== undefined) {
		jQuery('#qq_p_type_select').show();
		jQuery('#qq_p_args_text').show();
		if (jQuery('#qq_p_type').val() !== 'unit') {
        	jQuery('#qq_p_image_upload').show();
        }
	} else {
		jQuery('#qq_p_type_select').hide();
		jQuery('#qq_p_args_text').hide();
		if (jQuery('#qq_p_type').val() !== 'unit') {
        	jQuery('#qq_p_image_upload').hide();
        }
	}
	jQuery('#qq_p_open').click(function() {
		jQuery('#qq_p_type_select').fadeToggle(400);
		jQuery('#qq_p_args_text').fadeToggle(400);
		if (jQuery('#qq_p_type').val() !== 'unit') {
        	jQuery('#qq_p_image_upload').fadeToggle(400);
        }
	});

	if (jQuery('#bd_i_open:checked').val() !== undefined) {
		jQuery('#bd_i_type_select').show();
		jQuery('#bd_i_args_text').show();
		if (jQuery('#bd_i_type').val() !== 'unit') {
        	jQuery('#bd_i_image_upload').show();
        }
	} else {
		jQuery('#bd_i_type_select').hide();
		jQuery('#bd_i_args_text').hide();
		if (jQuery('#bd_i_type').val() !== 'unit') {
        	jQuery('#bd_i_image_upload').hide();
        }
	}
	jQuery('#bd_i_open').click(function() {
		jQuery('#bd_i_type_select').fadeToggle(400);
		jQuery('#bd_i_args_text').fadeToggle(400);
		if (jQuery('#bd_i_type').val() !== 'unit') {
			jQuery('#bd_i_image_upload').fadeToggle(400);
		}
	});
	if (jQuery('#bd_t_open:checked').val() !== undefined) {
		jQuery('#bd_t_open_checkbox').show();
		jQuery('#bd_t_args_text').show();
		if (jQuery('#bd_i_type').val() !== 'unit') {
        	jQuery('#bd_i_image_upload').show();
        }
	} else {
		jQuery('#bd_t_type_select').hide();
		jQuery('#bd_t_args_text').hide();
		if (jQuery('#bd_t_type').val() !== 'unit') {
        	jQuery('#bd_t_image_upload').hide();
        }
	}
	jQuery('#bd_t_open').click(function() {
		jQuery('#bd_t_type_select').fadeToggle(400);
		jQuery('#bd_t_args_text').fadeToggle(400);
		if (jQuery('#bd_t_type').val() !== 'unit') {
        	jQuery('#bd_t_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#bd_d_open:checked').val() !== undefined) {
		jQuery('#bd_d_type_select').show();
		jQuery('#bd_d_args_text').show();
		if (jQuery('#bd_d_type').val() !== 'unit') {
        	jQuery('#bd_d_image_upload').show();
        }
	} else {
		jQuery('#bd_d_type_select').hide();
		jQuery('#bd_d_args_text').hide();
		if (jQuery('#bd_d_type').val() !== 'unit') {
        	jQuery('#bd_d_image_upload').hide();
        }
	}
	jQuery('#bd_d_open').click(function() {
		jQuery('#bd_d_type_select').fadeToggle(400);
		jQuery('#bd_d_args_text').fadeToggle(400);
		if (jQuery('#bd_d_type').val() !== 'unit') {
        	jQuery('#bd_d_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#bd_p_open:checked').val() !== undefined) {
		jQuery('#bd_p_type_select').show();
		jQuery('#bd_p_args_text').show();
		if (jQuery('#bd_p_type').val() !== 'unit') {
        	jQuery('#bd_p_image_upload').show();
        }
	} else {
		jQuery('#bd_p_type_select').hide();
		jQuery('#bd_p_args_text').hide();
		if (jQuery('#bd_p_type').val() !== 'unit') {
        	jQuery('#bd_p_image_upload').hide();
        }
	}
	jQuery('#bd_p_open').click(function() {
		jQuery('#bd_p_type_select').fadeToggle(400);
		jQuery('#bd_p_args_text').fadeToggle(400);
		if (jQuery('#bd_p_type').val() !== 'unit') {
        	jQuery('#bd_p_image_upload').fadeToggle(400);
        }
	});

	if (jQuery('#tt_i_open:checked').val() !== undefined) {
		jQuery('#tt_i_type_select').show();
		jQuery('#tt_i_args_text').show();
		if (jQuery('#tt_i_type').val() !== 'unit') {
        	jQuery('#tt_i_image_upload').show();
        }
	} else {
		jQuery('#tt_i_type_select').hide();
		jQuery('#tt_i_args_text').hide();
		if (jQuery('#tt_i_type').val() !== 'unit') {
        	jQuery('#tt_i_image_upload').hide();
        }
	}
	jQuery('#tt_i_open').click(function() {
		jQuery('#tt_i_type_select').fadeToggle(400);
		jQuery('#tt_i_args_text').fadeToggle(400);
		if (jQuery('#tt_i_type').val() !== 'unit') {
			jQuery('#tt_i_image_upload').fadeToggle(400);
		}
	});
	if (jQuery('#tt_t_open:checked').val() !== undefined) {
		jQuery('#tt_t_open_checkbox').show();
		jQuery('#tt_t_args_text').show();
		if (jQuery('#tt_i_type').val() !== 'unit') {
        	jQuery('#tt_i_image_upload').show();
        }
	} else {
		jQuery('#tt_t_type_select').hide();
		jQuery('#tt_t_args_text').hide();
		if (jQuery('#tt_t_type').val() !== 'unit') {
        	jQuery('#tt_t_image_upload').hide();
        }
	}
	jQuery('#tt_t_open').click(function() {
		jQuery('#tt_t_type_select').fadeToggle(400);
		jQuery('#tt_t_args_text').fadeToggle(400);
		if (jQuery('#tt_t_type').val() !== 'unit') {
        	jQuery('#tt_t_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#tt_d_open:checked').val() !== undefined) {
		jQuery('#tt_d_type_select').show();
		jQuery('#tt_d_args_text').show();
		if (jQuery('#tt_d_type').val() !== 'unit') {
        	jQuery('#tt_d_image_upload').show();
        }
	} else {
		jQuery('#tt_d_type_select').hide();
		jQuery('#tt_d_args_text').hide();
		if (jQuery('#tt_d_type').val() !== 'unit') {
        	jQuery('#tt_d_image_upload').hide();
        }
	}
	jQuery('#tt_d_open').click(function() {
		jQuery('#tt_d_type_select').fadeToggle(400);
		jQuery('#tt_d_args_text').fadeToggle(400);
		if (jQuery('#tt_d_type').val() !== 'unit') {
        	jQuery('#tt_d_image_upload').fadeToggle(400);
        }
	});
	if (jQuery('#tt_p_open:checked').val() !== undefined) {
		jQuery('#tt_p_type_select').show();
		jQuery('#tt_p_args_text').show();
		if (jQuery('#tt_p_type').val() !== 'unit') {
        	jQuery('#tt_p_image_upload').show();
        }
	} else {
		jQuery('#tt_p_type_select').hide();
		jQuery('#tt_p_args_text').hide();
		if (jQuery('#tt_p_type').val() !== 'unit') {
        	jQuery('#tt_p_image_upload').hide();
        }
	}
	jQuery('#tt_p_open').click(function() {
		jQuery('#tt_p_type_select').fadeToggle(400);
		jQuery('#tt_p_args_text').fadeToggle(400);
		if (jQuery('#tt_p_type').val() !== 'unit') {
        	jQuery('#tt_p_image_upload').fadeToggle(400);
        }
	});
	// 广告选择
	function miniprogram_adsense_switch() {
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
	// 类型选择
	function miniprogram_we_adsense_switch( ) {
		var switcheds 	= ['i_image_upload','t_image_upload','d_image_upload','p_image_upload'];
		var selectCss	= ['i_type','t_type','d_type','p_type'];
		
		$.each(switcheds, function(index,switched) {
			$('#we_'+switched).hide();
		});

		$.each(selectCss, function(index,selectId) {
			var select = $('select#we_' + selectId).val();
			var image_upload = '#we_' + selectId.replace(/type/, "image_upload")
			if(select != 'unit'){
				$(image_upload).show();
			}
		});
	}
	function miniprogram_qq_adsense_switch( ) {
		var switcheds 	= ['i_image_upload','t_image_upload','d_image_upload','p_image_upload'];
		var selectCss	= ['i_type','t_type','d_type','p_type'];
		
		$.each(switcheds, function(index,switched) {
			$('#qq_'+switched).hide();
		});

		$.each(selectCss, function(index,selectId) {
			var select = $('select#qq_' + selectId).val();
			var image_upload = '#qq_' + selectId.replace(/type/, "image_upload")
			if(select != 'unit'){
				$(image_upload).show();
			}
		});
	}
	function miniprogram_bd_adsense_switch( ) {
		var switcheds 	= ['i_image_upload','t_image_upload','d_image_upload','p_image_upload'];
		var selectCss	= ['i_type','t_type','d_type','p_type'];
		
		$.each(switcheds, function(index,switched) {
			$('#bd_'+switched).hide();
		});

		$.each(selectCss, function(index,selectId) {
			var select = $('select#bd_' + selectId).val();
			var image_upload = '#bd_' + selectId.replace(/type/, "image_upload")
			if(select != 'unit'){
				$(image_upload).show();
			}
		});
	}
	function miniprogram_tt_adsense_switch( ) {
		var switcheds 	= ['i_image_upload','t_image_upload','d_image_upload','p_image_upload'];
		var selectCss	= ['i_type','t_type','d_type','p_type'];
		
		$.each(switcheds, function(index,switched) {
			$('#tt_'+switched).hide();
		});

		$.each(selectCss, function(index,selectId) {
			var select = $('select#tt_' + selectId).val();
			var image_upload = '#tt_' + selectId.replace(/type/, "image_upload")
			if(select != 'unit'){
				$(image_upload).show();
			}
		});
	}
	miniprogram_we_adsense_switch( );
	$('select#we_i_type').change(function() {
		miniprogram_we_adsense_switch( );
	});
	$('select#we_t_type').change(function() {
		miniprogram_we_adsense_switch( );
	});
	$('select#we_d_type').change(function() {
		miniprogram_we_adsense_switch( );
	});
	$('select#we_p_type').change(function() {
		miniprogram_we_adsense_switch( );
	});
	miniprogram_qq_adsense_switch( );
	$('select#qq_i_type').change(function() {
		miniprogram_qq_adsense_switch( );
	});
	$('select#qq_t_type').change(function() {
		miniprogram_qq_adsense_switch( );
	});
	$('select#qq_d_type').change(function() {
		miniprogram_qq_adsense_switch( );
	});
	$('select#qq_p_type').change(function() {
		miniprogram_qq_adsense_switch( );
	});
	miniprogram_bd_adsense_switch( );
	$('select#bd_i_type').change(function() {
		miniprogram_bd_adsense_switch( );
	});
	$('select#bd_t_type').change(function() {
		miniprogram_bd_adsense_switch( );
	});
	$('select#bd_d_type').change(function() {
		miniprogram_bd_adsense_switch( );
	});
	$('select#wbd_p_type').change(function() {
		miniprogram_bd_adsense_switch( );
	});
	miniprogram_tt_adsense_switch( );
	$('select#tt_i_type').change(function() {
		miniprogram_tt_adsense_switch( );
	});
	$('select#tt_t_type').change(function() {
		miniprogram_tt_adsense_switch( );
	});
	$('select#tt_d_type').change(function() {
		miniprogram_tt_adsense_switch( );
	});
	$('select#tt_p_type').change(function() {
		miniprogram_tt_adsense_switch( );
	});
});