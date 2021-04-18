<?php
/**
 * @package   Admin Settings
 */

// Options
add_filter( 'miniprogram_setting_options', function( $options ) {
	
	$options = array(
		'basic-setting'=>[
			'title'=>'小程序授权',
			'summary'=>'<p>WordPress + 小程序用户授权设置</p>',
			'fields'=> [
				'appid'			=>['title'=>'微信小程序 AppId','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'微信小程序 AppId 需要到微信小程序后台获取'],
				'secretkey'		=>['title'=>'微信小程序 AppSecret ','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'微信小程序 AppSecret 需要到微信小程序后台获取'],
				'qq_applets'	=>['title'=>'腾讯 QQ 小程序','type'=>'checkbox','description'=>'是否开启腾讯 QQ 小程序授权设置'],
				'qq_appid'		=>['title'=>'QQ 小程序 AppID','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'QQ 小程序 AppID 需要到 QQ 小程序后台获取'],
				'qq_secret'		=>['title'=>'QQ 小程序 AppSecret ','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'QQ 小程序 AppSecret 需要到 QQ 小程序后台获取'],
				'bd_applets'	=>['title'=>'百度智能小程序','type'=>'checkbox','description'=>'是否开启百度智能小程序授权设置'],
				'bd_appkey'		=>['title'=>'百度小程序 AppKey','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'百度小程序 AppKey 需要到百度智能小程序后台获取'],
				'bd_secret'		=>['title'=>'百度小程序 AppSecret ','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'百度小程序 AppSecret 需要到百度智能小程序后台获取'],
				'tt_applets'	=>['title'=>'今日头条小程序','type'=>'checkbox','description'=>'是否开启今日头条小程序授权设置'],
				'tt_appid'		=>['title'=>'头条小程序 AppId','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'头条小程序 AppId 需要到头条小程序小程序后台获取'],
				'tt_secret'		=>['title'=>'头条小程序 AppSecret ','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'头条小程序 AppSecret 需要到头条小程序小程序后台获取'],
				'use_role'		=>['title'=>'小程序用户组','type'=>'select','options'=>['subscriber'=>'订阅组','contributor'=>'投稿组','wechat'=>'小程序','author'=>'作者组','editor'=>'编辑组']]
			],
		],
		'general-setting'=>[
			'title'=>'常规设置',
			'summary'=>'<p>WordPress + 小程序 API 常规设置</p>',
			'fields'=> [
				'appname'		=>['title'=>'小程序名称','type'=>'text','rows'=>4,'placeholder'=>get_bloginfo("name")],
				'appdesc'		=>['title'=>'小程序描述','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>get_bloginfo('description')],
				'version'		=>['title'=>'小程序版本','type'=>'text','rows'=>4,'placeholder'=>get_bloginfo("version"),'description'=>'小程序版本号,默认留空为 WordPress 程序版本号'],
				'appcover'		=>['title'=>'小程序封面','type'=>'upload','class'=>'regular-text'],
				'debug'			=>['title'=>'API调试模式','type'=>'checkbox','description'=>'是否启用 API 调试模式, 注意: 上线小程序不建议启用'],
				'thumbnail'		=>['title'=>'默认缩略图','type'=>'upload','class'=>'regular-text'],
				'trust_domain'	=>['title'=>'downloadFile合法域名','type'=>'mu-text','class'=>'regular-text','placeholder'=>'微信公众平台小程序 DownloadFile 合法域名'],
				'template_id'	=>['title'=>'评论回复通知','type'=>'text','class'=>'regular-text','description'=>'服务类目：在线教育 - 文章评论回复通知, 关键词：文章标题、评论内容、回复内容、回复时间'],
				'auditing_id'	=>['title'=>'审核通过通知','type'=>'text','class'=>'regular-text','description'=>'服务类目：信息查询 - 审核通过通知, 关键词：审核结果、审核内容、提交时间'],
				'update_tpl_id'	=>['title'=>'资讯更新提醒','type'=>'text','class'=>'regular-text','description'=>'服务类目：信息查询 - 资讯更新提醒, 关键词：新闻类型、新闻标题、新闻摘要']
			],
		],
		'increase-setting'=>[
			'title'=>'功能扩展',
			'summary'=>'<p>WordPress API 功能扩展设置, 需要保存并刷新</p>',
			'fields'=> [
				'update'		=>['title'=>'启用更新提醒','type'=>'checkbox','description'=>'是否开启文章更新内容推送订阅消息通知 [注：需要设置资讯更新提醒模板]'],
				'sticky'		=>['title'=>'推荐文章功能','type'=>'checkbox','description'=>'是否开启小程序文章推荐 [注: 仅针对小程序置顶文章]'],
				'post_content'	=>['title'=>'文章列表内容','type'=>'checkbox','description'=>'是否启用文章列表 content 标签, 默认禁用'],
				'post_picture'	=>['title'=>'文章图像列表','type'=>'checkbox','description'=>'是否开启文章所有图片标签'],
				'prevnext'		=>['title'=>'文章上篇下篇','type'=>'checkbox','description'=>'是否开启文章页上一篇及下一篇数据'],
				'gutenberg'		=>['title'=>'屏蔽古腾堡','type'=>'checkbox','description'=>'是否屏蔽古腾堡编辑器'],
				'mediaon'		=>['title'=>'小程序视频/音频','type'=>'checkbox','description'=>'是否开启小程序文章视频/音频设置选项'],
				'qvideo'		=>['title'=>'解析视频组件','type'=>'checkbox','description'=>'文章自定义字段，仅支持部分腾讯视频地址解析'],
				'reupload'		=>['title'=>'图片自动重命名','type'=>'checkbox','description'=>'是否开启上传图片重命名,注意主题是否有冲突'],
				'advert'		=>['title'=>'广告功能设置','type'=>'checkbox','description'=>'是否开启小程序广告功能设置'],
				'security'		=>['title'=>'内容安全检测','type'=>'checkbox','description'=>'是否开启微信内容安全文本检测'],
				'we_submit'		=>['title'=>'页面内容接入','type'=>'checkbox','description'=>'是否开启微信小程序页面路径推送'],
				'bd_submit'		=>['title'=>'小程序 API 提交','type'=>'checkbox','description'=>'是否开启百度智能小程序 API 提交 [注: 仅支持后台发布文章时提交]']
			],
		],
	);

	if( wp_miniprogram_option('qq_appid') && wp_miniprogram_option('qq_secret') ) {
		$options['general-setting']['fields']['qq_audit_tpl'] = ['title'=>'QQ 审核通知','type'=>'text','class'=>'regular-text','description'=>'搜索一次性订阅消息模板：审核通过提醒 ，关键词及排序：审核结果、备注、通过时间'];
		$options['general-setting']['fields']['qq_reply_tpl'] = ['title'=>'QQ 评论通知','type'=>'text','class'=>'regular-text','description'=>'搜索一次性订阅消息模板：评论回复通知 ，关键词及排序：评论内容、回复内容、回复者'];
		$options['general-setting']['fields']['qq_update_tpl'] = ['title'=>'QQ 更新通知','type'=>'text','class'=>'regular-text','description'=>'搜索一次性订阅消息模板：资讯更新提醒 ，关键词及排序：资讯标题、内容摘要、发布时间、温馨提示'];
	}
	if( wp_miniprogram_option('bd_appkey') && wp_miniprogram_option('bd_secret') ) {
		$options['general-setting']['fields']['bd_reply_tpl'] = ['title'=>'百度评论通知','type'=>'text','class'=>'regular-text','description'=>'选择评论回复消息模板 ID ,参数：回复者,回复内容,回复时间'];
	}

	if( wp_miniprogram_option('advert') ) {
		$options['weadvert-setting'] = [
			'title'=>'微信广告功能',
			'summary'=>'<p>微信小程序广告功能设置,注意填写正确参数</p>',
			'fields'=> [
				'we_i_open'			=>['title'=>'首页广告','type'=>'checkbox','description'=>'是否开启首页广告'],
				'we_i_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
				'we_i_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
				'we_i_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
				
				'we_t_open'			=>['title'=>'列表广告','type'=>'checkbox','description'=>'是否开启列表页广告'],
				'we_t_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
				'we_t_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
				'we_t_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
				
				'we_d_open'			=>['title'=>'详情广告','type'=>'checkbox','description'=>'是否开启详情页广告'],
				'we_d_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
				'we_d_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
				'we_d_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
				
				'we_p_open'			=>['title'=>'页面广告','type'=>'checkbox','description'=>'是否开启单页广告'],
				'we_p_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
				'we_p_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
				'we_p_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数']
			],
		];
		if( wp_miniprogram_option('qq_appid') && wp_miniprogram_option('qq_secret') ) {
			$options['qqadvert-setting'] = [
				'title'=>'QQ 广告功能',
				'summary'=>'<p>QQ 小程序广告功能设置,注意填写正确参数</p>',
				'fields'=> [
					'qq_i_open'			=>['title'=>'首页广告','type'=>'checkbox','description'=>'是否开启首页广告'],
					'qq_i_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'qq_i_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'qq_i_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
					
					'qq_t_open'			=>['title'=>'列表广告','type'=>'checkbox','description'=>'是否开启列表页广告'],
					'qq_t_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'qq_t_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'qq_t_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
					
					'qq_d_open'			=>['title'=>'详情广告','type'=>'checkbox','description'=>'是否开启详情页广告'],
					'qq_d_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'qq_d_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'qq_d_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
					
					'qq_p_open'			=>['title'=>'页面广告','type'=>'checkbox','description'=>'是否开启单页广告'],
					'qq_p_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'qq_p_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'qq_p_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数']
				],
			];
		}
		if( wp_miniprogram_option('bd_appkey') && wp_miniprogram_option('bd_secret') ) {
			$options['bdadvert-setting'] = [
				'title'=>'百度广告功能',
				'summary'=>'<p>百度智能小程序广告功能设置,注意填写正确参数</p>',
				'fields'=> [
					'bd_i_open'			=>['title'=>'首页广告','type'=>'checkbox','description'=>'是否开启首页广告'],
					'bd_i_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'bd_i_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'bd_i_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
					
					'bd_t_open'			=>['title'=>'列表广告','type'=>'checkbox','description'=>'是否开启列表页广告'],
					'bd_t_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'bd_t_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'bd_t_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
					
					'bd_d_open'			=>['title'=>'详情广告','type'=>'checkbox','description'=>'是否开启详情页广告'],
					'bd_d_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'bd_d_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'bd_d_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
					
					'bd_p_open'			=>['title'=>'页面广告','type'=>'checkbox','description'=>'是否开启单页广告'],
					'bd_p_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'bd_p_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'bd_p_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数']
				],
			];
		}
		if( wp_miniprogram_option('tt_appid') && wp_miniprogram_option('tt_secret') ) {
			$options['ttadvert-setting'] = [
				'title'=>'头条广告功能',
				'summary'=>'<p>头条小程序广告功能设置,注意填写正确参数</p>',
				'fields'=> [
					'tt_i_open'			=>['title'=>'首页广告','type'=>'checkbox','description'=>'是否开启首页广告'],
					'tt_i_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'tt_i_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'tt_i_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
					
					'tt_t_open'			=>['title'=>'列表广告','type'=>'checkbox','description'=>'是否开启列表页广告'],
					'tt_t_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'tt_t_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'tt_t_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
					
					'tt_d_open'			=>['title'=>'详情广告','type'=>'checkbox','description'=>'是否开启详情页广告'],
					'tt_d_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'tt_d_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'tt_d_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
					
					'tt_p_open'			=>['title'=>'页面广告','type'=>'checkbox','description'=>'是否开启单页广告'],
					'tt_p_type'			=>['title'=>'广告类型','type'=>'select','options'=>['unit'=>'流量主','app'=>'小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
					'tt_p_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
					'tt_p_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数']
				],
			];
		}
	}
	
	return $options;
	
});
