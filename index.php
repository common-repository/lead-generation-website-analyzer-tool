<?php
/**
 * @package jmj_seo
 * @version 1.0
 */
/*
Plugin Name: Lead Generation Website Analyzer Tool
Plugin URI: https://wordpress.org/plugins/lead-generation-website-analyzer-tool/
Description: A free lead generation tool that adds a website analyzer tool to your website anywhere you want it to display.
Author: Joe Mendoza
Version: 1.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Author URI: https://www.lawyermarketing.com/
Text Domain: jmj-seo
*/
/*
Lead Generation Website Analyzer Tool is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Lead Generation Website Analyzer Tool is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Lead Generation Website Analyzer Tool. If not, see https://www.gnu.org/licenses/gpl-2.0.html
*/

// Register Style

function jmj_seo_custom_scripts() {

	wp_register_style( 'jmj-seo-css', plugins_url( '/minified/style.min.css', __FILE__ ), false, '1.0' );
	wp_enqueue_script( 'jmj-seo-js', plugins_url( '/minified/js.min.js', __FILE__ ), array(), '1.0.0', true );
	wp_localize_script( 'jmj-seo-js', 'jmj_seo', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'postid' => get_the_id()
	));
}
add_action( 'wp_enqueue_scripts', 'jmj_seo_custom_scripts' );

// shortcode to display intake form// Add Shortcode
function jmj_seo_shortcode() {
//enqueue css
	wp_enqueue_style( 'jmj-seo-css' );
	//get intro text
	$jmj_options = get_option( 'jmj_seo__settings' );
	 $key =  sanitize_text_field($jmj_options['jmj_seo__text_field_1']);
//ensure value is filled in.
	 if ($key==''){$key='Enter Your URL Below to receive a technical analysis of any website!';}
		// place form on page where shortcode is used.
	return '<div class="jmj_seo_loader_container"><div class="jmj_seo_loader"></div>Please wait...</div><div class="jmj_seo_test"><p>' . $key . '</p><form action="javascript:void(0);" method="post"><p><input type="url" placeholder="https://www.example.com" minlength="7" name="url" required></p><p><input type="submit" value="Submit" class="jmj_seo_small-padding jmj_seo_clear"></p></form></div>';	
}

add_shortcode( 'jmj-seo', 'jmj_seo_shortcode' );

//get results from pagespeed insights
function jmj_seo_get_results($url){
	$jmj_options = get_option( 'jmj_seo__settings' );
	 $key =  sanitize_text_field($jmj_options['jmj_seo__text_field_0']);
//ensure value is filled in.
	 if ($key==''){echo "Please enter Page Speed Insight API in the options page."; exit();}

// construct url and make data call
	 $request_url = 'https://www.googleapis.com/pagespeedonline/v4/runPagespeed?url=' .$url . '&key='. $key . '&screenshot=true';
	 $json_data = wp_remote_retrieve_body( wp_remote_get( $request_url,
    array(
        'timeout'     => 15,
        'httpversion' => '1.1',
    )) );
// decode json response and return
	 	$json_data = wp_remote_retrieve_body( wp_remote_get( $request_url,
    array(
        'timeout'     => 15,
        'httpversion' => '1.1',
    ) ) );
return json_decode($json_data,true);
}


function jmj_seo_first_sentence($content) {

    $pos = strpos($content, '.');
    return substr($content, 0, $pos+1);
}

function jmj_seo_base64_to_jpeg($base64_string, $output_file) {
    // open the output file for writing
    $ifp = fopen( $output_file, 'wb' ); 

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode( ',', $base64_string );

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode( $data[ 1 ] ) );

    // clean up the file resource
    fclose( $ifp ); 

    return $output_file; 
}
//Admin Menu Option for Google PagAPI Key an

add_action( 'admin_menu', 'jmj_seo__add_admin_menu' );
add_action( 'admin_init', 'jmj_seo__settings_init' );


function jmj_seo__add_admin_menu(  ) { 

	add_menu_page( 'Lead Generation Website Grader', 'Website Grader', 'manage_options', 'Lead Generation', 'jmj_seo__options_page' );
}


function jmj_seo__settings_init(  ) { 

	register_setting( 'pluginPage', 'jmj_seo__settings' );

	add_settings_section(
		'jmj_seo__pluginPage_section', 
		__( 'Plugin Settings', 'Lead Generation' ), 
		'jmj_seo__settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'jmj_seo__text_field_0', 
		__( 'Google Pagespeed API Key', 'jmj-seo' ), 
		'jmj_seo__text_field_0_render', 
		'pluginPage', 
		'jmj_seo__pluginPage_section' 
	);

	add_settings_field( 
		'jmj_seo__text_field_1', 
		__( 'Intake Form Intro Text', 'jmj-seo' ), 
		'jmj_seo__text_field_1_render', 
		'pluginPage', 
		'jmj_seo__pluginPage_section' 
	);

	add_settings_field( 
		'jmj_seo__text_field_2', 
		__( 'Request A Consultation Link', 'jmj-seo' ), 
		'jmj_seo__text_field_2_render', 
		'pluginPage', 
		'jmj_seo__pluginPage_section' 
	);
}


function jmj_seo__text_field_0_render(  ) { 

	$options = get_option( 'jmj_seo__settings' );
	?>
	<input type='text' name='jmj_seo__settings[jmj_seo__text_field_0]' value='<?php echo $options['jmj_seo__text_field_0']; ?>'><br /><br />
	Google PageSpeed API Key is free - go to <a href="https://developers.google.com/speed/docs/insights/v4/first-app#APIKey" target="_blank">https://developers.google.com/speed/docs/insights/v4/first-app#APIKey</a> to obtain a key.<br /><strong>A valid API key is required to use this plugin.</strong>	
	<?php
}

function jmj_seo__text_field_1_render(  ) { 

	$options = get_option( 'jmj_seo__settings' );
	?>
	<input type='text' name='jmj_seo__settings[jmj_seo__text_field_1]' value='<?php echo $options['jmj_seo__text_field_1']; ?>'><br /><br />
	This text acts as an introduction sentence for your intake form.<br />Default text: "<strong>Enter Your URL Below to receive a technical analysis of any website</strong>"
	<?php
}

function jmj_seo__text_field_2_render(  ) { 

	$options = get_option( 'jmj_seo__settings' );
	?>
	<input type='text' name='jmj_seo__settings[jmj_seo__text_field_2]' value='<?php echo $options['jmj_seo__text_field_2']; ?>'><br /><br />
	The link to your "contact us" or "request a quote" page.<br />Note: Include http or https prefix in link (e.g. https://www.google.com)
	<?php
}


function jmj_seo__settings_section_callback(  ) { 

	echo __( '<ul><li>Leads are sent to wordpress admin email.</li><li>Visit <a href="https://jmjwebsitedesign.com" target="_blank" />JMJ Marketing Services</a> for plugin support.</li></ul>', 'jmj-seo' );
}


function jmj_seo__options_page(  ) { 

	?>
	<form action='options.php' method='post'>

		<h2>Lead Generation Website Grader by <a href="https://jmjwebsitedesign.com" target="_blank" />JMJ Marketing Services</a></h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php
}

function jmj_seo_output_results ($json_data){

//common html elements and test options
$jmj_options = get_option( 'jmj_seo__settings' );
$html_divider = "</div><div class='jmj_seo_section jmj_seo_group'>";
$cta_link =  sanitize_text_field($jmj_options['jmj_seo__text_field_2']);
$cta_button = '<a class="jmj_seo_" href="' . $cta_link . '" target="_blank"><input class="jmj_seo_title-display jmj_seo_cta jmj_seo_small-padding" type="button" value="Request Free Consultation"></a>';
$page_score_result = $json_data['ruleGroups']['SPEED']['score'];
$data = $json_data['screenshot']['data'];
$image = str_replace(array('_', '-'), array('/', '+'), $data);
$gzip_result=jmj_seo_first_sentence($json_data['formattedResults']['ruleResults']['EnableGzipCompression']['summary']['format']);
$speed_result = $json_data['formattedResults']['ruleResults']['MainResourceServerResponseTime']['summary']['args']['0']['value'];
$static_resource = $json_data['pageStats']['numberStaticResources'];
$css_resources = $json_data['pageStats']['numberCssResources'];
$js_resources = $json_data['pageStats']['numberJsResources'];

//error check
if (isset($json_data['id'])) {

$test_url = $json_data['id'];
//fallback and variable results
if ($css_resources===''){$css_resources='0';}
if ($js_resources===''){$js_resources='0';}
if ($static_resource===''){$static_resource='0';}
if (isset($json_data['formattedResults']['ruleResults']['MinimizeRenderBlockingResources']['summary']['args']['0']['value'])) {$script_resources=$json_data['formattedResults']['ruleResults']['MinimizeRenderBlockingResources']['summary']['args']['0']['value'];}else{$script_resources='0';}
if (isset($json_data['formattedResults']['ruleResults']['MinimizeRenderBlockingResources']['summary']['args']['1']['value'])) {$css_resources=$json_data['formattedResults']['ruleResults']['MinimizeRenderBlockingResources']['summary']['args']['0']['value'];}else{$css_resources='0';}
if ($speed_result ==='https://developers.google.com/speed/docs/insights/Server'){$speed_result = ' < 600ms';}
if ($script_resources === 'https://developers.google.com/speed/docs/insights/BlockingJS'){$script_resources='0';}
if ($gzip_result ==='Compressing resources with gzip or deflate can reduce the number of bytes sent over the network.') {$gzip_result='gzip compression not detected.';}
if ($cta_link === ''){$cta_link='https://jmjwebsitedesign.com';}
if ($page_score_result>='80'){$page_score_result_analyisis='Good Job - your page applies most performance best practices and there is little headroom for further optimization. ';}elseif ($page_score_result){$page_score_result_analyisis='Not Bad - You have some performance best practices that are applied but your page is also missing some common optimizations and there is definately some headroom for improvement.';} else {$page_score_result_analyisis='Fail - Your page is not optimized and there is fairly large headroom for optimization.  Contact us for a website analysis and we can help you to improve your score.';}
if ($page_score_result>='80'){$page_score_result_label='Good';}elseif ($page_score_result){$page_score_result_label='Medium';} else {$page_score_result_label='Low';}
//output results
echo "<div class='jmj_seo_test-url'><h3>Test URL: " . $test_url . "</h3></div><div class='jmj_seo_section jmj_seo_group'>";
echo '<a href="' . $json_data['id'] . '"alt="'. $json_data['title'] .'"><img class="jmj_seo_screenshot jmj_seo_small-padding" title="' . $json_data['title'] . '" src="data:image/jpg;base64,' . $image .'" /></a>';
echo "<div class='jmj_seo_title-display jmj_seo_test-result jmj_seo_small-padding'><div class='jmj_seo_page-score'>Page Score: " . $page_score_result . "</div>";
echo '<div class="jmj_seo_progress-container"><progress value="' . $page_score_result . '" max="100"></progress></div><div class="jmj_seo_green jmj_seo_small-padding"> Rating: <strong>' . $page_score_result_label . '</strong></div><div class="jmj_seo_test_summary jmj_seo_small-padding"><strong>Summary:</strong> ' . $page_score_result_analyisis . '</div></div><hr />';

echo $html_divider;

echo "<div class='jmj_seo_col jmj_seo_span_1_of_3 jmj_seo_test-result'><div class='jmj_seo_title'>Avoid Landing Page Redirects</div><div class='jmj_seo_description'>Redirects trigger an additional HTTP request-response cycle and delay page rendering. # of redirects detected: <span class='jmj_seo_score'> " . jmj_seo_first_sentence($json_data['formattedResults']['ruleResults']['AvoidLandingPageRedirects']['summary']['format']) . "</span></div></div>";

echo "<div class='jmj_seo_col jmj_seo_span_1_of_3 jmj_seo_test-result'><div class='jmj_seo_title'>Enable gZIP Compression</div><div class='jmj_seo_description'>Enabling gzip compression can reduce the size of the transferred response, which can significantly improve the time to render your page.<br /><span class='jmj_seo_score'> " . $gzip_result . "</span></div></div>";

echo "<div class='jmj_seo_col jmj_seo_span_1_of_3 jmj_seo_test-result'><div class='jmj_seo_title'>Server Speed</div><div class='jmj_seo_description'>Faster websites equal happier visitors.  In our test, your server responded in:<span class='jmj_seo_score'> " . $speed_result . "</span></div></div>";

echo $html_divider;

echo "<div class='jmj_seo_col jmj_seo_span_1_of_3 jmj_seo_test-result'><div class='jmj_seo_title'>Number Of Static Resouces</div><div class='jmj_seo_description'>Number of static (i.e. cacheable) resources on the page:<span class='jmj_seo_score'> " . $static_resource . "</span></div></div>";

echo "<div class='jmj_seo_col jmj_seo_span_1_of_3 jmj_seo_test-result'><div class='jmj_seo_title'># of JS Resources</div><div class='jmj_seo_description'>Number of JavaScript resources referenced by the page:<span class='jmj_seo_score'> " . $js_resources . "</span></div></div>";

echo "<div class='jmj_seo_col jmj_seo_span_1_of_3 jmj_seo_test-result'><div class='jmj_seo_title'># of CSS Resources</div><div class='jmj_seo_description'>Number of CSS resources referenced by the page:<span class='jmj_seo_score'> " . $css_resources . "</span></div></div>";

echo $html_divider;

echo "<div class='jmj_seo_col jmj_seo_span_1_of_3 jmj_seo_test-result'><div class='jmj_seo_title'>Minimize Render Blocking Resources</div><div class='jmj_seo_description'>Your page has <span class='jmj_seo_score'>" . $script_resources .  " </span>blocking script resources and <span class='jmj_seo_score'>" . $css_resources .  "</span> blocking CSS resources.</div></div>";

echo "<div class='jmj_seo_col jmj_seo_span_1_of_3 jmj_seo_test-result'><div class='jmj_seo_title'>Total # Round Trips</div><div class='jmj_seo_description'>The number of round trips needed (between the browser and the server) to fully load the page:<span class='jmj_seo_score'> " . $json_data['pageStats']['numTotalRoundTrips'] . "</span></div></div>";

echo "<div class='jmj_seo_col jmj_seo_span_1_of_3 jmj_seo_test-result'><div class='jmj_seo_title'>Number Of Hosts</div><div class='jmj_seo_description'>Number of unique hosts referenced (or called) by the page:<span class='jmj_seo_score'> " . $json_data['pageStats']['numberHosts'] . "</span></div></div>";

echo $html_divider;

echo '</div><p><input class="jmj_seo_title-display jmj_seo_small-padding" type="button" value="Test Another Site" onClick="window.location.reload()">' . $cta_button . '</p>';
}else {echo '<p><strong>Hmmmm... something went wrong.  Please double check the URL and try again.</strong><br /><br /><input class="jmj_seo_title-display jmj_seo_small-padding" type="button" value="Try test again" onClick="window.location.reload()">';

}
}

add_action( 'wp_ajax_nopriv_jmj_seo_ajax_response', 'jmj_seo_ajax_response' );
add_action( 'wp_ajax_jmj_seo_ajax_response', 'jmj_seo_ajax_response' );

function jmj_seo_ajax_response() {
	 //get url from Query Parameter
     $url = esc_url_raw($_GET['data_url']);
     //process results (external API call to Google Pagespeed Insights)
     $json_data=jmj_seo_get_results($url);
     //error handler
      $ob = json_decode($json_data);
if($ob === null) {
 // if fail - try again
	$json_data=jmj_seo_get_results($url);
}
     //output results
	jmj_seo_output_results($json_data);
     //return 'success';
	wp_die();
}

?>