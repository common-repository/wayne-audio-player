<?php
/**
 * Plugin Name: Wayne Audio Player
 * Description: Stream MP3s with a responsive audio player
 * Version: 1.0
 * Author: George Holmes II
 * Author URI: http://georgeholmesii.com
 */


require_once('wayne-song-post-type.php'); // Register song post type
require_once('mobile-detect/Mobile_Detect.php'); // Detect mobile devices

function wayneCurPageURL() {
	 $pageURL = 'http';
	 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	 $pageURL .= "://";
	 if ($_SERVER["SERVER_PORT"] != "80") {
	 	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	 } else {
	  	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	 }
	 return $pageURL;
}

function wayne_audio_activate()
{
	// Audio player settings
	if( !get_option("wayne_audio_player_enabled_plugin") ) 
	{
		update_option("wayne_audio_player_enabled_plugin", "yes");
	}
	
	if( !get_option("wayne_audio_playlist_plugin") ) 
	{
		update_option("wayne_audio_playlist_plugin", "");
	}
	
	if( !get_option("wayne_audio_autoplay_plugin") ) 
	{
		update_option("wayne_audio_autoplay_plugin", "yes");
	}
	
}

register_activation_hook(__FILE__, 'wayne_audio_activate');
register_deactivation_hook(__FILE__, 'wayne_audio_deactivate');

// Front end scripts and styles
function wayne_audio_scripts() {
	
	wp_register_style( 'wayne-audio-jquery-ui-css', plugins_url('/css/jquery-ui-smoothness.css', __FILE__));
    wp_enqueue_style( 'wayne-audio-jquery-ui-css' );
	 
	wp_register_style( 'wayne-audio-css', plugins_url('/css/wayne-audio-styles.css', __FILE__));
    wp_enqueue_style( 'wayne-audio-css' );

	wp_register_script( 'wayne-audio-soundmanager',  plugins_url('/js/soundmanager/script/soundmanager2-nodebug-jsmin.js', __FILE__), array('jquery', 'jquery-ui-slider'),'', true);
    wp_enqueue_script( 'wayne-audio-soundmanager', 'jQuery', '1.0', true );
	
}   


add_action('wp_enqueue_scripts', 'wayne_audio_scripts');

// Admin scripts and styles
function wayne_audio_admin_scripts_and_styles() {
 	wp_enqueue_script( 'jquery' );
	
	wp_register_style('wayne-audio-admin-css',  plugins_url('/css/wayne-audio-admin-styles.css', __FILE__));
	wp_enqueue_style('wayne-audio-admin-css', 'jQuery', '1.0', true );
}

add_action('admin_enqueue_scripts', 'wayne_audio_admin_scripts_and_styles');

function wayne_options_page () {
	
?>
	<script>
	jQuery(function () {
		jQuery("#wayne-player-settings-form").submit(function (e) {
			e.preventDefault();
			// Post form data
			jQuery.ajax({
				url: '<?php echo wayneCurPageURL(); ?>',
				dataType: "text",
				data: jQuery("#wayne-player-settings-form").serializeArray(),
				type: "POST",
				timeout: 20000,
				cache: false,
				beforeSend: function( ) {
					console.log(jQuery("#wayne-player-settings-form").serializeArray());
					jQuery(".wayne-save-settings").hide();
					jQuery(".ajax-spinner").show();
					
				},
				error: function (jqXHR, textStatus, errorThrown) {
					jQuery(".wayne-save-settings").show();
					jQuery(".ajax-spinner").hide();
					
				},
				success: function(data) {
					jQuery(".wayne-save-settings").show();
					jQuery(".ajax-spinner").hide();
					jQuery("#wayne-settings-saved-alert").fadeIn(300, function () {
						setTimeout(function () { jQuery("#wayne-settings-saved-alert").fadeOut(500); }, 2000);
					});
					console.log(data);
				}
			}); 
			// end jQuery.ajax	
		});
		
		// Toggle yes/no button
		jQuery(".yes-no-buttons button").live("click", function (e) {
			e.preventDefault();
			if (jQuery(this).hasClass("wayne-yes"))
			{
				jQuery(this).parent().find(".wayne-no").removeClass("button-primary");
				jQuery(this).addClass("button-primary");
				jQuery(this).parent().parent().find("input").val("yes")
			}
			else
			{
				jQuery(this).parent().find(".wayne-yes").removeClass("button-primary");
				jQuery(this).addClass("button-primary");
				jQuery(this).parent().parent().find("input").val("no")
			}
		});
		// End Toggle yes/no button
	});
	</script>
	<div class="wrap"> 
        <h2> Wayne Audio Player Options</h2>
        
        <div id="wayne-settings-saved-alert">Settings Saved!</div>
        
        <form id="wayne-player-settings-form">
        	<!-- Settings Header -->
        	<!-- <div class="wayne-settings-logo"></div> -->
            <div style="float:right;">
                <button class="wayne-save-settings button button-primary" style="margin-bottom:10px;">Save Settings</button>
                <img class="ajax-spinner" src="<?php echo plugins_url('/images/loading-black.gif', __FILE__); ?>" alt="" />
            </div>
            <div style="clear:both;"></div>
            <!-- End Settings Header -->
        	<div style="border-bottom: 1px solid rgba(170,170,170,0.8);">
            	<div class="wayne-settings-container">
                    <table class="form-table" section="audio_player" style="" cellpadding="0" cellspacing="0">
                        <tbody>
                            
                            <tr>
                                <th>
                                    Audio Player Enabled
                                    <span class="setting-description">
                                        
                                    </span>
                                </th>
                                <td>
                                    <div class="yes-no-buttons">
                                        <button class="wayne-yes button <?php if (get_option("wayne_audio_player_enabled_plugin") == "yes") echo 'button-primary'; ?>">Yes</button>
                                        <button class="wayne-no button <?php if (get_option("wayne_audio_player_enabled_plugin") == "no") echo 'button-primary'; ?>">No</button>
                                    </div>
                                    <input type="hidden" class="wayne-text-input" name="wayne_audio_player_enabled_plugin" value="<?php echo get_option("wayne_audio_player_enabled_plugin"); ?>" />
                                </td>
                            </tr>
                             <tr>
                                <th>
                                    Autoplay Enabled
                                    <span class="setting-description">
                                        
                                    </span>
                                </th>
                                <td>
                                    <div class="yes-no-buttons">
                                        <button class="wayne-yes button <?php if (get_option("wayne_audio_autoplay_plugin") == "yes") echo 'button-primary'; ?>">Yes</button>
                                        <button class="wayne-no button <?php if (get_option("wayne_audio_autoplay_plugin") == "no") echo 'button-primary'; ?>">No</button>
                                    </div>
                                    <input type="hidden" class="wayne-text-input" name="wayne_audio_autoplay_plugin" value="<?php echo get_option("wayne_audio_autoplay_plugin"); ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Choose Playlist
                                    <span class="setting-description">
                                        Choose a playlist created on the "Wayne Playlists" page to load in the audio player.
                                    </span>
                                </th>
                                <td>
                                    <select name="wayne_audio_playlist_plugin" id="wayne_audio_playlist_plugin">
                                        <?php
                                        $wayne_playlists = get_terms( 'wayne_playlist', array(
                                            'orderby' => 'name', 
                                            'order' => 'ASC'
                                        ));
                                        
                                        foreach ($wayne_playlists as $playlist)
                                        {
                                            $playlist_name = $playlist->name;
                                            $playlist_slug = $playlist->slug;
                                            error_log($playlist_slug, 0);	
                                        ?>
                                            <option value="<?php echo $playlist_slug; ?>" <?php if (get_option("wayne_audio_playlist_plugin") == $playlist_slug) echo "selected"; ?>><?php echo $playlist_name; ?></option>
                                        <?
                                        }
                                        ?>
                                    </select>
                                   
                                </td>
                            </tr>
                            
                            
                        </tbody>
                    </table>
                  </div>  
        	</div>  
                      
    	</form>
	</div>
<?

	// Save all theme settings
	if (isset($_POST['wayne_audio_player_enabled']))
	{
		foreach($_POST as $k => $v) {
			update_option($k, stripslashes($v));
		}
	
	}
}

function wayne_audio_player_help () {
?>
	<div class="wrap"> 
        <h2> Wayne Audio Player Help</h2>
        
        <h4>Wayne Audio Player Options</h4>

            <ol>
                <li> 
                    Click the Wayne Audio Player Options link in the left side menu. 
                </li>
                <li>
                	Set Audio Player Enabled to "Yes" if you want to display the audio player at the bottom of the window.
                </li>
                <li>
                    Setting Autoplay Enabled to "Yes" will cause the audio player to play the first song in your chosen playlist when site pages load.
                </li>
                <li>
                    To use the audio player, you need to choose a playlist you created from the dropdown next to Choose Playlist.
                </li>
            </ol>
            
        <h4>Creating Songs and Playlists</h4>

            <ol>
                <li>
                    First create a playlist to add your songs to. Go to the Wayne Playlists page and enter a name for your new playlist in the field under Add New Wayne Playlist. Then, click the Add New Wayne Playlist button.
                </li>
                <li>
                    Go to the Add New Wayne Song page. Enter the song title of the song and choose an image of the song artwork from the media library. Enter the song's artist and choose an audio file. 
                </li>
                <li>
                    On the right side of the page there is a list of playlists. Click the checkbox next to each playlist you want this song to appear in. Finally click the Save Song button.
                </li>
        
            </ol>                
    </div>

<?	
}

function wayne_audio_menu () {
	add_menu_page('Wayne Audio Player Options', 'Wayne Audio Player Options', 'manage_options', __FILE__,'wayne_options_page',  plugins_url('/images/wayne-icon.png', __FILE__));
	add_submenu_page(__FILE__, 'Wayne Audio Player Options','Wayne Audio Player Options','manage_options',__FILE__,'wayne_options_page');
	add_submenu_page(__FILE__, 'Wayne Audio Player Help','Wayne Audio Player Help','manage_options','wayne_audio_player_help','wayne_audio_player_help');
} // End function wayne_admin_menu

add_action('admin_menu', 'wayne_audio_menu');

require("wayne-audio-javascript.php");

function save_wayne_player_options () {
	
	// Save all plugin settings
	if (isset($_POST['wayne_audio_player_enabled_plugin']))
	{
		error_log ('saving wayne player settings', 0);
		foreach($_POST as $k => $v) {
			update_option($k, stripslashes($v));
		}
		exit();
	}
}

add_action('admin_head', 'save_wayne_player_options');

function wayne_audio_player_markup () {
	if (get_option("wayne_audio_player_enabled_plugin") == "yes")
	{
	
	
	
		$songs = NULL;
		$songs = get_posts(array(
				'post_type' => 'wayne_song',
				'taxonomy' => 'wayne_playlist',
				'term' => get_option('wayne_audio_playlist_plugin'),
				'nopaging' => true, // to show all posts in this category, could also use 'numberposts' => -1 instead
			));

		$wayne_playlist_markup .= '<ul id="audio-player-playlist">';

		foreach($songs as $song)
		{
			$song_artist = get_post_meta($song->ID, 'wayne_song_artist', true);	
			$song_file = get_post_meta($song->ID, 'wayne_song_file', true);	
			if (trim(get_post_meta($song->ID, 'other_wayne_song_file', true)) != "")
				$song_file = get_post_meta($song->ID, 'other_wayne_song_file', true);	
			$song_artwork = wp_get_attachment_image_src( get_post_thumbnail_id($song->ID), 'thumbnail' );
		
			$wayne_playlist_markup .= '<li class="playlist_item" data-song_title="' . $song->post_title .'" data-song_artwork="' . $song_artwork[0] . '" data-song_file="' . $song_file . '" data-song_artist="' . $song_artist . '"></li>';
			
		}

		$wayne_playlist_markup .= '</ul>';
		echo $wayne_playlist_markup; 
		
		?>
		<div class="audio-player-container"><div id="wayne-audio-player"></div><div class="audio-player-toggle open"></div><div class="audio-player-bar"><div class="row"><div class="small-12 large-3 columns"><div class="audio-player-artwork"><img src="" alt=""/></div><div class="audio-player-song-info"><div class="audio-player-song-title"></div><div class="audio-player-song-artist"></div></div></div><div class="small-12 large-3 columns"><div id="current-track-time">00:00</div><div id="total-track-time">00:00</div><div id="position-scrubber" class="position-scrubber"></div></div><div class="small-12 large-3 columns"><div class="audio-controls-container"><a id="previous-button"></a><a id="play-pause-button"></a><a id="next-button"></a><div style="clear: both;"></div></div></div><div class="small-12 large-3 columns"><div id="volume-scrubber" class="volume-scrubber"></div></div></div></div></div>
	
	<?php
	}
}

add_action('wp_footer', 'wayne_audio_player_markup');
?>