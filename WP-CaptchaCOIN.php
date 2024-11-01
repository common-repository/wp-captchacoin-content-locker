<?php
/*
Plugin Name: WP CaptchaCO.IN Content Locker
Plugin URI: https://www.captchaco.in/
Description: The WP CaptchaCO.IN enables you to "lock" portions or complete posts/pages contents and ask for Bitcoins cryptopayment to unlock.
Author: gprialde
Version: 1.3
Author URI: https://www.captchaco.in/
License: GPLv2 or later
*/
class WP_CaptchaCOIN {
	public function __construct() {
		add_shortcode('wp_captchacoin_popup', array($this, 'shortcode_popup'));
		add_action('admin_menu', array($this, 'admin_page'));
		add_action('init',array($this, 'register_session'));	
		
		add_filter( 'wp_nav_menu_args', array($this, 'wpesc_nav_menu_args' ));
		add_filter( 'wp_page_menu_args', array($this, 'wpesc_nav_menu_args' ));
		add_filter( 'wp_list_pages_excludes', array($this, 'wpesc_nav_menu_args'));
	}	

	public function register_session(){
		if( !session_id() )
			session_start();
	}

	//generate the shortcode which "locks" the content
	public function shortcode_popup($atts, $content = null) {
		global $post;
		
		$shortcode_popup_atts = shortcode_atts( array('captcha_id' => '7fe65774'), $atts );
	
		$wp_captchacoin_key = trim(get_option('wp_captchacoin_key'));
		$currency = get_option( 'wp_captchacoin_currency', 'BTC');
	
		//user paid
		if(isset($_REQUEST['wp_captchacoin_submit'])) {
			if ($_REQUEST['captchacoin'] == 'CAPTCHACOIN_VERIFIED') {
				return $content;
			}
		} 

		//captchacoin button style
		$btn = get_option( 'wp_captchacoin_btnstyle', 'standard-yellow' );
		
		$res_captcha = file_get_contents("https://www.captchaco.in/api/?key=" . $wp_captchacoin_key . "&proc=get_captcha&params=" . $shortcode_popup_atts['captcha_id']);
		$captcha = json_decode($res_captcha, true);			
		
		$code = '<!-- CaptchaCO.IN JavaScript --><div id="main_captchacoin_' . $captcha['id'] . '" class="main_captchacoin"><a href="https://www.captchaco.in/" id="captchacoin_' . $captcha['id'] . '" data-amount="' . $captcha['amount'] . '" data-id="' . $captcha['id'] . '" data-address="' . $captcha['btc_address'] . '">Bitcoin Captcha and Wallet: CaptchaCO.IN</a><script type="text/javascript" src="https://www.captchaco.in/widget/captcha?d=' . $captcha['id'] . '"></script></div>';
		
		$return = '				
					<!-- CaptchaCO.IN Form -->
					<form name="pg_frm" method="post">
						' . $code . '
						
						 <div style="text-align: center; margin-bottom: 15px;">
							<div><h2 style="margin-bottom: 5px; margin-top: 5px; padding: 0; font-size: 20px;">THIS CONTENT IS LOCKED</h2></div>
							<input type="submit" value="View Locked Content" name="wp_captchacoin_submit" style="padding: 2px 8px 2px 8px; font-weight: normal; color: #000; border: 2px solid #0a0a0a; background-color: #dddddd; cursor: pointer;">
						</div>
					</form>
				';
		
		return $return;

	}

    //wp-admin options
	public function admin_page() {
	     add_menu_page('WP CaptchaCO.IN', 'WP CaptchaCO.IN', 'add_users', 'WP_CaptchaCOIN',  array($this, 'options_page'), plugins_url('wp-captchacoin-content-locker/favicon.ico'));
	     add_submenu_page( 'WP_CaptchaCOIN', 'WP CaptchaCO.IN Statistics', 'Statistics & Shortcodes', 'add_users', 'wp_captchacoin_stats', array($this, 'statistics_page'));
	     add_submenu_page( 'WP_CaptchaCOIN', 'WP CaptchaCO.IN Install Guide', 'Installation Guidelines', 'add_users', 'wp_captchacoin_install', array($this, 'installation_page'));
	}

	public function installation_page() {
		echo file_get_contents(plugins_url( 'documentation.html', __FILE__));		
	}

	public function options_page() {	
	//update options	
	if(isset($_POST['sb_captchacoin'])) {		
		update_option('wp_captchacoin_key', $_POST['wp_captchacoin_key']);
		update_option('wp_captchacoin_currency', $_POST['wp_captchacoin_currency']);
		echo '<div class="updated bellow-h2" style="margin-left: 0px; padding: 10px; color: #00CC00;">Success! Your WP CaptchaCO.IN Options Has Been Saved...</div>';
	}	
	
	$wp_captchacoin_key = get_option('wp_captchacoin_key');
	$wp_captchacoin_currency = get_option( 'wp_captchacoin_currency', 'BTC');
	?>
	<div class="wrap">
		<h3><img src="https://www.captchaco.in/favicon.ico" align="left" style="display: inline; float: left; margin-right: 5px;">WP CaptchaCO.IN Options</h3>		
		<div style="clear:both;width:500px;border-top:1px solid #EFEFEF;margin-top:10px;margin-bottom:10px;"></div>
		
		<form method="POST" enctype="multipart/form-data">			
			<table width="100%">
				<tr style="background-color:#efefef;">
					<td>CaptchaCO.IN Application Key</td>
					<td>
						<input type="text" name="wp_captchacoin_key" value="<?=$wp_captchacoin_key?>" style="width: 250px;" />
						<div style="display: inline; color: #333; font-size: 13px; font-weight: normal">[You can get your own key at <a href="https://www.captchaco.in/" target="_blank">https://www.captchaco.in/</a>]</div>
					</td>
				</tr>
				<tr style="background-color:#efefef;">
					<td>CaptchaCO.IN Currency<div style="display: block; color: #333; font-size: 11px; font-weight: normal">(ie. BTC, USD, EUR, etc)</div></td>
					<td>
						<select name="wp_captchacoin_currency"><option value="BTC">BTC</option></select>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" name="sb_captchacoin" value="Save Options" class="button button-primary"/></td>
				</tr>
			</table>			
		</form>
		
		<div style="clear:both;width:500px;border-top:1px solid #EFEFEF;margin-top:10px;margin-bottom:10px;"></div>
		
		<?
		$res_user = file_get_contents("https://www.captchaco.in/api/?key=" . $wp_captchacoin_key . "&proc=get_user");
		$user = json_decode($res_user, true);
		
		if (!empty($user['id'])) {			
			$res_balance = file_get_contents("https://www.captchaco.in/api/?key=" . $wp_captchacoin_key . "&proc=get_balance");
			$balance = json_decode($res_balance, true);	
	
		?>
		
		<h3>Bitcoin Wallet Information <div style="color: #333; font-size: 13px; font-weight: normal">To access your Bitcoin wallet or to cash out your Bitcoins to any corresponding PayPal address, please login to <a href="https://www.captchaco.in/login.php" target="_blank">CaptchaCO.IN</a>'s main website.</div></h3>
		<p><b>Current Wallet Balance</b> = <?=number_format(floatval($balance), 8)?> BTC</p>

		<div style="clear:both;width:500px;border-top:1px solid #EFEFEF;margin-top:10px;margin-bottom:10px;"></div>
		
		<?
			if(isset($_POST['locker_captchacoin'])) {		
				if ($_POST['wp_captchacoin_locker_amount'] >= 0.005) {
					$session = session_id();
					$btc_amount = number_format(trim($_POST['wp_captchacoin_locker_amount']), 8);
					$res_create = file_get_contents("https://www.captchaco.in/api/?key=" . $wp_captchacoin_key . "&proc=create_pay_captcha&params=,," . $session . "," . $btc_amount);
					$create = json_decode($res_create, true);						
					
					if ($create[0] != NULL && $create[0] != "NULL") {
						echo '<div class="updated" style="margin-left: 0px; padding: 10px; color: #00CC00;">Success! Your Content Locker Has Been Created Please See Install Guide For The Install Instructions...</div>';	
					} else {
						echo '<div class="error" style="margin-left: 0px; padding: 10px; color: #CC0000;">Something Went Wrong While Creating Your Locker, Please Try Again...</div>';
					}					
				} else {
					echo '<div class="error" style="margin-left: 0px; padding: 10px; color: #CC0000;">A Minimum Bitcoin Amount Of 0.005 Is Required To Create A Contet Locker...</div>';					
				}				
			}		
		?>
		
		<h3>Create Bitcoin Captcha Lockers <div style="color: #333; font-size: 13px; font-weight: normal">A minimum of 0.005 BTC is required to create a content locker.</div></h3>
		<form method="POST" enctype="multipart/form-data">			
			<table width="550">
				<tr style="background-color:#efefef;">
					<td align="right">Bitcoin Amount</td>
					<td align="left" style="width: 250px;">
						<input type="text" name="wp_captchacoin_locker_amount" value="" style="width: 250px;" /><br>						
					</td>
					<td align="left"><input type="submit" name="locker_captchacoin" value="Create Content Locker" class="button button-primary"/></td>
				</tr>
			</table>			
		</form>
				
		<div><p>Check the <a href="admin.php?page=wp_captchacoin_install">install guide</a> page for instructions on how to use the content locker shortcode.</p></div>
		
		<div style="clear:both;width:500px;border-top:1px solid #EFEFEF;margin-top:10px;margin-bottom:10px;"></div>
		
		<div>
			<h3>Fees and Prices</h3>
			<p>CaptchaCO.IN is free and will always be free for everyone to use and integrate. However, to keep-up with maintenance like server and other website and staff expenses needed to keep the site and service running 24/7, <b>we will deduct a processing fee of 2% if not 0.001 BTC or which is higher per pay captcha payment transaction</b>. Which means if someone pays you through your pay captcha installation of 0.01 BTC we take 2% of it which is 0.001 BTC and you keep 0.009 BTC but if your pay captcha installation earns 0.1 BTC we will deduct a fee of 0.002 BTC which is 2% of the whole transaction.</p>
		</div>
		<? } ?>		
		
		<div style="clear:both;width:500px;border-top:1px solid #EFEFEF;margin-top:10px;margin-bottom:10px;"></div>
		<div style="padding-top: 10px; border-top: 1px solid #D0D0D0; color: #555555;">We Accept <a href="https://www.coinbase.com/checkouts/9af1594677125214ee9473a219c16c4f" target="_blank">Bitcoin Donations</a></div>
	</div>
	<?php
}

	public function statistics_page() {
		$wp_captchacoin_key = trim(get_option('wp_captchacoin_key'));
?>
		<div class="wrap">
		<h3><img src="https://www.captchaco.in/favicon.ico" align="left" style="display: inline; float: left; margin-right: 5px;">WP CaptchaCO.IN Statistics</h3>

		<div style="clear:both;width:500px;border-top:1px solid #EFEFEF;margin-top:10px;margin-bottom:10px;"></div>

		<table width="640" class="wp-list-table widefat fixed posts">
			<thead>
				<tr>
					<th style="width: 100px;"><strong>Captcha ID</strong></th>
					<th style="width: 100px;"><strong>Amount</strong></th>
					<th style="width: 80px;"><strong>Conversions</strong></th>
					<th style="width: 80px;"><strong>Earnings</strong></th>
					<th style="width: 100px;"><strong>Revenue</strong></th>
					<th style="width: 100px;"><strong>Date</strong></th>
					<th style="width: 300px;"><strong>Shortcode</strong></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$res_user = file_get_contents("https://www.captchaco.in/api/?key=" . $wp_captchacoin_key . "&proc=get_user");
				$user = json_decode($res_user, true);
				
				$res_captchas = file_get_contents("https://www.captchaco.in/api/?key=" . $wp_captchacoin_key . "&proc=get_captchas&params=" . $user['id']);
				$captchas = json_decode($res_captchas, true);			
				
				if(count($captchas)) {
					foreach($captchas as $captcha) {
						$res_conversions = file_get_contents("https://www.captchaco.in/api/?key=" . $wp_captchacoin_key . "&proc=get_conversions&params=" . $captcha['id']);
						$conversions = json_decode($res_conversions, true);	
						
						$res_earnings = file_get_contents("https://www.captchaco.in/api/?key=" . $wp_captchacoin_key . "&proc=get_earnings&params=" . $captcha['id']);
						$earnings = json_decode($res_earnings, true);							
						
						$res_revenue = file_get_contents("https://www.captchaco.in/api/?key=" . $wp_captchacoin_key . "&proc=get_total_earnings&params=" . $captcha['id']);
						$revenue = json_decode($res_earnings, true);							
						
						echo '<tr>
								<td style="width: 100px;">' . $captcha['id'] . '</td>
								<td style="width: 100px;">' . number_format($captcha['amount'], 8) . ' BTC</td>
								<td style="width: 80px;">' . count($conversions) . '</td>
								<td style="width: 80px;">' . count($earnings) . '</td>
								<td style="width: 100px;">' . number_format(floatval($revenue), 8) . ' BTC</td>
								<td style="width: 100px;">' . $captcha['create_date'] . '</td>
								<td style="width: 300px;"><input type="text" onfocus="this.select();" readonly="readonly" value="[wp_captchacoin_popup captcha_id=' . $captcha['id'] . ']<!-- YOUR CONTENT HERE -->[/wp_captchacoin_popup]" class="large-text code"></td>
							  </tr>';
					}
				}
				?>
			</tbody>
		</table>

		</div>
		
		<div style="padding-top: 10px; color: #555555;">We Accept <a href="https://www.coinbase.com/checkouts/9af1594677125214ee9473a219c16c4f" target="_blank">Bitcoin Donations</a></div>		
		<?php
	}

}

$WP_CaptchaCOIN = new WP_CaptchaCOIN;