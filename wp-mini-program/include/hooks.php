<?php
/*
 * WordPress Custom API Data Hooks
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

// 屏蔽不常用 REST
if(wp_miniprogram_option('rest_other')) {
	add_filter( 'rest_endpoints', function( $endpoints ) {
		unset( $endpoints['/wp/v2/users'] );
		unset( $endpoints['/wp/v2/users/me'] );
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		unset( $endpoints['/wp/v2/posts/(?P<parent>[\d]+)/revisions']);
		unset( $endpoints['/wp/v2/posts/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)']);
		unset( $endpoints['/wp/v2/posts/(?P<id>[\d]+)/autosaves']);
		unset( $endpoints['/wp/v2/posts/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)']);
		unset( $endpoints['/wp/v2/pages/(?P<parent>[\d]+)/revisions']);
		unset( $endpoints['/wp/v2/pages/(?P<parent>[\d]+)/revisions/(?P<id>[\d]+)']);
		unset( $endpoints['/wp/v2/pages/(?P<id>[\d]+)/autosaves']);
		unset( $endpoints['/wp/v2/pages/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)']);
		unset( $endpoints['/wp/v2/comments']);
		unset( $endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
		unset( $endpoints['/wp/v2/statuses']);
		unset( $endpoints['/wp/v2/statuses/(?P<status>[\w-]+)']);
		unset( $endpoints['/wp/v2/settings']);
		unset( $endpoints['/wp/v2/themes']);
		return $endpoints;
	});
}

add_filter( 'rest_prepare_post',function ($data, $post, $request) {
	//global $wpdb;
	$_data = $data->data;
	$post_id = $post->ID;
	$post_date = $post->post_date;
	$author_id = $post->post_author;
	$author_avatar = get_user_meta($author_id, 'avatar', true);
	$taxonomies = get_object_taxonomies($_data['type']);
	$post_title = $post->post_title;
	$post_views = (int)get_post_meta( $post_id, "views" ,true );
	$post_excerpt = $_data["excerpt"]["rendered"];
	$post_content = $_data["content"]["rendered"];
	$session = isset($request['access_token'])?$request['access_token']:'';
	if( $session ) {
		$access_token = base64_decode( $session );
		$users = MP_Auth::login( $access_token );
		if ( $users ) {
			$user_id = $users->ID;
		} else {
			$user_id = 0;
		}
	} else {
		$user_id = 0;
	}
	$_data["id"]  = $post_id;
	$_data["date"] = $post_date;
	unset($_data['author']);
	$_data["author"]["id"] = $author_id;
	$_data["author"]["name"] = get_the_author_meta('nickname',$author_id);
	if ($author_avatar) {
		$_data["author"]["avatar"] = $author_avatar;
	} else {
		$_data["author"]["avatar"] = get_avatar_url($author_id);
	}
	$_data["author"]["description"] = get_the_author_meta('description',$author_id);
	$_data["meta"]["thumbnail"] = apply_filters( 'post_thumbnail', $post_id );
	$_data["meta"]["views"] = $post_views;
	$_data["comments"] = apply_filters( 'comment_type_count', $post_id, 'comment' );
	$_data["isfav"] = apply_filters( 'mp_commented', $post_id, $user_id, 'fav' );
	$_data["favs"] = apply_filters( 'comment_type_count', $post_id, 'fav' );
	$_data["islike"] = apply_filters( 'mp_commented', $post_id, $user_id, 'like' );
	$_data["likes"] = apply_filters( 'comment_type_count', $post_id, 'like' );
	if ($taxonomies) {
		foreach ( $taxonomies as $taxonomy ){
			$terms = wp_get_post_terms($post_id, $taxonomy, array('orderby' => 'term_id', 'order' => 'ASC', 'fields' => 'all'));
			foreach($terms as $term) {
				$tax = array();
				$tax["id"] = $term->term_id;
				$tax["name"] = $term->name;
				$tax["description"] = $term->description;
				$tax["cover"] = get_term_meta($term->term_id,'cover',true);
				if ($taxonomy === 'post_tag') { $taxonomy = "tag"; }
				$_data[$taxonomy][] = $tax;
			}
		}
	}
	$_data["title"]["rendered"] = html_entity_decode( $post_title );
	$_data["excerpt"]["rendered"] = html_entity_decode( strip_tags( trim( $post_excerpt ) ) ); 
	if ( isset( $request['id'] ) ) {
		if( !update_post_meta( $post_id, 'views', ( $post_views + 1 ) ) ) {
			add_post_meta($post_id, 'views', 1, true);  
		}
		$media_cover = get_post_meta( $post_id, 'cover' ,true );
		$media_author = get_post_meta( $post_id, 'author' ,true );
		$media_title = get_post_meta( $post_id, 'title' ,true );
		$media_video = get_post_meta( $post_id, 'video' ,true );
		$media_audio = get_post_meta( $post_id, 'audio' ,true );
		if (wp_miniprogram_option('mediaon') && ($media_video || $media_audio)) {
			if ($media_cover) {
				$_data["media"]['cover'] = $media_cover;
			} else {
				$_data["media"]['cover'] = apply_filters( 'post_thumbnail', $post_id );
			}
			if($media_author) {
				$_data["media"]['author'] = $media_author;
			}
			if($media_title) {
				$_data["media"]['title'] = $media_title;
			}
			if($media_video) {
				$_data["media"]['video'] = $media_video;
			}
			if($media_audio) {
				$_data["media"]['audio'] = $media_audio;
			}
		} 
		if(!$media_video) {
			$_data["content"]["rendered"] = apply_filters( 'the_video_content', $post_content );
		}
		$_data["post_favs"] = apply_filters( 'comment_type_list', $post_id, 'fav' );
		$_data["post_likes"] = apply_filters( 'comment_type_list', $post_id, 'like' );
		if (wp_miniprogram_option("prevnext")) {
			$category = get_the_category( $post_id );
			$next = get_next_post($category[0]->term_id, '', 'category');
			$previous = get_previous_post($category[0]->term_id, '', 'category');
			if (!empty($next->ID)) {
				$_data["next_post"]["id"] = $next->ID;
				$_data["next_post"]["title"]["rendered"] = $next->post_title;
				$_data["next_post"]["thumbnail"] = apply_filters( 'post_thumbnail', $next->ID );
				$_data["next_post"]["views"] = (int)get_post_meta( $next->ID, "views" ,true );
			}
			if (!empty($previous->ID)) {
				$_data["prev_post"]["id"] = $previous->ID;
				$_data["prev_post"]["title"]["rendered"] = $previous->post_title;
				$_data["prev_post"]["thumbnail"] = apply_filters( 'post_thumbnail', $previous->ID );
				$_data["prev_post"]["views"] = (int)get_post_meta( $previous->ID, "views" ,true );
			}
		}
	} else {
		if ( !wp_miniprogram_option("post_content") ) { unset($_data['content']); }
		if ( wp_miniprogram_option("post_picture") ) {
			$_data["pictures"] = apply_filters( 'post_images', $post_id );
		}
	}
	if(wp_miniprogram_option('gutenberg')) {
		unset($_data['categories']);
		unset($_data['tags']);
		unset($_data["_edit_lock"]);
		unset($_data["_edit_last"]);
		unset($_data['featured_media']);
		unset($_data['ping_status']);
		unset($_data['template']);
		unset($_data['slug']);
		unset($_data['status']);
		unset($_data['modified_gmt']);
		unset($_data['post_format']);
		unset($_data['date_gmt']);
		unset($_data['guid']);
		unset($_data['curies']);
		unset($_data['modified']);
		unset($_data['status']);
		unset($_data['comment_status']);
		unset($_data['sticky']);    
		unset($_data['_links']);
	}
	wp_cache_set('post_id_'.$post_id,$_data,'post_id_'.$post_id.'_group',3600);
	$_post = wp_cache_get('post_id_'.$post_id,'post_id_'.$post_id.'_group');
	if( $_post === false ){
		$_post = $_data;
		wp_cache_set('post_id_'.$post_id,$_data,'post_id_'.$post_id.'_group',3600);
	}
    $data->data = $_post;
	return $data;
}, 10, 3 );

add_filter( 'rest_prepare_page',function ($data, $post, $request) {
	$_data = $data->data;
	$post_id = $post->ID;
	$post_date = $post->post_date;
	$author_id = $post->post_author;
	$author_avatar = get_user_meta($author_id, 'avatar', true);
	$post_title = $post->post_title;
	$post_views = (int)get_post_meta( $post_id, "views" ,true );
	$post_excerpt = $_data["excerpt"]["rendered"];
	$_data["id"]  = $post_id;
	$_data["date"] = $post_date;
	$_data["time"] = datetime_standard($post_date);
	$_data["week"] = get_post_weekday($post_date);
	$_data["link"] = get_the_permalink($post_id);
	$_data["except"] = get_post_meta( $post_id, "except" ,true )?true:false;
	unset($_data['author']);
	$_data["author"]["id"] = $author_id;
	$_data["author"]["name"] = get_the_author_meta('nickname',$author_id);
	if ($author_avatar) {
		$_data["author"]["avatar"] = $author_avatar;
	} else {
		$_data["author"]["avatar"] = get_avatar_url($author_id);
	}
	$_data["author"]["description"] = get_the_author_meta('description',$author_id);
	//unset($_data["meta"]);
	$_data["menu"]["icon"] = get_post_meta( $post_id, "icon" ,true );
	$_data["menu"]["title"] = get_post_meta( $post_id, "title" ,true );
	$_data["meta"]["thumbnail"] = apply_filters( 'post_thumbnail', $post_id );
	$_data["meta"]["views"] = $post_views;
	$_data["comments"] = apply_filters( 'comment_type_count', $post_id, 'comment' );
	$_data["favs"] = apply_filters( 'comment_type_count', $post_id, 'fav' );
	$_data["likes"] = apply_filters( 'comment_type_count', $post_id, 'like' );
	$_data["title"]["rendered"] = html_entity_decode( $post_title );
	$_data["excerpt"]["rendered"] = html_entity_decode( strip_tags( trim( $post_excerpt ) ) ); 
	if ( !isset( $request['id'] ) ) {
		if (wp_miniprogram_option("post_content")) { unset($_data['content']); }
	} else {
		if( !update_post_meta( $post_id, 'views', ( $post_views + 1 ) ) ) {
			add_post_meta($post_id, 'views', 1, true);  
		}
	}
	if(wp_miniprogram_option('gutenberg')) {
		unset($_data["_edit_lock"]);
		unset($_data["_edit_last"]);
		unset($_data['featured_media']);
		unset($_data['ping_status']);
		unset($_data['template']);
		unset($_data['slug']);
		unset($_data['modified_gmt']);
		unset($_data['post_format']);
		unset($_data['date_gmt']);
		unset($_data['guid']);
		unset($_data['curies']);
		unset($_data['modified']);
		unset($_data['status']);
		unset($_data['comment_status']);
		unset($_data['sticky']);    
		unset($_data['_links']);
	}
	wp_cache_set('page_id_'.$post_id,$_data,'page_id_'.$post_id.'_group',3600);
	$_page = wp_cache_get('page_id_'.$post_id,'page_id_'.$post_id.'_group');
	if( $_page === false ){
		$_page = $_data;
		wp_cache_set('page_id_'.$post_id,$_data,'page_id_'.$post_id.'_group',3600);
	}
    $data->data = $_page;
	return $data;
}, 10, 3 );

add_filter( 'rest_prepare_category',function($data, $item, $request) {
	$cover = '';
	$term_id = $item->term_id;
	$args = array('category'=>$term_id,'numberposts' => 1);
	$posts = get_posts($args);
	if (!empty($posts)) {
		$recent_date = $posts[0]->post_date;
	} else {
		$recent_date = '无更新';
	}
    if(get_term_meta($item->term_id,'cover',true)) {
        $cover = get_term_meta($item->term_id,'cover',true);
    } else {
		$cover = wp_miniprogram_option('thumbnail');
	}
	if(get_term_meta($item->term_id,'except',true)) {
		$except = false;
	} else {
		$except = true;
	}
	$data->data['cover'] = $cover;
	$data->data['date'] = $recent_date;
	$data->data['except'] = $except;
	return $data;
}, 10, 3 );

add_filter( 'rest_prepare_post_tag', function($data, $item, $request) {
	$cover = '';
	$term_id = $item->term_id;
    if(get_term_meta($item->term_id,'cover',true)) {
        $cover = get_term_meta($item->term_id,'cover',true);
    } else {
		$cover = wp_miniprogram_option('thumbnail');
	}
	if(get_term_meta($item->term_id,'except',true)) {
		$except = false;
	} else {
		$except = true;
	}
	$data->data['cover'] = $cover;
	$data->data['except'] = $except;
	$args = array( 'orderby' => 'rand', 'posts_per_page' => 3, 'tag__in' => $item->term_id, 'data_query' => array( array( 'after' => '1 year ago' ) ) );
	$query = new WP_Query();
	$posts = $query->query( $args );
	$data->data['posts'] = apply_filters( 'rest_posts', $posts );
	return $data;
}, 10, 3 );

add_filter( 'the_content',function ($content) {
	$post_id = get_the_ID();
	if (wp_miniprogram_option('mediaon')) {
		if (get_post_meta( $post_id, 'cover' ,true )) {
			$cover_url = get_post_meta( $post_id, 'cover' ,true );
		} else {
			$cover_url = apply_filters( 'post_thumbnail', $post_id );
		}
		if (get_post_meta( $post_id, 'author' ,true )){
			$media_author = 'name="'.get_post_meta( $post_id, 'author' ,true ).'" ';
		} else {
			$media_author = '';
		}
		if (get_post_meta( $post_id, 'title' ,true )){
			$media_title = 'title="'.get_post_meta( $post_id, 'title' ,true ).'" ';
		} else {
			$media_title = '';
		}
		$video_id = get_post_meta($post_id,'video',true);
		$audio_id = get_post_meta($post_id,'audio',true);
	}
	if (!empty($video_id) && wp_miniprogram_option('qvideo')) {
		$video = apply_filters( 'share_video', $video_id );
		if($video) {
			$video_code = '<p><video '.$media_author.$media_title.' controls="controls" poster="'.$cover_url.'" src="'.$video.'" width="100%"></video></p>';
		} else {
			$video_code = '<p><video '.$media_author.$media_title.' controls="controls" poster="'.$cover_url.'" src="'.$video_id.'" width="100%"></video></p>';
		}
		return $video_code.$content;
	} else {
		return $content;
	}
});

add_filter('category_description', 'wp_delete_html_code');

add_filter( 'user_contactmethods',function($userInfo) {
	$userInfo['gender'] 				= __( '性别' );
	$userInfo['openid'] 				= __( 'OpenID' );
	$userInfo['avatar'] 				= __( '微信头像' );
	$userInfo['city'] 					= __( '所在城市' );
	$userInfo['province'] 				= __( '所在省份' );
	$userInfo['country'] 				= __( '所在国家' );
	$userInfo['language'] 				= __( '系统语言' );
	$userInfo['expire_in'] 				= __( '缓存有效期' );
	return $userInfo;
});

add_action( 'personal_options_update', 'update_miniprogam_platform' );
add_action( 'edit_user_profile_update', 'update_miniprogam_platform' );

function update_miniprogam_platform( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
    	return false;
	update_user_meta( $user_id, 'platform', $_POST['platform'] );
}

add_action( 'show_user_profile', 'add_miniprogam_platform_select' );
add_action( 'edit_user_profile', 'add_miniprogam_platform_select' );

function add_miniprogam_platform_select( $user ) { ?>
<table class="form-table">       
    <tr>
        <th><label for="dropdown">平台用户</label></th>
        <td>
            <?php $selected = get_the_author_meta( 'platform', $user->ID ); ?>
            <select name="platform" id="platform">
				<option value="website" <?php echo ($selected == "website")?  'selected="selected"' : ''; ?>>网站注册</option>
                <option value="wechat" <?php echo ($selected == "wechat")?  'selected="selected"' : ''; ?>>微信小程序</option>
				<option value="tencent" <?php echo ($selected == "tencent")?  'selected="selected"' : ''; ?>>QQ 小程序</option>
            </select>
            <span class="description">用户注册来源所属平台</span>
        </td>
    </tr>
</table>
<?php }

if (wp_miniprogram_option('user_manage')) {

	add_action('admin_head-users.php', function (){
		echo'<style type="text/css">
			.column-user img { float: left; margin-right: 10px; margin-top: 1px; }
			.column-gender { width:80px; }
			.column-openid { width:280px; }
			.column-registered, .column-expire { width:160px; }
			</style>';
	});

	add_filter( 'manage_users_columns', function ( $columns ){ 

		foreach ($columns as $column=>$value) {
			unset($columns[$column]);
		}
		$columns['cb']	= '<input type="checkbox" />';
		$columns["user"] = "用户";
		$columns["gender"] = "性别";
		$columns["openid"] = "OpenID";
		$columns["area"] = "地区";
		$columns["registered"] = "注册时间";
		$columns["expire"] = "缓存有效期";
		return $columns;

	});

	add_action( 'manage_users_custom_column', function ( $value, $column_name, $user_id ) {
		$user = get_userdata( $user_id );
		if('user' == $column_name) {
			$avatar = get_user_meta($user->ID, 'avatar', true);
			$nickname = get_user_meta($user->ID, 'nickname', true);
			if ( $nickname != get_user_meta($user->ID, 'openid', true) ) {
				$user_nickname = $nickname;
			} else {
				$user_nickname = '未知用户';
			}
			if ($avatar) { $value = '<img src="'.$avatar.'" class="avatar avatar-32 photo avatar-default" width="32" height="32" /><strong>'.$user_nickname.'</strong>'; } else { $value = get_avatar( get_the_author_meta( 'ID' ), 32 ).'<strong>'.$user_nickname.'</strong>'; }
		} else if('gender' == $column_name){
			$gender = get_user_meta($user->ID, 'gender', true);
			if($gender == 1) {
				$value = '男';
			} else if ($gender == 2) {
				$value = '女';
			} else {
				$value = '未知';
			}
		} else if ('area' == $column_name){
			$city = get_user_meta($user->ID, 'city', true);
			$province = get_user_meta($user->ID, 'province', true);
			if (!empty($city) && !empty($province)) { $value = $city.' • '.$province; } else { $value = $city.$province; }
		} else if ('openid' == $column_name){
			$value = get_user_meta($user->ID, 'openid', true);
		} else if ('redate' == $column_name){
			$value = get_date_from_gmt($user->user_registered);
		} else if ('expire' == $column_name){
			$value = get_user_meta($user->ID, 'expire_in', true);
		} else {
			$value = "";
		}
		return $value;
	}, 10, 3 );
	
}

if (wp_miniprogram_option('comment_manage'))  {
	add_action('admin_head-edit-comments.php', function (){
		echo'<style type="text/css">
			.column-type { width:80px; }
			</style>';
	});
	add_action( 'admin_menu',function () {
		global $menu;
		$menu[25][0] = '互动';
	});
	add_filter( 'manage_edit-comments_columns', function ( $columns ){
		$columns[ 'type' ] = __( '类型' );
		return $columns;
	});
	add_action( 'manage_comments_custom_column',function  ( $column_name, $comment_id ){
		switch( $column_name ) {
			case "type":
				$type = get_comment_type();
				switch( $type ) {
					case 'fav' :
						echo "收藏";
						break;
					case 'like' :
						echo "点赞";
						break;
					case 'comment' :
						echo "评论";
						break;
					default :
						echo $commenttxt;
				}
		}
	}, 10, 2 );
	add_filter( 'get_comments_number', 'get_wp_comment_count', 10, 1 );
	function get_wp_comment_count () {
		$post_id = get_the_ID();
		$args = array('post_id'=> $post_id, 'type'=>'comment', 'count' => true, 'status'=>'approve');
		$count = get_comments($args);
		return $count;
	}
}

if (wp_miniprogram_option('reupload')) {
	add_filter('wp_handle_upload_prefilter',function ($file) {
		$time = date("YmdHis");
		$file['name'] = $time . "" . mt_rand(1, 100) . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
		return $file;
	});
}

// 屏蔽古腾堡编辑器
if( wp_miniprogram_option('gutenberg') ) {
	add_filter('use_block_editor_for_post_type', '__return_false');
}