<?php
/*
 * WordPress Custom API Data Hooks
 */
 
if( !defined( 'ABSPATH' ) ) exit;

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
	if(wp_miniprogram_option('sticky')) {
		$fields['focus'] = ['title'=>'推荐文章', 'type'=>'checkbox',	'description'=>'是否在小程序推荐文章'];
	}
	$fields['source'] = ['title'=>'出处/作者', 'type'=>'text',	'class' => 'regular-text','description'=>'文章引用来源/出处,或填写文章作者'];
	$fields['thumbnail'] = ['title'=>'自定义缩略图',	'type'=>'upload','class' => 'regular-text','description'=>'自定义缩略图地址.注意:设置后无须另行设置特色图像'];
	if(wp_miniprogram_option('mediaon')) {
		$fields['cover'] = ['title'=>'封面图像', 'type'=>'upload','class' => 'regular-text','description'=>'视频封面,不设置则采用文章缩略图'];
		$fields['author'] = ['title'=>'视频作者', 'type'=>'text','class' => 'regular-text','description'=>'视频表演作者'];
		$fields['title'] = ['title'=>'作品名称', 'type'=>'text','class' => 'regular-text','description'=>'视频作品名称'];
		$fields['video'] = ['title'=>'视频地址', 'type'=>'upload',	'class' => 'regular-text'];
		$fields['audio'] = ['title'=>'音频地址', 'type'=>'upload',	'class' => 'regular-text'];
	}
	if(wp_miniprogram_option('bd_appkey') && wp_miniprogram_option('bd_secret')) {
		$fields['keywords'] = ['title'=>'Web 关键词', 'type'=>'text', 'class' => 'regular-text','description'=>'百度小程序 Web 化页面关键词设置, 多个关键词用英文逗号隔开'];
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
	if(wp_miniprogram_option('bd_appkey') && wp_miniprogram_option('bd_secret')) {
		$options['page-box']['fields']['keywords'] = ['title'=>'Web 关键词', 'type'=>'text', 'class' => 'regular-text','description'=>'百度小程序 Web 化页面关键词设置, 多个关键词用英文逗号隔开'];
	}
	return $options;
});

if( wp_miniprogram_option('sticky') ) {
	add_filter( 'views_edit-post', function ( $views ) {
		global $current_user, $wp_query;
		$query = array(  
			'post_type'   => 'post',  
			'post_status' => 'publish',
			'meta_key'	=> 'focus'
		);
		$result = new WP_Query($query);
		$class = isset($_GET['focus'])  ? ' class="current"' : '';  
		$views[] = sprintf(__('<a href="%s" ' .$class.' aria-current="page">推荐文章 <span class="count">(%d)</span></a>', 'focus'), admin_url('edit.php?post_type=post&focus=true'), $result->found_posts); 
		return $views; 
	});
	add_filter('parse_query', function ($query) {
		global $pagenow;
		if( is_admin() && 'edit.php' == $pagenow && isset($_GET[ 'focus' ]) ) {
			$query->query_vars[ 'post_type' ]    = 'post';
			$query->query_vars[ 'post_status' ]  = 'publish';
			$query->query_vars[ 'meta_key' ] 	 = 'focus';
		}
		return $query;
	});
}

add_filter('admin_comment_types_dropdown', function($comment_types) {
	unset($comment_types['pings']);
	return array_merge($comment_types, ['fav'=>'收藏'], ['like'=>'喜欢']);
});

add_action('parse_comment_query', function($comment_query) {
	if(is_singular()) {
		if(isset($comment_query->query_vars['parent']) && $comment_query->query_vars['parent'] == 0) {
			$comment_query->query_vars['type']	= array('','comment');
		}	
	}
});

if( wp_miniprogram_option("we_submit") ) {
	add_action('publish_post','we_miniprogram_posts_submit_pages',10,1);
	add_action('publish_to_publish',function () {
		remove_action('publish_post','we_miniprogram_posts_submit_pages',10,1);
	},11,1);
}
function we_miniprogram_posts_submit_pages( $post_id ) {
	$submit = array( );
	$submit['wechat'] = apply_filters( 'mp_we_submit_pages', $post_id );
	if(wp_miniprogram_option('bd_submit') && wp_miniprogram_option('bd_appkey') && wp_miniprogram_option('bd_secret')) {
		$submit['baidu'] = apply_filters( 'mp_bd_submit_pages', $post_id );
	}
	return $submit;
}

add_filter('mp_cover_url', function( $thumbnail ) {
	$prefix = parse_url( $thumbnail );
	$host = $prefix["host"];
	$trust_domain = wp_miniprogram_option('trust_domain');
	$domains = array();
	foreach( $trust_domain as $domain ) {
		$domains[] = str_replace( "http://", "", str_replace( "https://", "", $domain ) );
	}
	if( in_array($host, $domains) ) { 
		return $thumbnail;
	}
	return wp_miniprogram_option('thumbnail');
});

function wp_miniprogram_comment_type( $type ) {
	$option = array( "like" => "点赞", "fav" => "收藏" );
	$title = apply_filters( 'custom_comment_type_text', $option );
	if( isset($title[$type]) ) {
		return $title[$type];
	}
	return $type;
}

function wp_miniprogram_comment_post( $post_id ) {
	$option = array( "post" => "文章" );
	$title = apply_filters( 'custom_comment_post_text', $option );
	$type = get_post_type( $post_id );
	if( isset($title[$type]) ) {
		return $title[$type];
	}
	return $type;
}

function wp_miniprogram_rest_post( $post, $request ) {
	$_data = array( );
	$user_id = 0;
	$post_format = get_post_format( $post->ID );
	$author_avatar = get_user_meta( $post->post_author, 'avatar', true );
	$taxonomies = get_object_taxonomies( $post->post_type );
	$access_token = isset($request['access_token']) ? $request['access_token'] : '';
	if( $access_token ) {
		$users = MP_Auth::login( base64_decode( $access_token ) );
		if( $users ) {
			$user_id = $users->ID;
		}
	}
	$_data["id"]  = $post->ID;
	$_data["date"] = $post->post_date;
	$_data["week"] = get_wp_post_week( $post->post_date );
	$_data["format"] = $post_format ? $post_format : 'standard'; 
	$_data["type"] = $post->post_type;
	if( get_post_meta( $post->ID, "source" ,true ) ) {
		$_data["meta"]["source"] = get_post_meta( $post->ID, "source" ,true );
	}
	$_data["meta"]["thumbnail"] = apply_filters( 'post_thumbnail', $post->ID );
	$_data["meta"]["views"] = (int)get_post_meta( $post->ID, "views" ,true );
	$meta = apply_filters( 'custom_meta', $meta = array() );
	if(  $meta ) {
		foreach ( $meta as $meta_key ) {
			$_data["meta"][$meta_key] = get_post_meta( $post->ID, $meta_key ,true );
		}
	}
	$_data["comments"] = apply_filters( 'comment_type_count', $post->ID, 'comment' );
	$_data["isfav"] = apply_filters( 'miniprogram_commented', $post->ID, $user_id, 'fav' );
	$_data["favs"] = apply_filters( 'comment_type_count', $post->ID, 'fav' );
	$_data["islike"] = apply_filters( 'miniprogram_commented', $post->ID, $user_id, 'like' );
	$_data["likes"] = apply_filters( 'comment_type_count', $post->ID, 'like' );
	$_data["author"]["id"] = (int)$post->post_author;
	$_data["author"]["name"] = get_the_author_meta( 'nickname', $post->post_author );
	if( $author_avatar ) {
		$_data["author"]["avatar"] = $author_avatar;
	} else {
		$_data["author"]["avatar"] = get_avatar_url( $post->post_author );
	}
	$_data["author"]["description"] = get_the_author_meta( 'description', $post->post_author );
	if( $taxonomies ) {
		foreach( $taxonomies as $taxonomy ){
			$terms = wp_get_post_terms( $post->ID, $taxonomy, array('orderby' => 'term_id', 'order' => 'ASC', 'fields' => 'all') );
			foreach( $terms as $term ) {
				$tax = array();
				$tax["id"] = $term->term_id;
				$tax["name"] = $term->name;
				$tax["description"] = $term->description;
				$tax["cover"] = apply_filters( 'mp_thumbnail_url', get_term_meta($term->term_id, 'cover', true) );
				if( $taxonomy === 'post_tag' ) { $taxonomy = "tag"; }
				$_data[$taxonomy][] = $tax;
			}
		}
	}
	$_data["title"]["rendered"]  = html_entity_decode( $post->post_title );
	if( $post->post_excerpt ) {
		$_data["excerpt"]["rendered"] = html_entity_decode(wp_trim_words( wp_strip_all_tags( $post->post_excerpt ), 100, '...' ));
	} else {
		$_data["excerpt"]["rendered"] = html_entity_decode(wp_trim_words( wp_strip_all_tags( $post->post_content ), 100, '...' ));
	}
	if( wp_miniprogram_option("post_content") ) { 
		$_data["content"]["rendered"] = apply_filters( 'the_content', $post->post_content );
	 }
	if( wp_miniprogram_option("post_picture") ) {
		$_data["pictures"] = apply_filters( 'posts_gallery', $post->ID );
	}
	return apply_filters( "custom_rest_prepare_{$post->post_type}", $_data, $post, $request );
}