<?php

function create_wayne_playlist_taxonomies() {
	
	$labels = array(
		'name'                       => _x( 'Wayne Playlists', 'taxonomy general name' ),
		'singular_name'              => _x( 'Wayne Playlist', 'taxonomy singular name' ),
		'search_items'               => __( 'Search Wayne Playlists' ),
		'all_items'                  => __( 'All Wayne Playlists' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Wayne Playlist' ),
		'update_item'                => __( 'Update Wayne Playlist' ),
		'add_new_item'               => __( 'Add New Wayne Playlist' ),
		'new_item_name'              => __( 'New Wayne Playlist Name' ),
		'separate_items_with_commas' => __( 'Separate playlists with commas' ),
		'add_or_remove_items'        => __( 'Add or remove playlists' ),
		'choose_from_most_used'      => __( 'Choose from the most used playlists' ),
		'not_found'                  => __( 'No playlists found.' ),
		'menu_name'                  => __( 'Wayne Playlists' ),
	);


	register_taxonomy( "wayne_playlist", "wayne_song", array(
		'hierarchical' => true,
		'labels' => $labels, /* NOTICE: Here is where the $labels variable is used */
		'show_ui' => true,
		'query_var' => true,
		'show_in_nav_menus' => false
	));

}

// hook into the init action and call create_playlist_taxonomies() when it fires
add_action( 'init', 'create_wayne_playlist_taxonomies', 0 );


// Add song custom post type

function wayne_song_register() {

	$labels = array(
		'name' => _x('Wayne Songs', 'post type general name'),
		'singular_name' => _x('Song', 'post type singular name'),
		'add_new' => _x('Add New Wayne Song', 'wayne_song'),
		'add_new_item' => __('Add New Wayne Song'),
		'edit_item' => __('Edit Wayne Song'),
		'new_item' => __('New Wayne Song'),
		'view_item' => __('View Song'),
		'search_items' => __('Search Songs'),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'menu_icon' => null,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title', /*'editor', */'thumbnail'),
		'taxonomies' => array("wayne_playlist")
	  ); 

	register_post_type( 'wayne_song' , $args );
	/* this ads your post categories to your custom post type */
	//register_taxonomy_for_object_type('category', 'slide');
}

add_action('init', 'wayne_song_register');

// Add new song inputs
function wayne_song_info() {
	global $post;
	$wayne_song_data = get_post_custom($post->ID);

	?>

    <script>
		jQuery(function () {
			jQuery("#categorydiv h3 span").text("Add this song to a playlists:");
			jQuery("#category-tabs li:first a").text("All playlists");
			jQuery("#category-tabs li:eq(1)").hide();
			jQuery("#category-adder").hide();
			jQuery("#minor-publishing").hide();
			jQuery("#postbox-container-1 .hndle:first > span").html("Save Song");
			jQuery("#publish").attr("value", "Save Song");
			
		});
	</script>
    <style>
    #wayne_song_info label{
		font-size: 16px;
		color: #464646;
		display: inline-block;
		margin-bottom: 10px;
		font-family: Georgia, 'Times New Roman', 'Bitstream Charter', Times, serif;
		text-shadow: #fff 0 1px 0;
	}
	
	 #wayne_song_info input{
		font-size: 16px;
		
	}
	</style>
    <label>Artist</label><br>
    <input type="text" name="wayne_song_artist" style="width: 100%; max-width: 100%;" value="<?php echo $wayne_song_data['wayne_song_artist'][0]; ?>" /><br><br>
    
    
    <label>
    	Song File
    </label>
    <div><em> Manually entering a URL will override the song selected in the dropdown. Audio files should be in MP3 format.</em></div>
    <br />
    <?php
	global $wpdb;
	$table_name = $wpdb->prefix . "posts";
	$songs_query = $wpdb->prepare("SELECT * FROM $table_name WHERE post_mime_type = 'audio/mpeg'", NULL);
	$all_songs = $wpdb->get_results($songs_query);
	?>
    <select  name="wayne_song_file" style="width: 40%;">
		<?php
		foreach ($all_songs as $song)
		{
		?>
        	<option value="<?php echo $song->guid; ?>" <?php if ($wayne_song_data['wayne_song_file'][0] == $song->guid) echo "selected"; ?>><?php echo $song->post_title; ?></option>
        <?	
		}
		?>
    </select>
    &nbsp; or &nbsp; 
    <input style="width: 40%;" type="text" name="other_wayne_song_file" placeholder="Enter custom URL" value="<?php echo $wayne_song_data['other_wayne_song_file'][0]; ?>"/>
    <br><br>
	<?php
	
	
	
}


// Add custom columns to song post type
function my_edit_wayne_song_columns( $columns ) {

	$columns = array(
		
		'title' => __( 'Title' ),
		'image' => __( 'Song Artwork' ),
		'playlists' => __( 'Playlists' )
	
	);

	return $columns;
}

add_filter( 'manage_edit-wayne_song_columns', 'my_edit_wayne_song_columns' ) ;



// Add content to columns to song post type
function my_manage_wayne_song_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {
		/* If displaying the Title column. */
		case 'title' :

			/* Get the thumbnail. */
			$this_title =  the_title();
			/* If no title is found, output a default message. */
			if ( empty( $this_title ) )
				echo __( 'Unknown' );

			else
				printf( __( '%s ' ), $this_title );

			break;
		/* If displaying the image column. */
		case 'image' :

			/* Get the thumbnail. */
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail' );
			/* If no image is found, output a default message. */
			if ( empty( $thumbnail ) )
				echo __( 'Unknown' );

			else
				printf( __( '%s ' ), '<img width="75" src="' . $thumbnail[0] . '" alt=""/>' );

			break;
		
		/* If displaying the playlists column. */
		case 'playlists' :

			/* Get the playlists. */
			foreach((get_the_terms( $post_id, 'wayne_playlist' )) as $category) { 
				$playlists .=  $category->name . ', '; 
			} 
			$playlists = rtrim($playlists, ', ');
			/* If no playlists are found, output a default message. */
			if ( !$playlists )
				echo __( 'None' );

			else
				printf( __( '%s ' ), $playlists );

			break;

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

add_action( 'manage_wayne_song_posts_custom_column', 'my_manage_wayne_song_columns', 10, 2 );

// Customize the featured image labels for each custom post type
function swap_featured_image_metabox_wayne_song($translation, $text, $domain) {
	global $post;
	$translations = get_translations_for_domain( $domain);
	switch( $post->post_type ){
		
		case 'wayne_song':
			if ( $text == 'Featured Image')
	            return $translations->translate( 'Song Artwork' );
			/*if ( $text == 'Categories')
	            return $translations->translate( 'Performing in: ' ); */
			if ( $text == 'Remove featured image')
	            return $translations->translate( 'Remove Song Artwork' );
			
			break;
	}
	if ( $text == 'Set featured image')
		return $translations->translate( 'Select an image' );
 
	return $translation;
}

add_filter('gettext', 'swap_featured_image_metabox_wayne_song', 10, 4);

// Add custom meta boxes to custom post types
function add_meta_boxes_wayne_song(){
  
   add_meta_box("wayne_song_info", "Wayne Song Settings", "wayne_song_info", "wayne_song", "normal", "core");
}

add_action("admin_init", "add_meta_boxes_wayne_song");


// Save custom post fields
function save_details_wayne_song(){
  global $post;
  $postID = $post->ID;

  // To prevent metadata or custom fields from disappearing...
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
  return $postID;
	
	
	// Save Song data
	update_post_meta($postID, "wayne_song_file", $_POST["wayne_song_file"]);
	update_post_meta($postID, "wayne_song_artist", $_POST["wayne_song_artist"]);
	update_post_meta($postID, "other_wayne_song_artist", $_POST["other_wayne_song_artist"]);
	update_post_meta($postID, "other_wayne_song_file", $_POST["other_wayne_song_file"]);
	
	

	
}

add_action('save_post', 'save_details_wayne_song');
