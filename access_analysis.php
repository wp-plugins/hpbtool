<?php

function hpb_access_analysis_page() {

	if ($_POST[ 'addSite' ] == "1") {
		update_option('hpb_access_acount', $_POST['hpb_access_acount']);
		update_option('hpb_access_password', $_POST['hpb_access_password']);
		$hpb_response = hpb_add_site_access_analysis();
	} 
	if ($_POST[ 'deleteSite' ] == "1") {
		update_option('hpb_access_acount', $_POST['hpb_access_acount']);
		update_option('hpb_access_password', $_POST['hpb_access_password']);	
		$hpb_response = hpb_delete_site_access_analysis();
	} 
	$response_hpb_get_access_analysis_id;
	$id_aa = hpb_get_access_analysis_id( $response_hpb_get_access_analysis_id );
	$hpb_is_aa = hpb_is_access_analysis( $id_aa );
	$hpb_site_id = hpb_get_site_id( $id_aa, get_bloginfo( 'url' ) );
	wp_enqueue_style('hpb_dashboard_admin', HPB_PLUGIN_URL.'/hpb_dashboard_admin.css');
	wp_enqueue_script( 'jquery' );
?>
	<div id="hpb_dashboard_body">
	<div id="hpb_dashboard_title" class="wrap"><h2><img src="<?php echo HPB_PLUGIN_URL.'/image/admin/icon_hpb.png';?>">アクセス解析設定</h2></div>
<?php
	if ($_POST[ 'addSite' ] == "1") {
?>
	<div class="hpb_eyecatch_area"><img src="<?php echo HPB_PLUGIN_URL.'/image/admin/eyecatch.png';?>" class="hpb_eyecatch">
<?php
		echo $hpb_response.'</div>';
	} 
	if ($_POST[ 'deleteSite' ] == "1") {
?>
	<div class="hpb_eyecatch_area"><img src="<?php echo HPB_PLUGIN_URL.'/image/admin/eyecatch.png';?>" class="hpb_eyecatch">
<?php
		echo $hpb_response.'</div>';
	} 
	if( $hpb_is_aa == false ) {
		$chkmessage = 'アクセス解析を設定します。よろしいですか？';
	} else {
		$chkmessage = アクセス解析を解除します。よろしいですか？アクセス解析を解除すると今までの解析情報はすべて削除されます。;
	}
?>
	<script><!--
		jQuery('#check_access_analysis').hide();
	--></script>
	<form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>" autocomplete="off" onsubmit="if( confirm('<?php echo $chkmessage; ?>') ) return true; return false;">
	<?php wp_nonce_field('update-options'); ?>
<?php
	if($hpb_is_aa){
	} else {
		echo '<p>アクセス解析サービスのアカウント設定を行い、サイトをアクセス解析の対象に設定します。</p><div id="hpb_aa_service_start"><div class="hpb_caption">サービスの利用手続き</div><p>下のボタンをクリックして、「かんたんアクセス解析」の利用手続きを行います。</p><p><a class="button" href="https://kantan-access.com/apply/index.html" target="_blank">かんたんアクセス解析の利用手続き</a></p></div>';
	}
?>
	<div class="hpb_caption">アカウント設定</div>
	<p>Justアカウントに登録したメールアドレスとパスワードを入力します。<br/>Justアカウントについて詳しくは <a href="http://account.justsystems.com/jp/about.html" target="_blank">こちら</a> をご覧ください。
	<div id="hpb_aa_acount_form">
	<img id="hpb_just_account_logo" src="<?php echo HPB_PLUGIN_URL.'/image/admin/just_account.png';?>"/>
	<table class="hpb_form_table">
	<tr><td>メールアドレス</td><td><input size="60" type="text" name="hpb_access_acount" value="<?php echo get_option('hpb_access_acount'); ?>" <?php if($hpb_is_aa){ echo 'readonly';}?>/></td></tr>
	<tr><td>パスワード</td><td><input size="60" type="password" name="hpb_access_password" value="<?php echo get_option('hpb_access_password'); ?>" <?php if($hpb_is_aa){ echo 'readonly';}?>/></td></tr>
	</table></div>
	<input type="hidden" name="permission" value="0"/>
<?php 
	if( $hpb_is_aa == false ) {
		update_option( 'hpb_site_id', 0 ); 
		echo '<input type="hidden" name="addSite" value="1">
		<p class="submit"><input class="button-primary" type="submit" name="submit" value="サイトをアクセス解析対象に設定"</p></form>'; 
	} else {
		update_option( 'hpb_site_id', strval( $hpb_site_id ) ); 
		echo '<input type="hidden" name="deleteSite" value="1">
		<p class="submit"><input class="button-primary" type="submit" name="submit" value="サイトをアクセス解析対象から解除"/></p></form>';
	}
?>
</div>
<?php
}

function hpb_getfooter_access_analysis() {
if(!is_search() && get_option( 'hpb_site_id', 0 ) != 0){
?>
	<script type="text/javascript"><!--
	var _JustAnalyticsConfig = {
	'siteid': '<?php echo get_option( 'hpb_site_id' ); ?>',
	'domain': '<?php
	$url = parse_url(get_bloginfo( 'url' ));
	echo $url[ 'host' ];?>','path': '/'};// --></script>
	<script type="text/javascript" src="http://tracker.kantan-access.com/js/ja.js"></script><noscript><img width="1" height="1" alt="" src="http://tracker.kantan-access.com/jana_tracker/track4ns.gif?sid=<?php get_option('hpb_site_id');?>&t=&p=%2Findex.php&cs=UTF-8"></noscript>
<?php
}
}

function hpb_delete_site_access_analysis() {
	$uri = 'http://webservice2.jana.justsystems.com';
	$sm_location = 'https://kantan-access.com/jana_webservice2/services/SiteManager';
	$client_sm = new SoapClient( null, array( 'location' => $sm_location, 'uri' => $uri ) );
	$response_hpb_get_access_analysis_id;
	$id_aa = hpb_get_access_analysis_id( $response_hpb_get_access_analysis_id );
	if( $id_aa == '' ) {
		return $response_hpb_get_access_analysis_id;
	}
	$site_id = hpb_get_site_id( $id_aa, get_bloginfo( 'url' ) );
	$request = '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<soapenv:Body>
		<deleteSite xmlns="http://webservice2.jana.justsystems.com">
			<accessInfo>
				<loginName>'.get_option('hpb_access_acount').'</loginName>
				<password>'.get_option('hpb_access_password').'</password>
			</accessInfo>
			<serviceTicketId>'.$id_aa.'</serviceTicketId>
			<siteId>'.$site_id.'</siteId>
		</deleteSite>
	</soapenv:Body>
</soapenv:Envelope>';
	$res =  $client_sm->__doRequest( $request, $sm_location, 'deleteSite', 1.1 );
	$res_xml = simplexml_load_string( $res );
	$elm = $res_xml->children( 'soapenv', true );
	$elm = $elm->children( 'http://webservice2.jana.justsystems.com' );
	$elm = $elm->children( '' );
	$elm = $elm->children( '' );
	$response = '';
	if( $elm->statusCode == 0 || $elm->statusCode == 20300 ) {
		$response = 'かんたんアクセス解析の解析対象に登録したサイトを、解析対象から外しました。';
        } else {
		$response = $elm->statusMessage;
	}
	return $response;
}

function hpb_add_site_access_analysis() {
	$uri = 'http://webservice2.jana.justsystems.com';
	$sm_location = 'https://kantan-access.com/jana_webservice2/services/SiteManager';
	$client_sm = new SoapClient( null, array( 'location' => $sm_location, 'uri' => $uri ) );
	$response_hpb_get_access_analysis_id;
	$id_aa = hpb_get_access_analysis_id( $response_hpb_get_access_analysis_id );
	if( $id_aa == '' ) {
		return $response_hpb_get_access_analysis_id;
	}
	$site_name = get_bloginfo('name');
	if( $site_name == '' ) {
		$site_name = preg_replace( '#^(https?://)?(www.)?#', '', get_home_url() );
	}
	$request = '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<soapenv:Body>
		<addSite xmlns="http://webservice2.jana.justsystems.com">
			<accessInfo>
				<loginName>'.get_option('hpb_access_acount').'</loginName>
				<password>'.get_option('hpb_access_password').'</password>
			</accessInfo>
			<serviceTicketId>'.$id_aa.'</serviceTicketId>
			<siteName>'.$site_name.'</siteName>
			<siteUrl>'.get_bloginfo('url').'</siteUrl>
		</addSite>
  </soapenv:Body>
</soapenv:Envelope>';
	$res =  $client_sm->__doRequest( $request, $sm_location, 'addSite', 1.1 );
	$res_xml = simplexml_load_string( $res );
	$elm = $res_xml->children( 'soapenv', true );
	$elm = $elm->children( 'http://webservice2.jana.justsystems.com' );
	$elm = $elm->children( '' );
	$elm = $elm->children( '' );

	$site_id = strval( $elm->siteId );
	$response = '';
	if( $elm->statusCode == 0 ) {
		$response = 'サイトをかんたんアクセス解析の解析対象に設定しました。<a href="https://kantan-access.com/jana_webapp/login.do" target="_blank">アクセス解析ページを表示</a>';
	} else if( $elm->statusCode == 20303 || $elm->statusCode == 20304 ) {
		$site_id = hpb_get_Site_Id( $id_aa, get_bloginfo( 'url' ) );
		if( $site_id  == '' ) {
			return $response = '同じサイト名のサイトがすでに登録されています。サイト名を変更するか、登録されているサイトの登録を解除してください。';
		} else {
			$response = 'サイトをかんたんアクセス解析の解析対象に設定しました。<a href="https://kantan-access.com/jana_webapp/login.do" target="_blank">アクセス解析ページを表示</a>';
		}
	} else  {
		$response = hpb_get_statusmessage( $elm->statusCode );
	} 
	return $response;
}

function hpb_get_access_analysis_id( &$ErrorMessage ) {
	$hpb_access_acount = get_option('hpb_access_acount');
	$hpb_access_password = get_option('hpb_access_password');
	$uri = 'http://webservice2.jana.justsystems.com';
	$stm_location = 'https://kantan-access.com/jana_webservice2/services/ServiceTicketManager';
	$client_stm = new SoapClient( null, array('location' => $stm_location, 'uri' => $uri, 'trace'=>true ) );
	$request = '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<soapenv:Body>
		<getAllServiceTickets xmlns="http://webservice2.jana.justsystems.com">
		<accessInfo>
			<loginName>'.$hpb_access_acount.'</loginName>
			<password>'.$hpb_access_password .'</password>
		</accessInfo>
		</getAllServiceTickets>
	</soapenv:Body>
</soapenv:Envelope>';
	$res =  $client_stm->__doRequest( $request, $stm_location, 'getAllServiceTickets', 1.1 );
	$res_xml = simplexml_load_string( $res );
	$elm = $res_xml->children( 'soapenv', true );
	$elm = $elm->children( 'http://webservice2.jana.justsystems.com' );
	$elm = $elm->children( '' );
	$elm = $elm->children( '' );
	$id = $elm->serviceTickets->item->id;
	if( $id == '' ) {
		$ErrorMessage = hpb_get_statusmessage( $elm->statusCode );
	}
	return $id; 
}

function hpb_get_statusMessage( $statusCode ) {
	if( $statusCode == 100 ) {
		return '内部的なエラーが発生しました。';
	} else if( $statusCode == 101 ) {
		return 'サービスはメンテナンス中です。';
	} else if( $statusCode == 200 || $statusCode == 201 || $statusCode == 301 || $statusCode == 300 ) {
		return 'JUSTアカウントまたはパスワードが正しくありません。';
	} else if( $statusCode == 302 ) {
		return 'そのアカウントは「かんたんアクセス解析」の会員ではありません。';
	} else if( $statusCode == 303 ) {
		return 'そのアカウントは利用停止状態になっています。';
	} else if( $statusCode == 304 ) {
		return 'そのアカウントは退会しています。';
	} else if( $statusCode == 20200 ) {
		return 'パラメータの形式が不正です（サービス利用権ID）。';
	} else if( $statusCode == 20201 ) {
		return 'パラメータの形式が不正です（サイトの名称）。';
	} else if( $statusCode == 20202 ) {
		return 'パラメータの形式が不正です（サイトのURL）。';
	} else if( $statusCode == 20202 ) {
		return 'パラメータの形式が不正です（サイトID）。';
	}else if( $statusCode == 20300 ) {
		return '指定のサイトは存在しません';
	} else if( $statusCode == 20301 ) {
		return '指定のサービス利用権は利用期限を過ぎています。';
	} else if( $statusCode == 20302 ) {
		return 'サービス利用権の最大サイト数を越えています。';
	} else if( $statusCode == 20303 ) {
		return '同じ名前のサイトが既に登録されています。';
	} else if( $statusCode == 20304 ) {
		return '同じURLのサイトが既に登録されています。';
	} else if( $statusCode == 20305 ) {
		return 'サービス利用権は存在しません。';
	} else if( $statusCode == 20306 ) {
		return 'サービス利用権に課金エラーが発生しています。';
	} else if( $statusCode == 20307 ) {
		return 'サービス利用権は解約されています。';
	}
}

function hpb_is_access_analysis( $id_aa ) {
	if( $id_aa == '' ) {
		return false;
	}
	$site_id = hpb_get_site_id( $id_aa, get_bloginfo( 'url' ) );
	return $site_id != '';
}

function hpb_get_site_id( $id_aa, $url ) {
	if( $id_aa == '' ) {
		return;
	}
	$uri = 'http://webservice2.jana.justsystems.com';
	$sm_location = 'https://kantan-access.com/jana_webservice2/services/SiteManager';
	$client_sm = new SoapClient( null, array( 'location' => $sm_location, 'uri' => $uri ) );
	$request = '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<soapenv:Body>
		<getAllSites xmlns="http://webservice2.jana.justsystems.com">
		<accessInfo>
			<loginName>'.get_option('hpb_access_acount').'</loginName>
			<password>'.get_option('hpb_access_password').'</password>
		</accessInfo>
		<serviceTicketId>'.$id_aa.'</serviceTicketId>
		</getAllSites>
	</soapenv:Body>
</soapenv:Envelope>';
	$res =  $client_sm->__doRequest( $request, $sm_location, 'getAllSites', 1.1 );
	$res_xml = simplexml_load_string( $res );
	$elm = $res_xml->children( 'soapenv', true );
	$elm = $elm->children( 'http://webservice2.jana.justsystems.com' );
	$elm = $elm->children( '' );
	$elm = $elm->children( '' );
	foreach( $elm->sites->item as $item ){
		if( $item->url == $url ){
			return $item->id;
		}
	}
}

?>