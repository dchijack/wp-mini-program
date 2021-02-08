<?php
	global $pagenow;
	if( isset( $_REQUEST['settings-updated'] ) ) {
		wp_cache_flush();
	}
?>
<div class="wrap">
	<h2 class="mp-nav-tab-wrapper wp-clearfix">
		<?php miniprogram_options_nav_menu( $options ); ?>
	</h2>
	
	<div id="section" class="section-container wp-clearfix">
		<form id="<?php echo $option["id"]; ?>" method="post" action="options.php" enctype="multipart/form-data">
			<?php settings_fields( $option['group'] ); ?>
			<?php miniprogram_options_container( $option['options'], $options ); ?>
			<?php do_settings_sections( $option['group'] ); ?>
			<?php submit_button(); ?>
		</form>
	</div><!-- / #container -->
</div><!-- / .wrap -->
<div id="scroll-bar">
	<div id="goTop" class="scroll-up"><a href="javascript:;" title="直达顶部">▲</a></div>
	<div id="down" class="scroll-down"><a href="javascript:;" title="直达底部">▼</a></div>
</div>