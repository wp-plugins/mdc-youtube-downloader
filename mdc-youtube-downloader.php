<?php
/****
	* Plugin Name: MDC YouTube Downloader
	* Plugin URI: http://wordpress.org/plugins/mdc-youtube-downloader/
	* Description: MDC YouTube Downloader allows visitors to download YouTube videos directly from your WordPress site.
	* Author: Nazmul Ahsan
	* Version: 2.0.1
	* Author URI: http://nazmulahsan.me
	* Stable tag: 2.0.1
	* License: GPL2+
	* Text Domain: MedhabiDotCom
****/

include_once ('includes/mdc-option-page.php');
include_once ('includes/tinymce-editor-buttons.php');

class MDC_YouTube_Downloader{

	public function __construct(){
		add_action( 'wp_enqueue_scripts', array($this, 'mdc_wp_enqueue_scripts') );
		add_action( 'wp_footer', array($this, 'mdc_custom_style') );
		add_shortcode( 'mdc_youtube_downloader', array($this, 'mdc_youtube_downloader') );
		add_shortcode( 'youtube_downloader_form', array($this, 'mdc_youtube_downloader') );
	}

	public function mdc_wp_enqueue_scripts() {
		wp_enqueue_style( 'mdc_custom', plugins_url('css/style.css', __FILE__) );
	}

	public function mdc_custom_style(){
		$css = "<style>";
		$css .= get_option('mdc_custom_css');
		$css .= "</style>";
		echo $css;	
	}

	public function mdc_youtube_downloader(){
		$placeholder = (get_option('mdc_form_placeholder_text') == '') ? "Video ID or URL" : get_option('mdc_form_placeholder_text');
		$gener = (get_option('mdc_form_button_text') == '') ? "Generate Download Links" : get_option('mdc_form_button_text');
		$output = '<form class="form-download" method="post" id="download" action="">
			<input required type="text" name="videoid" id="videoid" size="40" placeholder="'.$placeholder.'" />
			<input class="btn btn-primary" type="submit" name="type" id="type" value="'.$gener.'" />
		</form>
		<br />
		<div class="mdc_video_div">';
		
		if(isset($_REQUEST['videoid'])){
			if(strlen($_REQUEST['videoid']) > 11){
				$video_url = $_REQUEST['videoid'];
				$vid_array = explode('?v=',$video_url);
				$my_id_full = $vid_array['1'];
				$found_id = substr($my_id_full, 0, 11);
			}
			else{
				$found_id = $_REQUEST['videoid'];
			}
			if(isset($_REQUEST['videoid']) && strlen($found_id) == 11){	
				// require_once('includes/file_get_contents.php');

				if(isset($_REQUEST['videoid'])) {
					$my_id = $found_id;
				} else {
					$output .= '<p>No video id passed in</p>';
					exit;
				}

				if(isset($_REQUEST['type'])) {
					$my_type =  $_REQUEST['type'];
				} else {
					$my_type = 'redirect';
				}

				/* First get the video info page for this video id */
				$my_video_info = 'http://www.youtube.com/get_video_info?&video_id='. $my_id;
				$my_video_info = file_get_contents($my_video_info);

				// get video name
			    $vidID = $_REQUEST['videoid'];
			    $content = file_get_contents("http://youtube.com/get_video_info?video_id=".$found_id);
				parse_str($content, $ytarr);
				$video_title = $ytarr['title'];
				/* TODO: Check return from curl for status code */

				$thumbnail_url = $title = $url_encoded_fmt_stream_map = $type = $url = '';

				parse_str($my_video_info);
				if(get_option('mdc_show_thumbnail') == 1){
					if(get_option('mdc_thumbnail_height')){
						$height = get_option('mdc_thumbnail_height');
					}
					else{
						$height = "auto";
					}
					if(get_option('mdc_thumbnail_width')){
						$width = get_option('mdc_thumbnail_width');
					}
					else{
						$width = "auto";
					}
					$output .= '<div class="mdc_floatleft"><img src="'. $thumbnail_url .'" border="0" hspace="2" vspace="2" height="'.$height.'" width="'.$width.'" class="mdc_video_thumb"></div>';
				}
				$my_title = $title;

				if(isset($url_encoded_fmt_stream_map)) {
					$my_formats_array = explode(',',$url_encoded_fmt_stream_map);
				} else {
					$output .= '<p>No encoded format stream found.</p>';
					$output .= '<p>Here is what we got from YouTube:</p>';
					$output .= $my_video_info;
				}
				if (count($my_formats_array) == 0) {
					$output .= '<p>No format stream map found - was the video id correct?</p>';
					exit;
				}

				/* create an array of available download formats */
				$avail_formats[] = '';
				$i = 0;
				$ipbits = $ip = $itag = $sig = $quality = '';
				$expire = time(); 
				/*	all video formats	*/
				foreach($my_formats_array as $format) {
					parse_str($format);
					$avail_formats[$i]['itag'] = $itag;
					$avail_formats[$i]['quality'] = $quality;
					$type = explode(';',$type);
					$avail_formats[$i]['type'] = $type[0];
					$avail_formats[$i]['url'] = urldecode($url) . '&signature=' . $sig;
					parse_str(urldecode($url));
					$avail_formats[$i]['expires'] = date("G:i:s T", $expire);
					$avail_formats[$i]['ipbits'] = $ipbits;
					$avail_formats[$i]['ip'] = $ip;
					$i++;
				}

				if ($my_type == get_option('mdc_form_button_text') || $my_type == "Generate Download Links") {
					$output .= '<div class="mdc_floatright">
						<p class="mdc_video_title">'.$video_title.'</p>
						<ul class="mdc_videos_list">';

					/* now that we have the array, print the options */
					for ($i = 0; $i < count($avail_formats); $i++) {
						$format = $avail_formats[$i]['type'];
						$format = explode('/', $format);
						$format = $format[1];

						//show quality?
						if(get_option('mdc_show_quality')){
							$item = ucfirst($format)." (Quality: ".ucfirst($avail_formats[$i]['quality']).")";
						}
						else{
							$item = ucfirst($format);
						}

						//text?
						if(get_option('mdc_download_text')) {
							$text = get_option('mdc_download_text');
						}
						else{
							$text = "Download";
						}

						$link = $link.'?forcedownload=1';
						//video link
						$link = $avail_formats[$i]['url'];
						
						$output .= '<li>'.$item.' - <a href="'.$link.'" download="'.$link.'" class="mime">'.$text.'</a></li>';
					}
					$output .= '</ul>
							</div>';
				}
				else {
					$format =  $_REQUEST['format'];
					$target_formats = '';
					switch ($format) {
						case "best":
							/* largest formats first */
							$target_formats = array('38', '37', '46', '22', '45', '35', '44', '34', '18', '43', '6', '5', '17', '13');
							break;
						case "free":
							/* Here we include WebM but prefer it over FLV */
							$target_formats = array('38', '46', '37', '45', '22', '44', '35', '43', '34', '18', '6', '5', '17', '13');
							break;
						case "ipad":
							/* here we leave out WebM video and FLV - looking for MP4 */
							$target_formats = array('37','22','18','17');
							break;
						default:
							/* If they passed in a number use it */
							if (is_numeric($format)) {
								$target_formats[] = $format;
							} else {
								$target_formats = array('38', '37', '46', '22', '45', '35', '44', '34', '18', '43', '6', '5', '17', '13');
							}
						break;
					}
				} // end of else for type not being Generate
			}//if $found_id = 11
			else{
				$output .= 'Invalid Video ID or URL';
			}
		}
		$output .= "</div>";
		return $output;
	}
}

$obj = new MDC_YouTube_Downloader;