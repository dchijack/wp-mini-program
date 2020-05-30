<?php
/*
 * WordPress Utils Class For Router
 */
 
if ( !defined( 'ABSPATH' ) ) exit;

add_filter( 'post_thumbnail', function($post_id) {
	$thumbnails = get_post_meta($post_id, 'thumbnail', true); // 获取自定义缩略图
	if(!empty($thumbnails)) {
		return $thumbnails;
	} else if(has_post_thumbnail($post_id)) {
		$thumbnail_id = get_post_thumbnail_id($post_id); // 获取特色图像 ID
		if($thumbnail_id) {
			$attachment = wp_get_attachment_image_src($thumbnail_id, 'full');
			$thumbnails = $attachment[0];
			return $thumbnails;
		} else {
			$thumbnail_code = get_the_post_thumbnail( $post_id, 'full' ); // 获取特色图像 HTML 代码
			$thumbnail_src = '/src=\"(.*?)\"/';
            if (preg_match($thumbnail_src, $thumbnail_code, $thumbnail)) {
				$thumbnails = $thumbnail[1];
				return $thumbnails;
            } else {
				$thumbnails = wp_miniprogram_option('thumbnail'); // 指定默认链接
				return $thumbnails;
			}
		}
	} else {
		$post = get_post($post_id);
		$post_content = $post->post_content;
			
		preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
		if( $matches && isset($matches[1]) && isset($matches[1][0]) ){     
			$thumbnails = $matches[1][0];
		}
			
		if(!empty($thumbnails)) {
			return $thumbnails;
		} else {
			$thumbnails = wp_miniprogram_option('thumbnail'); // 指定默认链接
			return $thumbnails;
		}
			
	}
});

add_filter( 'post_images', function($post_id) {
	if($post_id){
		$the_post       	= get_post($post_id);
		$post_content   	= $the_post->post_content;
	} 
	preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
	$images = array();
	if($matches && isset($matches[1])) {
		$_images=$matches[1]; 
		for($i=0; $i<count($matches[1]);$i++) {
			$image_url['id'] = $i;
			$image_url['link'] = $matches[1][$i];
			$images[] = $image_url;
		}
	}
	return $images;
});

add_filter( 'tencent_video', function($url) {
	if( filter_var($url, FILTER_VALIDATE_URL) ) { 
		if(preg_match('#https://v.qq.com/x/page/(.*?).html#i',$url, $m)) {
			$vids = $m[1];
		} else if(preg_match('#https://v.qq.com/x/cover/.*/(.*?).html#i',$url, $m)) {
			$vids = $m[1];
		} else {
			$vids = $url;
		}
	} else {
		$vids = $url;
	}
	if($vids) {
		if(strlen($vids) > 20) {
			return $url;
		}
		$url = 'https://vv.video.qq.com/getinfo?vid='.$vids.'&platform=101001&charge=0&otype=json';
		$remote = wp_remote_get( $url );
		$response = wp_remote_retrieve_body( $remote );
		$response = substr($response,13,-1);
		$response = json_decode($response,true);
		$response = $response['vl']['vi'][0];
		$mp4file  = $response['fn'];
		$mp4keys  = $response['fvkey'];
		//$ti		= $res['ti'];
		$mp4	= 'https://ugcws.video.gtimg.com/'.$mp4file.'?vkey='.$mp4keys;
		return $mp4;
	}
});

add_filter( 'the_video_content', function($content) {
	preg_match("/https\:\/\/v\.qq\.com\/x\/page\/(.*?)\.html/",$content, $qvideo);
	preg_match("/https\:\/\/v\.qq\.com\/cover\/(.*?)\/(.*?)\.html/",$content, $tencent);
	preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($content), $matches);
	$thumbnails = "";
	if( $matches && isset($matches[1]) && isset($matches[1][0]) ){
		$thumbnails = 'poster="'.$thumbnails.'" ';
	}
	if($qvideo || $tencent) {
		$url = $qvideo?$qvideo[0]:$tencent?$tencent[0]:'';
		if($url) {
			$video = apply_filters( 'tencent_video', $url );
		} else {
			$video = '';
		}
		if($video) {
			$contents = preg_replace('~<video (.*?)></video>~s','<video '.$thumbnails.'src="'.$video.'" controls="controls" width="100%"></video>',$content);
			return $contents;
		} else {
			return $content;
		}
	} else {
		return $content;
	}
});

add_filter( 'miniprogram_commented', function( $post_id, $user_id, $type ) {
	$user = get_user_by('ID',$user_id);
	if(!$user) {
		return false;
	}
	$args = array('post_id' => $post_id, 'type__in' => array( $type ), 'user_id' => $user_id, 'count' => true, 'status' => 'approve');
	$count = get_comments($args);
	return $count ? true : false;
}, 10, 3 );

add_filter( 'comment_type_count', function( $post_id, $type ) {
	$args = array('post_id'=> $post_id,'type__in'=>array( $type ),'count' => true,'status'=>'approve');
	$counts = get_comments($args);
	if(!update_post_meta($post_id, $type.'s', $counts)) {
		add_post_meta($post_id, $type.'s', 0, true);
	}
	return $counts?$counts:0;
}, 10, 2 );

add_filter( 'comment_type_list', function( $post_id, $type ) {
	$args = array('post_id'=> $post_id,'type__in'=>array( $type ),'number'=>10,'status'=>'approve');
	$comments = get_comments($args);
	$authors = array();
	foreach ( $comments as $comment ) {
		$_data = array();
		$user_id = $comment->user_id;
		$author_avatar = get_user_meta( $user_id, 'avatar', true );
		$_data["id"] = $user_id;
		$_data["name"] = get_the_author_meta('nickname',$user_id);
		if ($author_avatar) {
			$_data["avatar"] = $author_avatar;
		} else {
			$_data["avatar"] = get_avatar_url($user_id);
		}
		$authors[] = $_data;
	}
	return $authors;
}, 10, 2 );

add_filter( 'rest_posts', function( $posts, $request ) {
	$data = array();
	foreach ( $posts as $post ) {
		$_data = array();
		$post_id = $post->ID;
		$post_date = $post->post_date;
		$author_id = $post->post_author;
		$post_type = $post->post_type;
		$post_format = get_post_format( $post_id );
		$author_avatar = get_user_meta( $author_id, 'avatar', true );
		$taxonomies = get_object_taxonomies( $post_type );
		$thumbnail = apply_filters( 'post_thumbnail', $post_id );
		$post_title = $post->post_title;
		$post_excerpt = $post->post_excerpt;
		$post_content = $post->post_content;
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
		$_data["week"] = get_wp_post_week($post_date);
		$_data["format"] = $post_format?$post_format:'standard'; 
		$_data["type"] = $post_type;
		if( get_post_meta( $post_id, "source" ,true ) ) {
			$_data["meta"]["source"] = get_post_meta( $post_id, "source" ,true );
		}
		$_data["meta"]["thumbnail"] = $thumbnail;
		$_data["meta"]["views"] = (int)get_post_meta( $post_id, "views" ,true );
		$meta = apply_filters( 'custom_meta', $meta = array() );
		if ($meta) {
			foreach ( $meta as $meta_key ) {
				$_data["meta"][$meta_key] = get_post_meta( $post_id, $meta_key ,true );
			}
		}
		$_data["comments"] = apply_filters( 'comment_type_count', $post_id, 'comment' );
		$_data["isfav"] = apply_filters( 'miniprogram_commented', $post_id, $user_id, 'fav' );
		$_data["favs"] = apply_filters( 'comment_type_count', $post_id, 'fav' );
		$_data["islike"] = apply_filters( 'miniprogram_commented', $post_id, $user_id, 'like' );
		$_data["likes"] = apply_filters( 'comment_type_count', $post_id, 'like' );
		$_data["author"]["id"] = $author_id;
		$_data["author"]["name"] = get_the_author_meta('nickname',$author_id);
		if ($author_avatar) {
			$_data["author"]["avatar"] = $author_avatar;
		} else {
			$_data["author"]["avatar"] = get_avatar_url($author_id);
		}
		$_data["author"]["description"] = get_the_author_meta('description',$author_id);
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
		$_data["title"]["rendered"]  = html_entity_decode($post_title);
		if ($post_excerpt) {
			$_data["excerpt"]["rendered"] = html_entity_decode(wp_trim_words( wp_strip_all_tags( $post_excerpt ), 100, '...' ));
		} else {
			$_data["excerpt"]["rendered"] = html_entity_decode(wp_trim_words( wp_strip_all_tags( $post_content ), 100, '...' ));
		}
		if ( wp_miniprogram_option("post_content") ) { 
			$_data["content"]["rendered"] = apply_filters( 'the_content', $post_content );
		 }
		if ( wp_miniprogram_option("post_picture") ) {
			$_data["pictures"] = apply_filters( 'post_images', $post_id );
		}
		$data[] = $_data;
	}
	return $data;
}, 10, 2 );

add_filter( 'reply_comments', function( $post_id, $reply, $parent ) {
	$args = array(
		'post_id' => $post_id,
		'type__in' => array('comment'),
		'status' => 'approve',
		'parent' => $parent,
		'number' => 10,
		"orderby" => 'comment_date',
		"order" => 'DESC'
	);
	$comments = get_comments($args);
	$data = array();
	foreach ($comments as $comment) {
		$comment_id = $comment->comment_ID;
		$user_id = $comment->user_id;
		$user_name = $comment->comment_author;
		$date = $comment->comment_date;
		$content = $comment->comment_content;
		$parent = $comment->comment_parent;
		$avatar = get_user_meta($user_id, 'avatar', true);
		$_data["id"] = $comment_id;
		$_data["author"]["id"] = $user_id;
		$_data["author"]["name"] = ucfirst($user_name);
		if ($avatar) {
			$_data["author"]["avatar"] = $avatar;
		} else {
			$_data["author"]["avatar"] = get_avatar_url($user_id);
		}
		$_data["date"] = datetime_before($date);
		$_data["content"] = $content;
		$_data["parent"] = $parent;
		$_data["reply_to"] = ucfirst($reply);
		$_data["reply"] = apply_filters( 'reply_comments', $post_id, $user_name, $comment_id );
		$data[] =$_data;
	}	
	return $data;
}, 10, 3 );

add_filter( 'security_msgSecCheck', function($content) {
	$access_token = MP_Auth::we_miniprogram_access_token( );
	$token = isset($token['access_token']) ? $token['access_token'] : '';
	if( !$token ) {
		return new WP_Error( 'error', 'access token 错误' , array( 'status' => 403 ) );
	}
	$url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token='.$access_token;
    $header = array(
        "Content-Type: application/json;charset=UTF-8"
    );
	$msg = wp_strip_all_tags( $content );
	$body = json_encode( array( "content" => $msg ) );
	$args = array(
		'method'  => 'POST',
		'body' 	  => ''.$body.'',
		'headers' => $header,
		'cookies' => array( )
	);
	$response = wp_remote_post( $url, $args );
	$result = wp_remote_retrieve_body( $response );
	return json_decode( $result );
} );

add_filter( 'mp_we_submit_pages', function($post_id) {
	$post_type = get_post_type( $post_id );
	$session = MP_Auth::we_miniprogram_access_token( );
	$access_token = isset($session['access_token']) ? $session['access_token'] : '';
	if( $access_token ) {
		$url = 'https://api.weixin.qq.com/wxa/search/wxaapi_submitpages?access_token='.$access_token;
		if( $post_type == 'post' ) {
			$path = 'pages/detail/detail';
		} else if( $post_type == 'page' ) {
			$path = 'pages/page/page';
		} else {
			$path = '';
		}
		if( $path ) {
			$pages = array( 'path' => $path, 'query' => 'id='.$post_id );
			$args = array( 'body' => json_encode( array('pages' => array( $pages ) ) ) );
			$response = wp_remote_post( $url, $args );
			if ( is_wp_error( $response ) ) {
				return array( "status" => 404, "code" => "error", "message" => "数据请求错误" );
			} else {
				return json_decode( $response['body'], true );
			}
		} else {
			return array( "status" => 404, "code" => "error", "message" => "页面路径错误" );
		}
	}
} );

add_filter( 'mp_bd_submit_pages', function($post_id) {
	$post_type = get_post_type( $post_id );
	$session = MP_Auth::bd_miniprogram_access_token( );
	$access_token = isset($session['access_token']) ? $session['access_token'] : '';
	if( $access_token ) {
        $url = 'https://openapi.baidu.com/rest/2.0/smartapp/access/submitsitemap/api?access_token='.$access_token;
        if( $post_type == 'post' ) {
            $path = 'pages/detail/detail?id='.$post_id;
        } else if( $post_type == 'page' ) {
            $path = 'pages/page/page?id='.$post_id;
        } else {
            $path = '';
        }
        if( $path ) {
            $header = array(
                "Content-Type" => "application/x-www-form-urlencoded"
            );
            $body = array(
                "type" => 0,
                "url_list" => $path
            );
            $args = array(
                'method'  => 'POST',
                'headers' => $header,
                'body' 	  => http_build_query( $body )
            );
            $response = wp_remote_post( $url, $args );
            if ( is_wp_error( $response ) ) {
                return array( "status" => 400, "code" => "error", "message" => "数据请求错误" );
            } else {
                $res = json_decode( $response['body'], true );
                if( $res['errno'] === 0 ) {
                    if( !update_post_meta( $post_id, '_api_submited', 'success' ) ) {
                        add_post_meta($post_id, '_api_submited', 'success', true); 
                    }
                }
                return $res;
            }
        } else {
            return array( "status" => 400, "code" => "error", "message" => "页面路径错误" );
        }
    }
});