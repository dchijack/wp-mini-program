<?php
/*
 * Subscribe Message Template
 */

function wp_insert_miniprogram_subscribe( $data ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'applets_subscribe_user';
	$result = $wpdb->insert(
		$table_name,
		$data
	);
	if( $result ) {
		return $wpdb->insert_id;
	} else {
		return false;
	}
}

function wp_update_miniprogram_subscribe( $args, $data ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'applets_subscribe_user';
	$result = $wpdb->update(
		$table_name,
		$data,
		$args
	);
	return $result;
}

function wp_insert_miniprogram_subscribe_tracks( $data ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'applets_subscribe_message';
	$result = $wpdb->insert(
		$table_name,
		$data
	);
	if( $result ) {
		return $wpdb->insert_id;
	} else {
		return false;
	}
}

function get_miniprogram_subscribe_recent_task(  ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'applets_subscribe_message';
	$sql = "SELECT * FROM $table_name ORDER BY date DESC LIMIT 1";
	$result = $wpdb->get_row( $sql );
	return $result;
}

function get_miniprogram_subscribe_by_utplid( $openid, $template ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'applets_subscribe_user';
    $where = $wpdb->prepare("AND openid = %s AND template = %s ", esc_sql($openid), esc_sql($template));
    $sql = "SELECT * FROM $table_name WHERE $where";
    $sql = @str_replace('WHERE AND','WHERE', $sql);
    $data = $wpdb->get_row( $sql );
    return $data;
}

function get_miniprogram_user_subscribe_counts( $openid = '', $program = '' ) {
	global $wpdb;
	$where = "";
	$table_name = $wpdb->prefix . 'applets_subscribe_user';
	if( $openid ) {
		$where .= $wpdb->prepare("AND openid = %s",esc_sql($openid));
	}
	if( $program ) {
		$where .= $wpdb->prepare("AND program = %s",esc_sql($program));
	}
	if( $where ) {
		$sql = "SELECT SUM( count ) FROM $table_name WHERE $where";
		$sql = @str_replace('WHERE AND','WHERE',$sql);
	} else {
		$sql = "SELECT SUM( count ) FROM $table_name";
	}
	$count = (int)$wpdb->get_var( $sql );
	return $count;
}

function get_miniprogram_notice_template_counts( $template = '', $program = '' ) {
	global $wpdb;
	$where = "";
	$table_name = $wpdb->prefix . 'applets_subscribe_message';
	if( $openid ) {
		$where .= $wpdb->prepare("AND template = %s",esc_sql($template));
	}
	if( $program ) {
		$where .= $wpdb->prepare("AND program = %s",esc_sql($program));
	}
	if( $where ) {
		$sql = "SELECT COUNT( * ) FROM $table_name WHERE $where";
		$sql = @str_replace('WHERE AND','WHERE',$sql);
	} else {
		$sql = "SELECT COUNT( * ) FROM $table_name";
	}
	$count = (int)$wpdb->get_var( $sql );
	return $count;
}

function get_miniprogram_subscriber_openid_by_tpl( $template ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'applets_subscribe_user';
	$where = $wpdb->prepare("AND count > 0 AND template = %s ",esc_sql($template));
	$order = "ORDER BY date ASC";
	$sql = "SELECT openid FROM $table_name WHERE $where $order";
	$sql = @str_replace('WHERE AND','WHERE',$sql);
	$result = $wpdb->get_results($sql);
	return $result;
}

function get_miniprogram_subscriber_count_by_tpl( $template = '', $program = '' ) {
	global $wpdb;
	$where = "";
	$table_name = $wpdb->prefix . 'applets_subscribe_user';
	if( $template ) {
		$where .= $wpdb->prepare("AND template = %s",esc_sql($template));
	}
	if( $program ) {
		$where .= $wpdb->prepare("AND program = %s",esc_sql($program));
	}
	$order = "GROUP BY openid";
	if( $where ) {
		$sql = "SELECT COUNT( * ) FROM $table_name WHERE $where $order";
		$sql = @str_replace('WHERE AND','WHERE',$sql);
	} else {
		$sql = "SELECT COUNT( * ) FROM $table_name $order";
	}
	$count = (int)$wpdb->get_var( $sql );
	return $count;
}

function get_miniprogram_notice_success_count( $date = '', $program = '' ) {
	global $wpdb;
	date_default_timezone_set('PRC');
	$where = "";
	$table_name = $wpdb->prefix . 'applets_subscribe_message';
	if( $date ) {
		$datetime = date( "Y-m-d", strtotime( $date ) );;
		$where .= $wpdb->prepare("AND year(date) = %s AND month(date) = %s AND day(date) = %s ", esc_sql( substr( $datetime, 0, 4 ) ), esc_sql( substr( $datetime, 5, 2 ) ), esc_sql( substr( $datetime, 8, 2 ) ));
	}
	if( $program ) {
		$where .= $wpdb->prepare("AND program = %s",esc_sql($program));
	}
	$sql = "SELECT COUNT( * ) FROM $table_name WHERE errcode = 0 $where";
	$sql = @str_replace('WHERE AND','WHERE',$sql);
	$count = (int)$wpdb->get_var( $sql );
	return $count;
	
}

function get_miniprogram_today_notice_count( $program = '' ) {
	global $wpdb;
	$where = "";
	date_default_timezone_set('PRC');
	$table_name = $wpdb->prefix . 'applets_subscribe_message';
	if( $program ) {
		$where .= $wpdb->prepare("AND program = %s",esc_sql($program));
	}
	$sql = "SELECT COUNT( * ) FROM $table_name WHERE year(date)=year(now()) AND month(date)=month(now()) AND day(date)=day(now()) $where";
	$sql = @str_replace('WHERE AND','WHERE',$sql);
	$count = (int)$wpdb->get_var( $sql );
	return $count;
}

function get_miniprogram_subscribe_notice_tracks( $args = '' ) {
	global $wpdb;
	date_default_timezone_set('PRC');
	$table_name = $wpdb->prefix . 'applets_subscribe_message';
	$query      = "";
    $where      = "";
    $orderby    = "";
    $limits     = "";
	$found_rows = "";
	if( !empty( $args ) ) {
		$args   = wp_parse_args( $args );
	}
	if( isset($args['id']) ) {
		$query .= $wpdb->prepare( "AND id = %s ", esc_sql( $args['id'] ) );
	}
	if( isset($args['task']) ) {
		$query .= $wpdb->prepare( "AND task = %s ", esc_sql( $args['task'] ) );
	}
	if( isset($args['openid']) ) {
		$query .= $wpdb->prepare( "AND openid = %s ", esc_sql( $args['openid'] ) );
	}
	if( isset($args['template']) ) {
		$query .= $wpdb->prepare( "AND template = %s ", esc_sql( $args['template'] ) );
	}
	if( isset($args['program']) ) {
		$query .= $wpdb->prepare( "AND program = %s ", esc_sql( $args['program'] ) );
	}
	if( isset($args['errcode']) ) {
		$query .= $wpdb->prepare( "AND errcode = %s ", esc_sql( $args['errcode'] ) );
	}
	if( isset($args['date']) ) {
		$datetime = date( "Y-m-d", strtotime( $args['date'] ) );
		$query .= $wpdb->prepare( "AND year(date) = %s AND month(date) = %s AND day(date) = %s ", esc_sql( substr( $datetime, 0, 4 ) ), esc_sql( substr( $datetime, 5, 2 ) ), esc_sql( substr( $datetime, 8, 2 ) ) );
	}
	if( isset($args['s']) ) {
		$query .= $wpdb->prepare( "AND openid LIKE '%%%s%%' OR template LIKE '%%%s%%' ", esc_sql( $args['s'] ), esc_sql( $args['s'] ) );
	}
	if( isset($args['offset']) && isset($args['per_page']) ) {
		$offset = (int)$args['offset'];
		$per_page = (int)$args['per_page'];
		if( $offset && $per_page ) {
			$limits = "LIMIT $offset, $per_page";
		} else if( !$offset && $per_page ) {
			$limits = "LIMIT $per_page";
		}
	}
	if( isset($args['orderby']) ) {
		$order = isset($args['order']) ? $args['order'] : "DESC";
		$orderby = "ORDER BY {$query_vars['orderby']} $order";
	} else {
		$orderby = "ORDER BY date DESC";
	}
	if( $query ) {
		$where = "WHERE $query";
	}
	if( $limits ) {
		$found_rows = "SQL_CALC_FOUND_ROWS";
	}
	$sql    = "SELECT $found_rows * FROM $table_name $where $orderby $limits";
	$sql    = @str_replace( 'WHERE AND', 'WHERE', $sql );
	$result = $wpdb->get_results( $sql );
	return $result;
}

function get_miniprogram_subscribe_notice_count( $args = '' ) {
	global $wpdb;
	date_default_timezone_set('PRC');
	$table_name = $wpdb->prefix . 'applets_subscribe_message';
	$query      = "";
    $where      = "";
	if( !empty( $args ) ) {
		$args   = wp_parse_args( $args );
	}
	if( isset($args['id']) ) {
		$query .= $wpdb->prepare( "AND id = %s ", esc_sql( $args['id'] ) );
	}
	if( isset($args['task']) ) {
		$query .= $wpdb->prepare( "AND task = %s ", esc_sql( $args['task'] ) );
	}
	if( isset($args['openid']) ) {
		$query .= $wpdb->prepare( "AND openid = %s ", esc_sql( $args['openid'] ) );
	}
	if( isset($args['template']) ) {
		$query .= $wpdb->prepare( "AND template = %s ", esc_sql( $args['template'] ) );
	}
	if( isset($args['program']) ) {
		$query .= $wpdb->prepare( "AND program = %s ", esc_sql( $args['program'] ) );
	}
	if( isset($args['errcode']) ) {
		$query .= $wpdb->prepare( "AND errcode = %s ", esc_sql( $args['errcode'] ) );
	}
	if( isset($args['date']) ) {
		$datetime = date( "Y-m-d", strtotime( $args['date'] ) );
		$query .= $wpdb->prepare( "AND year(date) = %s AND month(date) = %s AND day(date) = %s ", esc_sql( substr( $datetime, 0, 4 ) ), esc_sql( substr( $datetime, 5, 2 ) ), esc_sql( substr( $datetime, 8, 2 ) ) );
	}
	if( isset($args['s']) ) {
		$query .= $wpdb->prepare( "AND openid LIKE '%%%s%%' OR template LIKE '%%%s%%' ", esc_sql( $args['s'] ), esc_sql( $args['s'] ) );
	}
	if( $query ) {
		$where = "WHERE $query";
		$where = @str_replace( 'WHERE AND', 'WHERE', $where );
	}
	$sql = "SELECT COUNT( * ) FROM $table_name $where";
	$counts = (int) $wpdb->get_var( $sql );
	return $counts;
}