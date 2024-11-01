<?php
// In page Javascript
function wayne_audio_javascript () {
?>
	<script>
		var WayneDebugMode = true;
		var songPlays = 0;
		function wayneConsole (message) {
			if (WayneDebugMode == true)
				console.log(message);		
		}
		
		function convertMilliseconds (ms, p) {
		
				var pattern = p || "hh:mm:ss",
					arrayPattern = pattern.split(":"),
					clock = [ ],
					hours = Math.floor ( ms / 3600000 ), // 1 Hour = 36000 Milliseconds
					minuets = Math.floor (( ms % 3600000) / 60000), // 1 Minutes = 60000 Milliseconds
					seconds = Math.floor ((( ms % 360000) % 60000) / 1000) // 1 Second = 1000 Milliseconds
		
				// build the clock result
				function createClock(unit){
		
		
				// match the pattern to the corresponding variable
				if (pattern.match(unit)) {
					if (unit.match(/h/)) {
						addUnitToClock(hours, unit);
					}
					if (unit.match(/m/)) {
						addUnitToClock(minuets, unit);
					}
					if (unit.match(/s/)) {
						addUnitToClock(seconds, unit);
					};
					}
				}
		
				function addUnitToClock(val, unit){
		
					if ( val < 10 && unit.length === 2) {
						val = "0" + val;
					}
		
					clock.push(val); // push the values into the clock array
		
				}
		
		
				// loop over the pattern building out the clock result
				for ( var i = 0, j = arrayPattern.length; i < j; i ++ ){
		
					createClock(arrayPattern[i]);
		
				}
		
				return {
					hours : hours,
					minuets : minuets,
					seconds : seconds,
					clock : clock.join(":")
				};
		
		}
		
		<?php
		$detect = new Mobile_Detect();
		if ($detect->isMobile()) 
		{ 
		?>
			setTimeout(function () {jQuery(".audio-player-toggle").trigger("click");}, 1000);
		
		<?php
		}
		
		if (get_option("wayne_audio_autoplay_plugin") == "yes")
		{
		?>
		
			setTimeout(function () { jQuery("#play-pause-button").trigger("click"); }, 1000);
		<?php
		}
		?>
		
		
		
		jQuery(function () {
			soundManager.url = "<?php echo plugins_url('/js/soundmanager/swf', __FILE__);?>";
			
			
			// Hide audio player
			jQuery(".audio-player-toggle.open").live("click", function (e) {
				jQuery(".audio-player-container").animate({
					bottom: '-' + jQuery(".audio-player-container").height() + 'px'
				}, 300, function () {
					jQuery(".audio-player-toggle").removeClass("open");
					jQuery(".audio-player-toggle").addClass("closed");
				});
			});
			
			// Show audio player
			jQuery(".audio-player-toggle.closed").live("click", function (e) {
				jQuery(".audio-player-container").animate({
					bottom: '0px'
				}, 300, function () {
					jQuery(".audio-player-toggle").removeClass("closed");
					jQuery(".audio-player-toggle").addClass("open");
				});
			});
			
			jQuery( "#position-scrubber" ).slider({
				range: "max",
				min: 0,
				max: 0,
				value: 0,
				slide: function( event, ui ) {
					
				}
			});
			
			if (navigator.mimeTypes ["application/x-shockwave-flash"] != undefined)
			{
				// Set volume range if browser has Flash enabled
				jQuery( "#volume-scrubber" ).slider({
					range: "max",
					min: 0,
					max: 100,
					value: 50,
					slide: function( event, ui ) {
						
					}
				});
			}
			else
			{
				// Set volume range if browser doesn't have Flash enabled
				jQuery( "#volume-scrubber" ).slider({
					range: "max",
					min: 0,
					max: 1,
					value: 0.5,
					step: 0.01,
					slide: function( event, ui ) {
						
					}
				});	
			}
			
			jQuery(".audio-controls-container a").click(function (e) {
				e.preventDefault();
			});
			
			var htmlSound = new Audio ();
			
			// Set first song in playlist to be played
			jQuery(".playlist_item:first").addClass("current");
			
			var firstSong;
			var mySound;
			
			// Find playlist item with current class
			firstSong = jQuery(".playlist_item:first").attr("data-song_file");
				
			
			jQuery("#play-pause-button").live("click", function (e) {
				wayneConsole(songPlays);
				if (songPlays == 0)
				{
					
					playAudio(firstSong);
					jQuery(this).addClass("playing");
					
				}
				else
				{
					// Pause music if it has playing class
					if (jQuery(this).hasClass("playing"))
					{
						wayneConsole("stop playing");
						jQuery(this).removeClass("playing");
						
						if (navigator.mimeTypes["application/x-shockwave-flash"] != undefined)
							mySound.pause('wayne-audio-player');
						else
							htmlSound.pause();
						return;
					}
					else
					{
						wayneConsole("start playing");
						jQuery(this).addClass("playing");
						
						
						if (navigator.mimeTypes ["application/x-shockwave-flash"] != undefined)
							mySound.resume('wayne-audio-player');
						else
							htmlSound.play();
						return;	
					}
				}
				
			});
		
			
			jQuery("#next-button").live("click", function (e) {
				var thisSongFile = jQuery(".playlist_item.current").next(".playlist_item").attr("data-song_file");
				if(!thisSongFile)
				{
					jQuery(".playlist_item").removeClass("current");
					jQuery(".playlist_item:first").addClass("current");
					thisSongFile = jQuery(".playlist_item:first").attr("data-song_file");
					playAudio(thisSongFile);
				}
				else
				{
					console.log(thisSongFile);
					var foundCurrent = false;
					jQuery(".playlist_item").each(function () {
						if (foundCurrent == true)
						{
							jQuery(this).addClass("current");
							foundCurrent = false;
							return;	
						}
						
						if (jQuery(this).hasClass("current"))
						{
							jQuery(this).removeClass("current");
							foundCurrent = true;	
						}
					});
					playAudio(thisSongFile);
				
				}
			});
			
			jQuery("#previous-button").live("click", function (e) {
				var thisSongFile = jQuery(".playlist_item.current").prev(".playlist_item").attr("data-song_file");
				if(!thisSongFile)
				{
					jQuery(".playlist_item").removeClass("current");
					jQuery(".playlist_item:first").addClass("current");
					thisSongFile = jQuery(".playlist_item:first").attr("data-song_file");
					playAudio(thisSongFile);
				}
				else
				{
					
					var foundCurrent = false;
					jQuery(".playlist_item").each(function () {
						if (jQuery(this).hasClass("current"))
						{
							jQuery(this).removeClass("current");
							jQuery(this).prev(".playlist_item").addClass("current");	
						}
					});
					playAudio(thisSongFile);
				
				}
			});
			
			// Load album track preview into audio player
			jQuery(".track-preview").live("click", function (e) {
				e.preventDefault();
				
				var thisSongFile = jQuery(this).attr("data-song_file");
				var thisSongArtist = jQuery(this).attr("data-song_artist");
				var thisSongTitle =  jQuery(this).attr("data-song_title");
				
				var isInPlaylist = false;
				// Determine if selected song is already in playlist
				jQuery(".playlist_item").each(function () {
					
					// Get title and artist of playing song
					var songTitle = jQuery(this).attr("data-song_title");
					var songArtist = jQuery(this).attr("data-song_artist");
					
					if (thisSongArtist == songArtist && thisSongTitle == songTitle)
					{
						jQuery(".playlist_item").removeClass("current");
						jQuery(this).addClass("current");
						isInPlaylist = true;
					}
				
				});
				
				if (isInPlaylist == true)
				{
					playAudio(thisSongFile);
				}
				else
				{
					jQuery(".playlist_item").removeClass("current");
					var newPlaylistItem = '<li class="playlist_item current" song_title="' + thisSongTitle + '" song_artwork="" song_file="' + thisSongFile + '" song_artist="' + thisSongArtist + '"></li>';
					jQuery("#audio-player-playlist").append(newPlaylistItem);
					playAudio(thisSongFile);
				}
				
			});
			
			function playAudio (songFile) {
				songPlays++;
				wayneConsole("Loading " + songFile);
				if (navigator.mimeTypes ["application/x-shockwave-flash"] != undefined)
				{	
						
					soundManager.destroySound('wayne-audio-player');
					mySound = soundManager.createSound({
						id:'wayne-audio-player',
						url: songFile,
						onload: function() {
							var duration = mySound.duration;
							jQuery( "#position-scrubber" ).slider("option", "max", duration);
							var durationTime = convertMilliseconds(duration, "mm:ss");
							jQuery("#total-track-time").html(durationTime.clock);
							jQuery( "#position-scrubber" ).bind( "slide", function(event, ui) {
								mySound.setPosition(ui.value);
							});
							
							// Show pause button
							jQuery("#play-pause-button").addClass("playing");
						},
						whileplaying: function() {
							var position = mySound.position;
							var positionTime = convertMilliseconds(position, "mm:ss");
							jQuery("#current-track-time").html(positionTime.clock);
							jQuery( "#position-scrubber" ).slider("option", "value", position);
							var volume = jQuery( "#volume-scrubber" ).slider("option", "value");
							mySound.setVolume(volume);
						},
						whileloading: function() {
							var duration = mySound.duration;
							var durationTime = convertMilliseconds(duration, "mm:ss");
							jQuery("#total-track-time").html(durationTime.clock);
							
						},
						 onfinish: function() {
							// Play next song in playlist
							setTimeout(function () {jQuery("#next-button").trigger("click");}, 300);
						 }
						 
						 
					});
					// End soundManager.createSound
					
					mySound.play();
					jQuery("#play-pause-button").addClass("playing");
					
				}
				// End if (navigator.mimeTypes ["application/x-shockwave-flash"] != undefined)
				else
				// Player will use HTML5
				{
					htmlSound.src = songFile;
					htmlSound.load();
					
					jQuery( "#position-scrubber" ).bind( "slide", function(event, ui) {
						htmlSound.currentTime = ui.value;
					});
						
					htmlSound.addEventListener("timeupdate", function() {
						var newVolume = jQuery( "#volume-scrubber" ).slider("option", "value");
						htmlSound.volume = newVolume;
						
						var duration = htmlSound.duration * 1000;
						var durationTime = convertMilliseconds(duration, "mm:ss");
						jQuery("#total-track-time").html(durationTime.clock );
						
						var position = htmlSound.currentTime * 1000;
						var positionTime = convertMilliseconds(position, "mm:ss");
						jQuery("#current-track-time").html(positionTime.clock );
						
						jQuery( "#position-scrubber" ).slider("option", "max", duration/1000);
						jQuery( "#position-scrubber" ).slider("option", "value", position/1000);
						
					});
				
					htmlSound.addEventListener("ended", function() {
						setTimeout(function () {jQuery("#next-button").trigger("click");}, 300);
					});	
					
					htmlSound.play();
					jQuery("#play-pause-button").addClass("playing");
				}
				// End Player will use HTML5
				
				jQuery(".playlist_item").each(function () {
					if (jQuery(this).hasClass("current"))
					{
						// Set title, artist, and artwork of playing song
						var songArtwork = jQuery(this).attr("data-song_artwork");
						var songTitle = jQuery(this).attr("data-song_title");
						var songArtist = jQuery(this).attr("data-song_artist");
						
						if (songArtwork != "")
						{
							jQuery(".audio-player-artwork img").attr("src", songArtwork);
							jQuery(".audio-player-artwork").show();
						}
						else
						{
							jQuery(".audio-player-artwork img").attr("src", "");
							jQuery(".audio-player-artwork").hide();
						}
						
						jQuery(".audio-player-song-title").html(songTitle);
						jQuery(".audio-player-song-artist").html(songArtist);
					}
				});
				
				
			}
			// End playAudio()
		});
	</script>	
<?php	
}

add_action('wp_footer', 'wayne_audio_javascript');
?>