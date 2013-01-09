<?php

function hpb_plugin_admin_init_import() {
	global $plugin_page;
	$this_plugin = "hpb_main";
	if ( $plugin_page == $this_plugin ) {
		if( isset( $_POST['do_action'] ) ) {
			if( hpb_import_xml( $_POST ) == true){
				wp_redirect( admin_url('admin.php?page=hpb_main&imported=true') );
				exit();
			} 
		} else if (	isset( $_POST['cancel'] ) ) {
				wp_redirect( admin_url('admin.php?page=hpb_main&imported=false') );
				exit();
		}
	} 
}

function hpb_import_list_page() {
	wp_enqueue_style('hpb_dashboard_admin', HPB_PLUGIN_URL.'/hpb_dashboard_admin.css');
	wp_enqueue_script('jquery');
	wp_enqueue_script('hpb_dashboard_import_script', HPB_PLUGIN_URL.'/hpb_dashboard_import_admin.js');
	?>
	<div id="hpb_dashboard_title" class="wrap"><h2><img src="<?php echo HPB_PLUGIN_URL.'/image/admin/icon_hpb.png';?>">データの反映</h2></div>
	<p>更新内容を確認して、[データの反映を実行する]をクリックしてください。</p>
	<?php
	hpb_import_list();
}

function hpb_import_page() {
?>
<?php
		if( !file_exists( HPB_PLUGINDATA_DIR.'/usercontents.xml' ) ) {
			return;
		}
		$content = file_get_contents( HPB_PLUGINDATA_DIR.'/usercontents.xml' );
		if( $content == false ){
			return;
		}
		$xml = simplexml_load_string( $content, 'SimpleXMLElement', LIBXML_NOCDATA );
 		if( $xml == false ){
			return;
		}
		if( $_GET['imported'] == 'true' ) {
?>
<div class="submit hpb_eyecatch_area"><img src="<?php echo HPB_PLUGIN_URL.'/image/admin/eyecatch.png';?>" class="hpb_eyecatch">データを反映しました。サイトを確認してみましょう。<a href="<?php echo home_url(); ?>" target="_blank" class="button-primary">サイトを見る</a></div>
<?php
			return;
		} else if (get_option('hpb_plugin_last_imported') == $xml->date && $xml->date != '') {
			return;
		}
?>
<?php
?>
<div class="submit hpb_eyecatch_area"><form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>"><img src="<?php echo HPB_PLUGIN_URL.'/image/admin/eyecatch.png';?>" class="hpb_eyecatch">ホームページビルダーでメニューや固定ページの編集を行った場合は、データの反映をする必要があります。<input class="button-primary" type="submit" name="hpb-update-data" value="データの反映" /></form></div>
<?php
?>
<?php
}

define( 'IMPORT_STATUS_TRASH', 1 );
define( 'IMPORT_STATUS_DELETE', 2 );
define( 'IMPORT_STATUS_ADD', 3 );
define( 'IMPORT_STATUS_UPDATE', 4 );
define( 'IMPORT_STATUS_NOACTION', 5 );

define( 'PULLDOWN_TYPE_TRASH', 1);
define( 'PULLDOWN_TYPE_DELETE', 2);
define( 'PULLDOWN_TYPE_ADD', 3);
define( 'PULLDOWN_TYPE_UPDATE', 4);

function hpb_import_list() {
	$import_data = array();
	$cutrrent_pages = get_pages( array( 'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private', 'inherit' ) ) );
	foreach ( $cutrrent_pages as $current_page ){
		if( get_post_status( $current_page->ID ) != 'trash' ) {
			$import_data[count( $import_data )] = array(
			'title' => $current_page->post_title,
			'link'  => get_page_link( $current_page->ID ),
			'status'=> IMPORT_STATUS_TRASH,
			'type'  => PULLDOWN_TYPE_TRASH,
			'id'	=> $current_page->ID
			);
		} 
	}
	$content = file_get_contents( HPB_PLUGINDATA_DIR.'/usercontents.xml' );
	if( $content == false ){
		return;
	}
	$xml = simplexml_load_string( $content, 'SimpleXMLElement', LIBXML_NOCDATA );
 	if( $xml == false ){
		return;
	}

	$id = 0;
	foreach ( $xml->item as $item ) {
		$id++;
		if( $item->post_type != 'page' ) {
			continue;
		}
		$title = esc_html( $item->title );
		$exist_in_data = false;
		for ( $index = 0; $index < count( $import_data ); $index++ ) {
			$data = $import_data[$index];
			if( $title == $data['title'] || $item->title == $data['title'] ) {
				$import_data[$index]['status'] = IMPORT_STATUS_UPDATE;
				$import_data[$index]['type'] = PULLDOWN_TYPE_UPDATE;
				$exist_in_data = true;
			}
		} 
		if ( $exist_in_data == false ) {
			$import_data[count( $import_data )] = array(
			'title' => $item->title,
			'link'  => '',
			'status'=> IMPORT_STATUS_ADD,
			'type'  => PULLDOWN_TYPE_ADD,
			'id'	=> 'hpb_'.$id
			);
		}
	}
	foreach ( $import_data as $key => $row ) {
		$status[$key] = $row['status'];
	}
	array_multisort( $status, SORT_STRING, $import_data ); 
?>
	<form method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	<?php wp_nonce_field('update-options'); ?>
	<input type="hidden" name="action" value="update" />
	<table id="hpb_import_list">
	<tr><th>タイトル</th><th>ステータス</th></tr>
<?php
	foreach ( $import_data as $data ) {
		$link = $data['link'];
		$title = esc_html( $data['title'] );
		if( $title == '' ) {
			$title = '(タイトルなし)';
		}
?>
	<tr><td class="hpb_page_title">
<?php
	if( $link != '' ) {
?>
		<a target="_blank" href="<?php echo esc_attr($link); ?>"><?php echo $title; ?></a>
<?php
	} else {
		echo $title;
	}
 ?>
	</td><?php echo hpb_get_pulldown( $data['status'], $data['type'], $data['id']); ?></tr>
<?php
	}
?>
	<tr class="hpb_table_footer"><th>タイトル</th><th colspan="2">ステータス</th></tr>
	</table><br>
	<div id="hpb_update_custom_menu_field"><input type="checkbox" name="update_custom_menu" id="hpb_update_custom_menu" value="1" <?php checked( get_option('hpb_import_custom_menu', 1 ), 1 ); ?>><label for="hpb_update_custom_menu">　カスタムメニューを更新する。</label></input></div>
<div id="hpb_activate_theme"><input type="checkbox" name="activate_theme" id="hpb_activate_theme" value="1" <?php checked( get_option('hpb_activate_theme', 1 ), 1 ); ?>><label for="hpb_activate_theme">　テーマを有効化する。</label></input></div><br/>
	<input class="button-primary" type="submit" name="do_action" value="データの反映を実行する"></input>
	<input class="button" type="submit" name="cancel" value="キャンセル"></input>
	</form>
<?php
}

function hpb_get_pulldown( $status, $type, $id ) {
	$content = ''; 
	if( $type === PULLDOWN_TYPE_ADD ) {
		$content = '<td class="hpb_status_button"><ul><li><label class="status_add selected"><input name="'.$id.'" type="radio" value="add" checked/></label></li><li><label class="status_keep"><input name="'.$id.'" type="radio" value="noaction"/></label></li></ul><div class="hpb_clearboth"/></td>';
	} else if( $type === PULLDOWN_TYPE_UPDATE ) {
		$content = '<td class="hpb_status_button"><ul><li><label class="status_update selected"><input name="'.$id.'" type="radio" value="update" checked/></label></li><li><label class="status_keep"><input name="'.$id.'" type="radio" value="noaction"/></label></li></ul><div class="hpb_clearboth"/></td>';
	} else if( $type === PULLDOWN_TYPE_TRASH ) {
		$content = '<td class="hpb_status_button"><ul><li><label class="status_trash selected"><input name="'.$id.'" type="radio" value="trash" checked/></label></li><li><label class="status_keep"><input name="'.$id.'" type="radio" value="noaction"/></label></li></ul><div class="hpb_clearboth"/></td>';
	} 
	return $content;
}

function hpb_import_xml( $post ) {
	$content = file_get_contents( HPB_PLUGINDATA_DIR.'/usercontents.xml' );
	if( $content == false ){
		return;
	}
	$xml = simplexml_load_string( $content, 'SimpleXMLElement', LIBXML_NOCDATA );
 	if( $xml == false ){
		return;
	}
	update_option( 'hpb_activate_theme', $_POST['activate_theme'] );
	if( $_POST['activate_theme'] == 1 ) {
		hpb_change_theme( strval( $xml->theme_dir ) );
	}
	$blogdescription = esc_html( $xml->blogdescription );
	update_option( 'blogdescription', $blogdescription );
	$blogname = esc_html( $xml->blogname );
	update_option( 'blogname', $blogname );
	//create nav menu
	$menus = array(); 
	foreach ( $xml->menu as $menu ) {
		$exist_menu = get_term_by( 'name', strval( $menu->name ), 'nav_menu' );
		if( $exist_menu == false ) {
			$new_menu = wp_create_nav_menu( strval( $menu->name ) );
			if( !is_wp_error( $new_menu ) ) {
				$menus[ strval( $menu->name ) ] =  wp_get_nav_menu_object( $new_menu );
				if( $new_menu ){
					$locations = get_theme_mod( 'nav_menu_locations' );
					$locations[ strval( $menu->location ) ] = $new_menu;
					set_theme_mod( 'nav_menu_locations', $locations ); 
				}
			}
		} else {
			$menus[ strval( $menu->name ) ] =  $exist_menu;
			if( $exist_menu ){
				$locations = get_theme_mod( 'nav_menu_locations' );
				$locations[ strval( $menu->location ) ] = $exist_menu->term_id;
				set_theme_mod( 'nav_menu_locations', $locations ); 
			}
		}
	}

	// category
	foreach ( $xml->category as $category ){
		hpb_insert_category( strval( $category->cat_name ), strval( $category->category_description ), strval( $category->category_nicename ), strval( $category->taxonomy ) );
	}

	//delete page
	foreach( $post as $key=>$value ) {
		if( preg_match( '/^[0-9]+$/', $key ) ) {
			if( $value == 'trash' ) {
				if( wp_delete_post( $key ) != false ) {
					//delete custom menu
					_wp_delete_post_menu_item( $key );
				}
			} else if( $value == 'delete' ) {
				wp_delete_post( $key, true );
			}
		}
	}

	// post
	$checkList = array();
	$index_xml_item = 0;

	$exists = get_pages( array( 'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private', 'inherit' ) ) );
	foreach ( $xml->item as $item ) {
		$index_xml_item = $index_xml_item + 1;
		$new_page = 0;
		$bexist = false;
		$title = esc_html( $item->title );
		if( $item->post_type == 'page' ){
			foreach ( $exists as $exist ){
				if( get_post_status( $exist->ID ) != 'trash' ) {
					if( $exist->post_title == $title || $exist->post_title == $item->title ){
						$bexist = true;
						$new_page = $exist->ID;			
					}
				}
			}
		}
		if( $item->post_type != 'post' && $item->post_type != 'page' ){
			$custom_post_query = array(
					'post_type' => strval( $item->post_type )
			);		
			$custom_type_posts = get_posts( $custom_post_query );
			if( count( $custom_type_posts ) > 0 && $checkList[ strval( $item->post_type ) ]!=1 ){
				continue;
			} else {
				$checkList[ strval( $item->post_type ) ] = 1;
			}
		}
		if( $bexist == false ){
			if( isset($post['hpb_'.$index_xml_item]) && $post['hpb_'.$index_xml_item] == 'noaction' ) {
			} else {
			/* add page */
			$user = wp_get_current_user();
			$add_post = array(
				'post_author'    => strval( $user->id ),
				'post_title'     => strval( $title ),
				'post_status'    => strval( $item->status ),
				'comment_status' => strval( $item->comment_status ),
				'ping_status'    => strval( $item->ping_status ),
				'post_name'      => strval( $item->post_name ),
				'post_content'   => strval( $item->content ),
				'post_type'      => strval( $item->post_type )
			);
			$new_page = wp_insert_post( $add_post );
			if( $new_page != 0 && $item->template_name != '' ){
				update_post_meta( $new_page, '_wp_page_template', strval( $item->template_name ) );
			}

			//taxonomy
			foreach( $item->term_category as $category ){
				wp_set_object_terms( $new_page, strval( $category->nicename ), strval( $category->taxonomy ) );
			}

			//attachment
			$attachments = $item->attachments;
			if( $attachments ){
				foreach( $attachments->children() as $attachment ){
					hpb_attachment( $new_page, $attachment->post_title, $attachment->post_content, $attachment->post_caption, $attachment->post_alt, $attachment->file_path, $attachment->featured_image );
				}
			}
			}
		}
		if( $bexist == true && $new_page != 0 ){
			if( isset($post[strval($new_page)]) && $post[strval($new_page)] == 'noaction' ){
			} else {
			/* update page */
			$update_post = array(
				'ID'             => $new_page,
				'post_author'    => strval( $user->id ),
				'post_title'     => strval( $title ),
				'post_status'    => strval( $item->status ),
				'comment_status' => strval( $item->comment_status ),
				'ping_status'    => strval( $item->ping_status ),
				'post_content'   => strval( $item->content ),
				'post_type'      => strval( $item->post_type )
			);
			wp_update_post( $update_post );
			update_post_meta( $new_page, '_wp_page_template', strval( $item->template_name ) );
				
			//attachment
			$attachments = $item->attachments;
			if( $attachments ){
				foreach( $attachments->children() as $attachment ){
					hpb_attachment( $new_page, $attachment->post_title, $attachment->post_content, $attachment->post_caption, $attachment->post_alt, $attachment->file_path, $attachment->featured_image );
				}
			}

			/* delete custom menu */		
			if( $_POST['update_custom_menu'] == 1 ) {
				_wp_delete_post_menu_item($new_page);
			}
			}
		}

		if( $new_page != 0 && $item->front_page == 1 ){
			update_option( 'page_on_front', $new_page );
			update_option( 'show_on_front', 'page' );
		}

		// custom menu 
		update_option( 'hpb_import_custom_menu', $_POST['update_custom_menu'] );
		if( $_POST['update_custom_menu'] == 1 ) {
			foreach( $item->custom_menu as $menu ){
				$post_id = strval( $new_page );
				if( $post_id == 0 ) {
					continue;
				}

				$custommenu_db_id = 0;
				$parent_menu = $menus[ strval( $menu->parent_menu ) ];
				$menu_title = strval( $title );
				if( $menu->menu_title != '' ) {
					$menu_title = $menu->menu_title;
				}
				$menu_items = wp_get_nav_menu_items( $parent_menu->term_id, array('post_status' => 'any') );
				foreach( (array) $menu_items as $exist_menu ) {
					if( $exist_menu->object_id == $post_id ) {
						$custommenu_db_id = $exist_menu->db_id;
					}
				}
			
				if( $parent_menu ){
					$new = array(
					'menu-item-db-id'       => $custommenu_db_id,
					'menu-item-object-id'   => strval( $new_page ),
					'menu-item-object'      => 'page',
					'menu-item-parent-id'   => 0,
					'menu-item-position'    => $menu->menu_order,
					'menu-item-type'        => 'post_type',
					'menu-item-title'       => $menu_title,
					'menu-item-url'         => '',
					'menu-item-description' => '',
					'menu-item-attr-title'  => '',
					'menu-item-status'      => 'publish',
				);
				wp_update_nav_menu_item( $parent_menu->term_id , $custommenu_db_id, $new );
				}
			}
		}
	}
	update_option('hpb_plugin_last_imported', strval( $xml->date ));
	return true;
}

function hpb_change_theme( $theme_dir ) {
	$theme = $theme_dir;
	$stylesheet = $theme_dir;

	$theme_dir = get_theme_root().'/'.$theme_dir;
	//check exist
	if( !file_exists( $theme_dir ) ){
		return;
	}
	if(get_current_theme() != $theme ){
		switch_theme( $theme , $stylesheet );
	}
}

function hpb_attachment( $id, $post_title, $post_content, $post_excerpt, $post_alt, $file_path, $featured_image ) {
	$wp_upload_dir= wp_upload_dir();
	$filename = $wp_upload_dir[ 'basedir' ].'/'.$file_path;

	//check exist
	if( !file_exists( $filename ) ){
		return;
	}

	$post_query = array(
		'post_type' => 'attachment',
		'numberposts' => -1,
		'post_status' => null,
	);
	global $wpdb;
	global $wp_version;
	$attach_id = 0;
	$attachments = get_posts( $post_query );
	foreach( $attachments as $attachment ) {
		$attach_path = get_attached_file( $attachment->ID );
		if( $attach_path == $filename ) {
			if($attach_id != 0 ){
				if($wp_version >= 3.4 && $wp_version < 3.5){
					hpb_delete_attachment($attachment->ID);
				}
			} else {
				$attach_id = $attachment->ID;
			}
		}
	}

	if( $attach_id == 0 ) {
		$wp_filetype = wp_check_filetype( $filename, null );
		$attachment = array(
			'guid'           => $wp_upload_dir[ 'baseurl' ]._wp_relative_upload_path( $filename ),
			'post_mime_type' => $wp_filetype[ 'type' ],
			'post_title'     => $post_title,
			'post_content'   => $post_content,
			'post_status'    => 'inherit',
			'post_excerpt'   => $post_excerpt,
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $id );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		update_post_meta( $attach_id, '_wp_attachment_image_alt', strval($post_alt) );
	}
	//thumbnail 
	if( $featured_image == '1' ) {
		add_post_meta($id, '_thumbnail_id', $attach_id, true);
	}
}

function hpb_delete_attachment( $post_id ) {
	global $wpdb;

	if ( !$post = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $post_id) ) )
		return $post;

	if ( 'attachment' != $post->post_type )
		return false;

	delete_post_meta($post_id, '_wp_trash_meta_status');
	delete_post_meta($post_id, '_wp_trash_meta_time');

	$meta = wp_get_attachment_metadata( $post_id );
	$backup_sizes = get_post_meta( $post->ID, '_wp_attachment_backup_sizes', true );
	$file = get_attached_file( $post_id );

	if ( is_multisite() )
		delete_transient( 'dirsize_cache' );

	wp_delete_object_term_relationships($post_id, array('category', 'post_tag'));
	wp_delete_object_term_relationships($post_id, get_object_taxonomies($post->post_type));

	delete_metadata( 'post', null, '_thumbnail_id', $post_id, true ); // delete all for any posts.

	$comment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d", $post_id ));
	foreach ( $comment_ids as $comment_id )
		wp_delete_comment( $comment_id, true );

	$post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d ", $post_id ));
	foreach ( $post_meta_ids as $mid )
		delete_metadata_by_mid( 'post', $mid );

	$wpdb->delete( $wpdb->posts, array( 'ID' => $post_id ) );

	clean_post_cache( $post );
}

function hpb_insert_category( $cat_name, $category_description, $category_nicename, $taxonomy ) {
	$catarr = array(
		'cat_name'             => $cat_name,
		'category_description' => $category_description,
		'category_nicename'    => $category_nicename,
		'taxonomy'             => $taxonomy
	);
	wp_insert_category( $catarr, $wp_error );
}

?>