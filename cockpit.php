<?php

function hpb_cockpit_page() {
?>
<style>

#hpb_dashboard_body {
	margin: 0px;
	padding: 0px 22px 0px 0px;
}

/*title*/
#hpb_dashboard_title {
	margin:5px 0px 10px 0px;
}

#hpb_dashboard_title img {
	vertical-align: middle;
	margin-right:10px;
}

</style>
<div id="hpb_dashboard_body">
<div id="hpb_dashboard_title" class="wrap"><h2><img src="<?php echo HPB_PLUGIN_URL.'/image/admin/icon_hpb.png';?>">コックピット設定</h2></div>
<iframe width="100%" height="580px" src="http://www.justsystems.com/jp/links/hpb/cpintro2.html?p=hpb17_wp_hpbdash"></div>
<?php
}

?>