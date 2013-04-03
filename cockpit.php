<?php

add_action('init', 'hpbtool_cockpit_init');
add_action( 'publish_future_post', 'hpb_cockpit_future_publish_hook');

function hpb_cockpit_future_publish_hook($post_id) {
   if(!class_exists('CockpitManager')){
	require_once HPB_PLUGIN_DIR.'/cockpit_service.php';
	$cockpitManager = new CockpitManager('', plugins_url( '', __FILE__ ).'/image/admin/icon_hpb.png'); 
	if(method_exists($cockpitManager, 'cockpit_publish_post_hook')) {
		$cockpitManager->cockpit_publish_post_hook($post_id, true);
	}
   }
}

function hpbtool_cockpit_init(){
	if(!class_exists('CockpitManager')){
		require_once HPB_PLUGIN_DIR.'/cockpit_service.php';
		$cockpitManager = new CockpitManager('', plugins_url( '', __FILE__ ).'/image/admin/icon_hpb.png'); 
		$cockpitManager->cockpit_init();
	}
}

function hpb_cockpit_page() {
	$cockpitManager = new CockpitManager('', plugins_url( '', __FILE__ ).'/image/admin/icon_hpb.png'); 
	$cockpitManager->cockpit_admin_home();
}

?>