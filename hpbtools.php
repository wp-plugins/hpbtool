<?php
/*
Plugin Name: hpb Dashboard
Plugin URI:http://www.justsystems.com/jp/links/hpb/wppdf.html?p=hpb17_wp_hpbdash 
Description: ホームページビルダーが提供するプラグインです。hpbダッシュボードが追加されます。
Version: 1.0.0
Author: JustSystems
Author URI:http://www.justsystems.com/jp/links/hpb/creator.html?p=hpb17_wp_hpbdash
*/

define( 'HPB_PLUGIN_DIR', WP_PLUGIN_DIR.'/hpbtool' );
define( 'HPB_PLUGIN_URL', WP_PLUGIN_URL.'/hpbtool' );
define( 'HPB_PLUGINDATA_DIR', WP_PLUGIN_DIR.'/hpbtooldata' );

require_once HPB_PLUGIN_DIR.'/social_buttons.php';
require_once HPB_PLUGIN_DIR.'/form.php';
require_once HPB_PLUGIN_DIR.'/access_analysis.php';
require_once HPB_PLUGIN_DIR.'/import.php';
require_once HPB_PLUGIN_DIR.'/cockpit.php';

add_action( 'admin_menu' , 'hpb_option' );
 
function hpb_option() {
	$icon_url = HPB_PLUGIN_URL.'/image/admin/menu_hpb.png';	add_menu_page( 'HPBTOOL', 'hpbダッシュボード', 'administrator', 'hpb_main', 'hpb_admin_home', $icon_url, 3 );
	add_submenu_page( 'hpb_main', 'ホーム', 'ホーム', 'administrator', 'hpb_main', 'hpb_admin_home' );
	add_submenu_page( 'hpb_main', 'ソーシャルボタン設定', 'ソーシャルボタン設定', 'administrator', 'hpb_social_page', 'hpb_social_page' );
	add_submenu_page( 'hpb_main', 'フォーム設定', 'フォーム設定', 'administrator', 'hpb_form_page', 'hpb_form_page' );
	add_submenu_page( 'hpb_main', 'コックピット設定', 'コックピット設定', 'administrator', 'hpb_cockpit_page', 'hpb_cockpit_page' );
	add_submenu_page( 'hpb_main', 'アクセス解析設定', 'アクセス解析設定', 'administrator', 'hpb_access_analysis_page', 'hpb_access_analysis_page' );
	add_submenu_page( 'hpb_main', 'オプション', 'オプション', 'administrator', 'hpb_dashborad_widget_option', 'hpb_dashborad_widget_option' );		
}

function hpb_admin_home() {
?>
<div id="hpb_dashboard_body">
<?php
	$hpbpage = $_GET['hpbpage'];
	if( isset( $_POST['hpb-update-data'] ) ) {
		hpb_import_list_page();
	} else {
		hpb_import_page();
		hpb_guidance_activate_multibyte_patch();
		hpb_guidance_new_post();
		hpb_dashboard_widget_function();
	}
?>
</div>
<?php
}

function hpb_guidance_new_post() {
?>
<?php
	if( get_option('hpb_dashboard_guidance', '0') == 1 ) {
		return;
	}
	if( $_GET['hpb_visible'] == 'false' ){
		update_option('hpb_dashboard_guidance', '1');
		return;
	}
	$wp_post_type_names = get_post_types(array('_builtin' => false), 'objects');
?>
<div id="hpb_guidance">
	<a href="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>&hpb_visible=false" id="hpb_guidance_invisible"></a>
	<div id="hpb_guidance_image"></div>
</div>
<div id="hpb_guidance_step">
	<table><tr><td><div id="hpb_guidance_step1"></div></td><td><div id="hpb_guidance_step2"></div></td></tr>
	<tr><td>
<?php
	if( count($wp_post_type_names) > 0 ) {
?>
	<a href="post-new.php?post_type=<?php echo reset($wp_post_type_names)->name; ?>" id="hpb_guidance_post"></a>
<?php
	} else {
?>
	<a href="post-new.php" id="hpb_guidance_post"></a>
<?php
	}
?>
	</td><td><a href="<?php echo get_option('siteurl');?>" id="hpb_guidance_view_site" target="_blank"></a></td></tr></table>
</div>
<?php
}

add_action( 'wp_head', 'hpb_head');

function hpb_head() {
	hpb_head_form();
	hpb_head_social();
}

add_action( 'wp_footer', 'hpb_getfooter' );

function hpb_getfooter() {
	hpb_getfooter_access_analysis();
}

add_action("wp_enqueue_scripts", "hpb_enqueue_style");

function hpb_enqueue_style () {
	hpb_social_enqueue_style();
	hpb_form_enqueue_style();
}

add_filter( 'the_content', 'hpb_the_content' );

function hpb_the_content ( $content ) {
	$content = hpb_form_the_content( $content );
	$content = hpb_social_the_content( $content );
	return $content;
}

function hpb_custom_admin_menu () {
	if( get_option('hpb_hide_menus', 1 ) != 1 ) {
		return;
	}
	global $menu;
	$remove_menus = array();
	if( get_option( 'hpb_visible_menu_dashbord', 0 ) == 0 ) {
		$remove_menus[count( $remove_menus )] = __('ダッシュボード');
	}
	if( get_option( 'hpb_visible_menu_media', 0 ) == 0 ) {
		$remove_menus[count( $remove_menus )] = __('メディア');
	}
	if( get_option( 'hpb_visible_menu_link', 0 ) == 0 ) {
		$remove_menus[count( $remove_menus )] = __('リンク');
	}
	if( get_option( 'hpb_visible_menu_themes', 0 ) == 0 ) {
		$remove_menus[count( $remove_menus )] = __('外観');
	}
	if( get_option( 'hpb_visible_menu_tools', 0 ) == 0 ) {
		$remove_menus[count( $remove_menus )] = __('ツール');
	}
	if( get_option( 'hpb_visible_menu_users', 0 ) == 0 ) {
		$remove_menus[count( $remove_menus )] = __('ユーザー');
	}
	if( get_option( 'hpb_visible_menu_options', 0 ) == 0 ) {
		$remove_menus[count( $remove_menus )] = __('設定');
	}
	if( get_option( 'hpb_visible_menu_plugins', 0 ) == 0 ) {
		$remove_menus[count( $remove_menus )] = __('プラグイン');
	}
	if( get_option( 'hpb_visible_menu_addpage', 0 ) == 0 ) {
		$remove_menus[count( $remove_menus )] = __('固定ページ');
	}
	end ( $menu );
	while ( prev($menu) ){
		$value = explode( ' ', $menu[key($menu)][0] );
		if( in_array( $value[0] != NULL?$value[0]:"" , $remove_menus ) ) {
			unset( $menu[key($menu)] );
		}
	}
}
add_action('admin_menu', 'hpb_custom_admin_menu');

function hpb_dashborad_widget_option() {
	wp_enqueue_style('hpb_dashboard_admin', HPB_PLUGIN_URL.'/hpb_dashboard_admin.css');
?>
	<div id="hpb_dashboard_body">
	<div id="hpb_dashboard_title" class="wrap"><h2><img src="<?php echo HPB_PLUGIN_URL.'/image/admin/icon_hpb.png';?>">オプション</h2></div>
	<p>項目を選択して[設定を保存する]をクリックすると、画面の左側に表示されているメニューが切り替わります。</p>
	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>
	<table class="hpb_form_table">
	<tr><td>メニューのカスタマイズ</td> 
	<td><input type="radio" name="hpb_hide_menus" value="1" <?php checked( get_option('hpb_hide_menus', 1 ), 1 ); ?>> かんたんメニュー
	<p class="indent1">サイト更新によく使う機能をまとめたメニューです。WordPressを初めてお使いの方にお勧めです。<br/>用途に応じて、表示する項目をカスタマイズすることもできます。</p>
	<div id="hpb_visible_settings">
	<span class="indent1">カスタマイズ項目:</span><br/>
	<div class="indent1"><input type="checkbox" name="hpb_visible_menu_dashbord" value="1" <?php checked( get_option('hpb_visible_menu_dashbord', 0 ), 1 ); ?>/><?php echo __('ダッシュボード'); ?></div>
	<div class="indent1"><input type="checkbox" name="hpb_visible_menu_addpage" value="1" <?php checked( get_option('hpb_visible_menu_addpage', 0 ), 1 ); ?>/><?php echo __('固定ページ'); ?></div>
	<div class="indent1"><input type="checkbox" name="hpb_visible_menu_media" value="1" <?php checked( get_option('hpb_visible_menu_media', 0 ), 1 ); ?>/><?php echo __('メディア'); ?></div>
	<div class="indent1"><input type="checkbox" name="hpb_visible_menu_link" value="1" <?php checked( get_option('hpb_visible_menu_link', 0 ), 1 ); ?>/><?php echo __('リンク'); ?></div>
	<div class="indent1"><input type="checkbox" name="hpb_visible_menu_themes" value="1" <?php checked( get_option('hpb_visible_menu_themes', 0 ), 1 ); ?>/><?php echo __('外観'); ?></div>
	<div class="indent1"><input type="checkbox" name="hpb_visible_menu_tools" value="1" <?php checked( get_option('hpb_visible_menu_tools', 0 ), 1 ); ?>/><?php echo __('ツール'); ?></div>
	<div class="indent1"><input type="checkbox" name="hpb_visible_menu_users" value="1" <?php checked( get_option('hpb_visible_menu_users', 0 ), 1 ); ?>/><?php echo __('ユーザー'); ?></div>
	<div class="indent1"><input type="checkbox" name="hpb_visible_menu_options" value="1" <?php checked( get_option('hpb_visible_menu_options', 0 ), 1 ); ?>/><?php echo __('設定'); ?></div>
	<div class="indent1"><input type="checkbox" name="hpb_visible_menu_plugins" value="1" <?php checked( get_option('hpb_visible_menu_plugins', 0 ), 1 ); ?>/><?php echo __('プラグイン'); ?></div>
	</div>
	<p><input type="radio" name="hpb_hide_menus" value="0" <?php checked( get_option('hpb_hide_menus', 1 ), 0 ); ?>> すべてのメニュー</p>
	<p class="indent1">WordPressのすべての機能が使えます。WordPressを使い慣れている方にお勧めです。</p></td></tr></table>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="hpb_hide_menus, hpb_visible_menu_dashbord, hpb_visible_menu_addpage, hpb_visible_menu_media, hpb_visible_menu_widgets, hpb_visible_menu_link, hpb_visible_menu_themes, hpb_visible_menu_tools, hpb_visible_menu_users, hpb_visible_menu_options, hpb_visible_menu_plugins" />
	<br><input type="submit" class="button-primary" value="<?php _e('設定を保存する') ?>" />
	</form>
	</div>
<?php
}

function hpb_dashboard_widget_function() {
	wp_enqueue_style('hpb_dashboard_menu_widget_style', HPB_PLUGIN_URL.'/hpb_dashboard_admin.css');
?>
<p class="hpb_content_head"><img src="<?php echo HPB_PLUGIN_URL; ?>/image/admin/post_new_head.png"/></p>
<table class="hpb_content_table">
<?php
	$wp_post_type_names = get_post_types(array('_builtin' => false), 'objects');
	$index = 0;
	foreach( $wp_post_type_names as $post_type ) {
		$index += 1;
?>
	<tr><td class="hpb_menu_icon<?php if($index == 1) { echo ' hpb_first_menu'; }?>"><?php echo getPostTypeIconByName($post_type->label); if( isUserDefineType($post_type->label) ) { echo '<div class="hpb_user_define_post">'.$post_type->label.'</div>';}?></td><td <?php if($index == 1) { echo 'class="hpb_first_menu_contents"'; }?>><ul><li class="hpb_menu_item"><a href="post-new.php?post_type=<?php echo $post_type->name; ?>" class="hpb_post_new_btn"></a></li><li class="hpb_menu_item"><a href="<?php echo home_url().'?post_type='.$post_type->name; ?>" class="hpb_view_post_btn" target="_blank"></a></li><li class="hpb_menu_item"><a href="edit.php?post_type=<?php echo $post_type->name; ?>" class="hpb_view_post_list_btn"></a><li></ul></td></tr>
<?php
	}
?>
	<tr><td class="hpb_menu_icon"><img src="<?php echo HPB_PLUGIN_URL; ?>/image/admin/post_blog_icon.png"></td><td><ul><li class="hpb_menu_item"><a href="post-new.php" class="hpb_post_new_btn"></a></li><li class="hpb_menu_item"><a href="<?php echo home_url();?>?post_type=post" class="hpb_view_post_btn" target="_blank"></a></li><li class="hpb_menu_item"><a href="edit.php" class="hpb_view_post_list_btn"></a><li></ul></td><tr>
</table><br>
<p class="hpb_content_head"><img src="<?php echo HPB_PLUGIN_URL; ?>/image/admin/check_head.png"/></p>
<table class="hpb_content_table"><tr>
	<td class="hpb_menu_icon hpb_first_menu"><img src="<?php echo HPB_PLUGIN_URL; ?>/image/admin/comment_icon.png"></td>
	<td class="hpb_first_menu_contents"><ul><li class="hpb_menu_item"><a href='edit-comments.php?comment_status=moderated' id="<?php $comment_counts = get_comment_count(); if( $comment_counts['awaiting_moderation'] == 0 ) { echo 'hpb_comment_approval_btn3';} elseif($comment_counts['awaiting_moderation'] < 100 ) { echo 'hpb_comment_approval_btn';} else { echo 'hpb_comment_approval_btn2';}?>"><span class="hpb_comment_count<?php if( $comment_counts['awaiting_moderation'] == 0 ) { echo ' displaynone';} ?>"><?php $comment_counts = get_comment_count(); echo $comment_counts['awaiting_moderation']; ?></span></a></li>
	<li class="hpb_menu_item"><a href='edit-comments.php?comment_status=approved' id="<?php $comment_counts = get_comment_count(); if($comment_counts['approved'] == 0 ) { echo 'hpb_comment_view_btn3';} elseif($comment_counts['approved'] < 100 ) { echo 'hpb_comment_view_btn';} else { echo 'hpb_comment_view_btn2';}?>"><span class="hpb_comment_count<?php if( $comment_counts['approved'] == 0 ) { echo ' displaynone';} ?>"><?php echo $comment_counts['approved']; ?></span></a></li></ul></td>
</tr><tr>
	<td class="hpb_menu_icon"><img src="<?php echo HPB_PLUGIN_URL; ?>/image/admin/cockpit_icon.png"></td>
	<td><ul><li class="hpb_menu_item"><a href='http://www.justsystems.com/jp/links/hpb/cp.html?p=hpb17_wp_hpbdash' id="hpb_cockpit_check_btn" target="_blank"></a></li></ul></td>
</tr><tr>
	<td class="hpb_menu_icon"><img src="<?php echo HPB_PLUGIN_URL; ?>/image/admin/seo_icon.png"></td>
	<td><ul><li class="hpb_menu_item"><a href='https://kantan-access.com/jana_webapp/login.do' id="hpb_aa_check_btn" target="_blank"></a></li></ul></td>
</tr></table>
<div class="hpb_clearboth"></div></td>
<p class="hpb_content_head"><img src="<?php echo HPB_PLUGIN_URL; ?>/image/admin/settings_head.png"/></p>
<table class="hpb_content_table"><tr>
<td class="hpb_menu_icon hpb_first_menu"><img src="<?php echo HPB_PLUGIN_URL; ?>/image/admin/option_icon.png"/></td>
<td class="hpb_first_menu_contents"><ul class="hpb_option_settings_category">
	<li class="hpb_option_menu_item"><a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=hpb_main&page=hpb_social_page'; ?>" id="hpb_socialbutton_settings"></a></li>
	<li class="hpb_option_menu_item"><a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=hpb_main&page=hpb_cockpit_page'; ?>" id="hpb_cockpit_settings"></a></li>
	<li class="hpb_option_menu_item"><a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=hpb_main&page=hpb_access_analysis_page'; ?>" id="hpb_aa_settings"></a></li>
	<li class="hpb_option_menu_item"><a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=hpb_main&page=hpb_form_page'; ?>" id="hpb_form_settings"></a></li>
	<li class="hpb_option_menu_item"><a href="widgets.php" id="hpb_layout_settings"></a></li>
	<li class="hpb_option_menu_item"><a href="<?php echo get_option('siteurl') . '/wp-admin/admin.php?page=hpb_main&page=hpb_dashborad_widget_option'; ?>" id="hpb_option_settings"></a></li>	
</ul>
<div class="hpb_clearboth"></div></td></tr>
<tr><td class="hpb_menu_icon"><img src="<?php echo HPB_PLUGIN_URL; ?>/image/admin/help_icon.png"/></td>
<td><ul class="hpb_option_settings_category hpb_menu_list">
	<li class="hpb_option_setting hpb_option_menu_item"><a href="http://www.justsystems.com/jp/links/hpb/wppdf.html?p=hpb17_wp_hpbdash" id="hpb_help" target="_blank"></a></li>
	<li class="hpb_option_setting hpb_option_menu_item"><a href="http://support.justsystems.com/jp/app/servlet/productslink?apl=hpb17" id="hpb_faq" target="_blank"></a></li>
</ul>
<div class="hpb_clearboth"></div></td></tr></table>
<?php	
} 

function isUserDefineType ( $post_title ) {

	switch( $post_title ) {
		case 'ニュース':
		case '製品':
		case 'サービス':
		case '活動':
		case 'FAQ':
		case '商品':
		case 'メニュー':
		case '物件':
			return 0;
		default:
			return 1;
	} 
	return 0;	
}

function getPostTypeIconByName( $post_title ) {
	switch( $post_title ) {
		case 'ニュース':
			$hpb_post_icon = '<img src="'.HPB_PLUGIN_URL.'/image/admin/post_news_icon.png"/>';
			break;
		case '製品':
			$hpb_post_icon = '<img src="'.HPB_PLUGIN_URL.'/image/admin/post_product_icon.png"/>';
			break;
		case 'サービス':
			$hpb_post_icon = '<img src="'.HPB_PLUGIN_URL.'/image/admin/post_service_icon.png"/>';
			break;
		case '活動':
			$hpb_post_icon = '<img src="'.HPB_PLUGIN_URL.'/image/admin/post_activity_icon.png"/>';
			break;
		case 'FAQ':
			$hpb_post_icon = '<img src="'.HPB_PLUGIN_URL.'/image/admin/post_faq_icon.png"/>';
			break;
		case '商品':
			$hpb_post_icon = '<img src="'.HPB_PLUGIN_URL.'/image/admin/post_goods_icon.png"/>';
			break;
		case 'メニュー':
			$hpb_post_icon = '<img src="'.HPB_PLUGIN_URL.'/image/admin/post_menu_icon.png"/>';
			break;
		case '物件':
			$hpb_post_icon = '<img src="'.HPB_PLUGIN_URL.'/image/admin/post_property_icon.png"/>';
			break;
		default:
			$hpb_post_icon = '<img src="'.HPB_PLUGIN_URL.'/image/admin/post_user_icon.png"/>';
	} 
	return $hpb_post_icon;
}

function redirect_dashiboard() {
	if ( get_option('hpb_hide_menus', 1 ) == 1 && basename($_SERVER['REQUEST_URI']) == 'wp-admin' ) {
		wp_redirect(get_option('siteurl') . '/wp-admin/admin.php?page=hpb_main');
		exit;
	}
}
add_action( 'init', 'redirect_dashiboard' );

add_action( 'admin_init', 'hpb_plugin_admin_init' );

function hpb_plugin_admin_init() {
	hpb_plugin_admin_init_import();
}

function hpb_custom_editor_settings( $initArray ){
    $initArray['theme_advanced_buttons1'] = 'bold,italic,strikethrough,underline,|,|,bullist,numlist,|,|,justifyleft,justifycenter,justifyright,justifyfull,|,|,link,unlink,wp_more,|,|,wp_adv';
    $initArray['theme_advanced_buttons2'] = 'formatselect,|,|, fontsizeselect,forecolor,backcolor,|,|,hr,|,|,charmap,|,|,outdent,indent,|,|,undo,redo';
    return $initArray;
}

add_filter( 'tiny_mce_before_init', 'hpb_custom_editor_settings' );

add_filter(  'gettext',  'change_side_text'  );
add_filter(  'ngettext',  'change_side_text'  );
function change_side_text( $translated ) {
     $translated = str_ireplace(  '投稿',  'ブログ',  $translated );
     return $translated;
}

add_action( 'admin_footer_text', 'custom_admin_footer' );
function custom_admin_footer() {
    echo '<a href="http://www.justsystems.com/jp/links/hpb/wppdf.html?p=hpb17_wp_hpbdash" target="_blank"/>ホームページビルダー WordPress編 PDFマニュアル</a>';
}

add_action( 'admin_print_styles', 'hpb_plugin_admin_styles' );
   
function hpb_plugin_admin_styles() {
	wp_enqueue_style( 'hpbpluginadminstyle', HPB_PLUGIN_URL.'/hpb_plugin_admin_style.css' );
}

function hpb_guidance_activate_multibyte_patch() {
if( isset($_POST['hpb_activate_multibyte_patch']) ) {
	hpb_activate_multibyte_patch();
}
if( is_plugin_active('wp-multibyte-patch/wp-multibyte-patch.php') == false ) {
?>
<div class="hpb_eyecatch_area" class="submit"><form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>"><img src="<?php echo HPB_PLUGIN_URL.'/image/admin/eyecatch.png';?>" class="hpb_eyecatch">WordPressを日本語環境で正しく動作させるために WP Multibyte Patch プラグインを必ず有効化しましょう。<input class="button-primary" type="submit" name="hpb_activate_multibyte_patch" value="有効化する" /></form></div>
<?php
}
}

function hpb_activate_multibyte_patch() {
	activate_plugin( 'wp-multibyte-patch/wp-multibyte-patch.php');
}

?>