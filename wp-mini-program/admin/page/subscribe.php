<?php

if ( !defined( 'ABSPATH' ) ) exit;

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MP_Subscribe_Message_Task extends WP_List_Table {

    function __construct( ) {
        global $page;
        parent::__construct(
            array(
                'singular'  => __( 'subscribe', 'imahui' ),
                'plural'    => __( 'subscribe', 'imahui' ),
                'ajax'      => false
            )
        );
    }

    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'id':
            case 'task':
            case 'openid':
            case 'errcode':
            case 'pages':
            case 'msg':
            case 'date':
                return $item[ $column_name ];
            default:
                return print_r( $item, true );
        }

    }

    function get_columns( ) {
        $columns = array(
            'id'        => __('ID','imahui'),
            'task'      => __('任务ID','imahui'),
            'openid'    => __('OpenID','imahui'),
            'errcode'   => __('错误码','imahui'),
            'pages'     => __('页面','imahui'),
            'msg'       => __('错误信息','imahui'),
            'date'      => __('时间','imahui')
        );
        return $columns;
    }

    function prepare_items( $val = '' ) {
        $per_page = 10;
        $columns = $this->get_columns();
        $hidden = array();
        $this->_column_headers = array( $columns, $hidden );
        $data = array();
        $results = get_miniprogram_subscribe_notice_tracks( $val );

        if( $results ) {
            foreach ($results as $res) {
                $data[] = array(
                    'id'         => $res->id,
                    'task'       => $res->task,
                    'openid'     => $res->openid,
                    'errcode'    => $res->errcode,
                    'pages'      => $res->pages,
                    'msg'        => mp_subscribe_errcode_msg( $res->errcode ),
                    'date'       => $res->date
                );
            }
        }

        $current_page = $this->get_pagenum();
        $total_items = get_miniprogram_subscribe_notice_count( $val );
        $data = array_slice( $data,( ($current_page-1)*$per_page ), $per_page );

        $this->items = $data;
        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total_items/$per_page )
            )
        );

    }

}

function miniprogram_subscribe_message_count( ) {
    $subscribers   = get_miniprogram_subscriber_count_by_tpl( );
    $wesubscribe   = get_miniprogram_subscriber_count_by_tpl( '', 'WeChat' );
    $qqsubscribe   = get_miniprogram_subscriber_count_by_tpl( '', 'QQ' );
    $counts        = get_miniprogram_user_subscribe_counts( );
    $wesubscribes  = get_miniprogram_user_subscribe_counts( '', 'WeChat' );
    $qqsubscribes  = get_miniprogram_user_subscribe_counts( '', 'QQ' );
    $wetoday_push  = get_miniprogram_subscribe_notice_count( 'program=WeChat&date='.date('Y-m-d') );
    $wetoday       = get_miniprogram_notice_success_count( date('Y-m-d'), 'WeChat' );
    $wesuccess     = $wetoday_push ? round($wetoday/$wetoday_push*100,2) : 0;
    $wetotal_push  = get_miniprogram_subscribe_notice_count( 'program=WeChat' );
    $qqtoday_push  = get_miniprogram_subscribe_notice_count( 'program=QQ&date='.date('Y-m-d') );
    $qqtoday       = get_miniprogram_notice_success_count( date('Y-m-d'), 'QQ' );
    $qqsuccess     = $qqtoday_push ? round($wetoday/$qqtoday*100,2) : 0;
    $qqtotal_push  = get_miniprogram_subscribe_notice_count( 'program=QQ' );
    if( ! wp_miniprogram_option('qq_appid') || ! wp_miniprogram_option('qq_secret') ) {
        $total_success = get_miniprogram_notice_success_count( '', 'WeChat');
        $total_round   = $wetotal_push ? round($total_success/$total_round*100,2) : 0;
    }
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">订阅消息</h1>
        <p>小程序订阅消息,丸子小程序订阅插件(<a href="https://www.weitimes.com/doc/guide/530.html" target="_blank">点击查看帮助</a>)，丸子小程序专业版，<a href="https://www.weitimes.com/" target="_blank">点击这里查看详情</a></p>
        <hr class="wp-header-end">
        <div class="el-card">
            <h2 class="el-card__header"><span class="dashicons dashicons-info"></span> 订阅信息</h2>
            <div class="el-card__body">
                <div class="el-col">
                    <span>订阅用户</span><h1><?php echo $subscribers; ?></h1>
                </div>
                <div class="el-col">
                    <span>微信用户</span><h1><?php echo $wesubscribe; ?></h1>
                </div>
                <?php if( wp_miniprogram_option('qq_appid') && wp_miniprogram_option('qq_secret') ) { ?>
                <div class="el-col">
                    <span>QQ 用户</span><h1><?php echo $qqsubscribe; ?></h1>
                </div>
                <div class="el-col">
                    <span>订阅次数</span><h1><?php echo $counts; ?></h1>
                </div>
                <?php } else { ?>
                <div class="el-col">
                    <span>成功推送</span><h1><?php echo $total_success; ?></h1>
                </div>
                <div class="el-col">
                    <span>总成功率</span><h1><?php echo $total_round.'%'; ?></h1>
                </div>
                <?php } ?>
            </div>
        </div>
        <div class="el-card">
            <h2 class="el-card__header"><span class="dashicons dashicons-info"></span> 微信订阅</h2>
            <div class="el-card__body">
                <div class="el-col">
                    <span>订阅次数</span><h1><?php echo $wesubscribes; ?></h1>
                </div>
                <div class="el-col">
                    <span>今日推送</span><h1><?php echo $wetoday_push; ?></h1>
                </div>
                <div class="el-col">
                    <span>今日成功率</span><h1><?php echo $wesuccess; ?>%</h1>
                </div>
                <div class="el-col">
                    <span>全部推送</span><h1><?php echo $wetotal_push; ?></h1>
                </div>
            </div>
        </div>
        <?php if( wp_miniprogram_option('qq_appid') && wp_miniprogram_option('qq_secret') ) { ?>
        <div class="el-card">
            <h2 class="el-card__header"><span class="dashicons dashicons-info"></span> QQ 订阅</h2>
            <div class="el-card__body">
                <div class="el-col">
                    <span>订阅次数</span><h1><?php echo $qqsubscribes; ?></h1>
                </div>
                <div class="el-col">
                    <span>今日推送</span><h1><?php echo $qqtoday_push; ?></h1>
                </div>
                <div class="el-col">
                    <span>今日成功率</span><h1><?php echo $qqsuccess; ?>%</h1>
                </div>
                <div class="el-col">
                    <span>全部推送</span><h1><?php echo $qqtotal_push; ?></h1>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
<?php }

function miniprogram_subscribe_message_task_table( ) {
    $data = new MP_Subscribe_Message_Task();
    $data->prepare_items( $_GET );
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">任务列表</h1>
        <hr class="wp-header-end">
        <form id="subscribe-message-filter" method="get">
            <input type="hidden" name="page" value="<?php echo trim($_GET['page']); ?>" />
            <?php $data->display(  ); ?>
        </form>
    </div>
    <style>
    th.column-id, td.column-id, th.column-task, td.column-task, th.column-errcode, td.column-errcode {
      width:8%;
    }
    th.column-date, td.column-date {
      width:15%!important;
    }
    </style>
<?php }