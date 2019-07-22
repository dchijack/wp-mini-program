<?php
/**
 * @package   Admin Settings
 */

// Options
add_filter( 'miniprogram_setting_options', function( $options ) {
	
	$options = array(
		'basic-setting'=>[
			'title'=>'微信授权',
			'summary'=>'<p>WordPress + 微信小程序用户授权设置</p>',
			'fields'=> [
				'appid'			=>['title'=>'微信小程序 AppId','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'微信小程序 AppId 需要到微信小程序后台获取'],
				'secretkey'		=>['title'=>'微信小程序 AppSecret ','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'微信小程序 AppSecret 需要到微信小程序后台获取'],
				'qq_applets'	=>['title'=>'腾讯 QQ 小程序','type'=>'checkbox','description'=>'是否开启腾讯 QQ 小程序授权设置'],
				'qq_appid'		=>['title'=>'QQ 小程序 AppID','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'QQ 小程序 AppID 需要到 QQ 小程序后台获取'],
				'qq_secret'		=>['title'=>'QQ 小程序 AppSecret ','type'=>'text','class'=>'regular-text','rows'=>4,'placeholder'=>'QQ 小程序 AppSecret 需要到 QQ 小程序后台获取'],
				'use_role'		=>['title'=>'微信用户组','type'=>'select','options'=>['subscriber'=>'订阅组','contributor'=>'投稿组','wechat'=>'小程序','author'=>'作者组','editor'=>'编辑组']],
			],
		],
		'general-setting'=>[
			'title'=>'常规设置',
			'summary'=>'<p>WordPress + 小程序 API 常规设置</p>',
			'fields'=> [
				'appname'		=>['title'=>'小程序名称','type'=>'text','rows'=>4,'description'=>'小程序名称'],
				'appdesc'		=>['title'=>'小程序描述','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'小程序首页分享标题描述'],
				'version'		=>['title'=>'小程序版本','type'=>'text','rows'=>4,'description'=>'小程序版本号,默认留空为 WordPress 程序版本号'],
				'formats'		=>['title'=>'文章格式类型','type'=>'mu-check','options'=>['aside'=>'日志','gallery'=>'相册','link'=>'链接','image'=>'图像','quote'=>'引用','status'=>'状态','video'=>'视频','audio'=>'语音','chat'=>'聊天']],
				'thumbnail'		=>['title'=>'默认缩略图','type'=>'upload','class'=>'regular-text'],
				'template_id'	=>['title'=>'回复评论模板 ID','type'=>'text','class'=>'regular-text','description'=>'回复评论消息模板 ID ,参数：回复者,回复内容,回复时间'],
				'trust_domain'	=>['title'=>'downloadFile合法域名','type'=>'textarea','class'=>'regular-text','description'=>'把微信公众平台小程序的downloadFile合法域名填写这里,每个域名单独一行'],
			],
		],
		'optimize-setting'=>[
			'title'=>'优化设置',
			'summary'=>'<p>WordPress API 数据开启/关闭选项</p>',
			'fields'=> [
				'sticky'		=>['title'=>'自定义置顶','type'=>'checkbox','description'=>'是否开启自定义文章置顶 [注: 仅针对小程序置顶文章]'],
				'post_content'	=>['title'=>'文章列表内容','type'=>'checkbox','description'=>'是否启用文章列表 content 标签, 默认禁用'],
				'user_manage'	=>['title'=>'自定义用户列表','type'=>'checkbox','description'=>'是否启用自定义用户管理列表'],
				'comment_manage'=>['title'=>'自定义评论列表','type'=>'checkbox','description'=>'是否启用自定义评论管理列表'],
				'rest_other'	=>['title'=>'WP REST API','type'=>'checkbox','description'=>'是否禁用不需要使用的 WP REST API'],
				'post_picture'	=>['title'=>'文章图像列表','type'=>'checkbox','description'=>'是否开启文章所有图片标签'],
				'gutenberg'		=>['title'=>'屏蔽古腾堡','type'=>'checkbox','description'=>'是否屏蔽古腾堡编辑器'],
			],
		],
		'increase-setting'=>[
			'title'=>'功能扩展',
			'summary'=>'<p>WordPress API 功能扩展设置,需要保存并刷新</p>',
			'fields'=> [
				'prevnext'		=>['title'=>'文章上下篇','type'=>'checkbox','description'=>'是否开启文章输出上一篇及下一篇'],
				'mediaon'		=>['title'=>'小程序视频/音频','type'=>'checkbox','description'=>'是否开启小程序视频/音频内容'],
				'qvideo'		=>['title'=>'解析视频组件','type'=>'checkbox','description'=>'文章自定义字段，支持腾讯视频/微博视频解析'],
				'reupload'		=>['title'=>'图片重命名','type'=>'checkbox','description'=>'是否开启上传图片重命名,注意主题是否有冲突'],
				'advert'		=>['title'=>'广告功能设置','type'=>'checkbox','description'=>'是否开启小程序广告功能设置'],
			],
		],
	);

	if (wp_miniprogram_option('advert')) {
		$options['adsense-setting'] = [
			'title'=>'广告功能',
			'summary'=>'<p>小程序广告功能设置,注意填写正确参数</p>',
			'fields'=> [
				'ad_i_open'			=>['title'=>'首页广告','type'=>'checkbox','description'=>'是否开启首页广告'],
				'ad_i_type'			=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
				'ad_i_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
				'ad_i_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
				
				'ad_t_open'			=>['title'=>'列表广告','type'=>'checkbox','description'=>'是否开启列表页广告'],
				'ad_t_type'			=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
				'ad_t_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
				'ad_t_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
				
				'ad_d_open'			=>['title'=>'详情广告','type'=>'checkbox','description'=>'是否开启详情页广告'],
				'ad_d_type'			=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
				'ad_d_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
				'ad_d_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
				
				'ad_p_open'			=>['title'=>'页面广告','type'=>'checkbox','description'=>'是否开启单页广告'],
				'ad_p_type'			=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告','site'=>'网站链接','taobao'=>'淘宝口令']],
				'ad_p_image'		=>['title'=>'广告图片','type'=>'upload','class'=>'regular-text'],
				'ad_p_args'			=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
			],
		];
	}
	
	return $options;
	
});
