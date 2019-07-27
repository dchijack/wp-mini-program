<?php
/*
 * WordPress Custom API Data Hooks
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

add_filter('term_options',function ($options){
	$options['cover'] = array(
		'taxonomies' => array('category','post_tag'),
		'title' => '封面', 
		'type' => 'upload'
	);
	return $options;
});

// Meta
add_filter( 'meta_options',function ($options) {
	$fields = array();
	$options['post-box']['title'] = '文章设置';
	$options['post-box']['type'] = 'post';
	if (wp_miniprogram_option('sticky')) {
		$fields['focus'] = ['title'=>'推荐文章', 'type'=>'checkbox',	'description'=>'是否在小程序推荐文章'];
	}
	$fields['source'] = ['title'=>'出处/作者',	'type'=>'text',	'class' => 'regular-text','description'=>'文章引用来源/出处,或填写文章作者'];
	$fields['thumbnail'] = ['title'=>'自定义缩略图',	'type'=>'upload','class' => 'regular-text','description'=>'自定义缩略图地址.注意:设置后无须另行设置特色图像'];
	if (wp_miniprogram_option('mediaon')) {
		$fields['cover'] = ['title'=>'封面图像',		'type'=>'upload','class' => 'regular-text','description'=>'视频/音频封面图像,不设置则采用文章缩略图'];
		$fields['author'] = ['title'=>'封面图像',		'type'=>'upload','class' => 'regular-text','description'=>'视频/音频封面图像,不设置则采用文章缩略图'];
		$fields['title'] = ['title'=>'作品名称',		'type'=>'text','class' => 'regular-text','description'=>'视频/音频的作品名称,比如歌曲名称'];
		$fields['video'] = ['title'=>'视频地址',		'type'=>'upload',	'class' => 'regular-text'];
		$fields['audio'] = ['title'=>'音频地址',		'type'=>'upload',	'class' => 'regular-text'];
	}
	$options['post-box']['fields'] = $fields;
	$options['page-box'] =  [
		'title'   => '页面设置',
		'type'	  => 'page',
		'fields'  => [
			'icon'			=>['title'=>'ICON',	'type'=>'text',	'class' => 'regular-text','description'=>'页面列表项 ICON ，用于个人中心页输出页面列表的图标'],
			'title'			=>['title'=>'标题',	'type'=>'text',	'class' => 'regular-text','description'=>'页面列表项标题，用于个人中心页输出页面列表的标题简称'],
			'thumbnail'		=>['title'=>'自定义缩略图',	'type'=>'upload','class' => 'regular-text','description'=>'自定义缩略图地址.注意:设置后无须另行设置特色图像']
		]
	];
	return $options;
});