<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function afi_add_config() {	 

	$api_key = "";
	$points = "";
	$conversion_rate = "";
	$points_mode = "";
	$inc_shoping_cost = "";
	$coockie_check = "";
		
	afi_post_config();
	afi_config_values( $api_key, $points, $conversion_rate, $points_mode, $inc_ship_cost, $cookie_check );

	if ( empty( $api_key ) ) {
		?>
		<div class="afi-banner afi-attention-banner">
			If you do not have a workspace at afi.to, then you can create one 
			<a href="https://cloud.afidesk.com/workspaces/new" target="_blank">here</a>
		</div>
		<?php
	}

	?>

	<form method='post'>
		<label for="afi-inp_api">
			<p> Input your API from afi.to:</p>
			<input type="text" id="afi-inp_api" name='afi-api_key' size="40"
				<?php if ( ! empty( $api_key ) ){
				?> value="<?php echo esc_attr( $api_key ); ?>" <?php
				}else { ?>placeholder="API key" <?php } ?>autofocus>
		</label>

		<label>
			<p>Points type</p>
			<select id="select_points_mode" name="afi-points_mode" onload="points_mode(this.value)" onchange="points_mode(this.value)">
				<option value="fix"
					<?php
					if ( ! empty( $points_mode ) ) {
						if ( 'fix' == $points_mode ) {
							echo " selected";
						}
					} else {
						echo " selected";
					}
					?>>Fixed points</option>
				<option value="order_percent"
					<?php
					if ( ! empty( $points_mode ) ) {
						if ( 'order_percent' == $points_mode ) {
							echo " selected";
						}
					}
					?>>Conversion rate</option>
			</select>  
		</label>

		<div id="fixed_points_input"
			<?php
			if($points_mode == 'order_percent'){
				echo ' class="hidden"';
			}
			?> >
			<label>
				<p>Points</p>
				<input type="text" name="afi-fixed_points"
					<?php 
					if(!empty($points)){
						echo 'value="' . $points . '"';
					}else {
						echo 'placeholder="12"';
					}
					?> >
			</label>
		</div>
		<div id="order_percent_input"
			<?php
			if($points_mode == 'fix' || empty($points_mode)){
				echo ' class="hidden"';
			}
			?> >
			<label>
				<p>Conversion rate</p>
				<input type="text" name="afi-conversion_rate"
					<?php 
					if(!empty($conversion_rate)){
						echo 'value="' . $conversion_rate . '"';
					}else {
						echo 'placeholder="Conversion rate"';
					}
					?> >
			</label>
			<label>
				<p>
					<input type="checkbox" name="afi-inc_ship_cost"
						<?php 
						if('1' == $inc_ship_cost){
							echo " checked";
						}
						?> > Include shipping cost
				</p>
			</label>
		</div>
		<label>
			<p>
				<input type="checkbox" name="afi-cookie_check"
					<?php 
					if('1' == $cookie_check){
						echo " checked";
					}
					?> > Only for referrals
				</p>
		</label>
		<input class="afi-but afi-reset-but" type='reset' name='res1' value="Reset">
		<input class='afi-but afi-save-but' type='submit' name='sub1' value="Save">
	</form>
	<?php
}

function afi_post_config() {
	if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

		global $wpdb;

		$wpdb->insert( $wpdb->options, ['option_name' => 'afi_api_key'] );
		$wpdb->insert( $wpdb->options, ['option_name' => 'afi_fixed_points'] );
		$wpdb->insert( $wpdb->options, ['option_name' => 'afi_conversion_rate'] );
		$wpdb->insert( $wpdb->options, ['option_name' => 'afi_points_mode'] );
		$wpdb->insert( $wpdb->options, ['option_name' => 'afi_inc_ship_cost'] );
		$wpdb->insert( $wpdb->options, ['option_name' => 'afi_cookie_check'] );

		$errors = [];

		if(empty($_POST['afi-api_key'])){
			$errors[] = 'Input API key!';
		}
		if(!preg_match( '/^[a-zA-Z0-9_]{22}+$/', $_POST['afi-api_key'] )){
			$errors[] = 'Invalid API key!';
		}
		if('fix' == $_POST['afi-points_mode'] && empty($_POST['afi-fixed_points'])){
			$errors[] = 'Input fixed points!';
		}
		if('order_percent' == $_POST['afi-points_mode'] && empty($_POST['afi-conversion_rate'])){
			$errors[] = 'Input conversion rate!';
		}

		if(empty($errors)){
			
			$wpdb->update( $wpdb->options, ['option_value' => sanitize_text_field( $_POST['afi-api_key'] )], ['option_name' => 'afi_api_key'] );	   
			$wpdb->update( $wpdb->options, ['option_value' => sanitize_text_field( $_POST['afi-points_mode'] )], ['option_name' => 'afi_points_mode'] );
			
			if(!empty($_POST['afi-cookie_check'])){
				$wpdb->update( $wpdb->options, ['option_value' => '1'], ['option_name' => 'afi_cookie_check'] );
			} else {
				$wpdb->update( $wpdb->options, ['option_value' => '0'], ['option_name' => 'afi_cookie_check'] );
			}
			
			if('fix' == $_POST['afi-points_mode']){
				$wpdb->update( $wpdb->options, ['option_value' => sanitize_text_field( $_POST['afi-fixed_points'] )], ['option_name' => 'afi_fixed_points'] );
			} elseif('order_percent' == $_POST['afi-points_mode']){
				$wpdb->update( $wpdb->options, ['option_value' => sanitize_text_field( $_POST['afi-conversion_rate'] )], ['option_name' => 'afi_conversion_rate'] );
				
				if(!empty($_POST['afi-inc_ship_cost'])){
					$wpdb->update( $wpdb->options, ['option_value' => '1'], ['option_name' => 'afi_inc_ship_cost'] );
				} else {
					$wpdb->update( $wpdb->options, ['option_value' => '0'], ['option_name' => 'afi_inc_ship_cost'] );
				}
			}
			echo "<h3 class='afi-h3'>All configuration added successfully!</h3>";
		} else {
			echo "<h3 class='afi-h3'>" . esc_attr( array_shift( $errors ) ) . "</h3>";
		}
	}
}

function afi_config_values( &$api, &$points, &$conversion_rate, &$points_mode, &$inc_ship_cost, &$cookie_check ) {

	global $wpdb;

	$api = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_api_key'" );
	$points = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_fixed_points'" );
	$conversion_rate = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_conversion_rate'" );
	$points_mode = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_points_mode'" );
	$inc_ship_cost = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_inc_ship_cost'" );
	$cookie_check = $wpdb->get_var( "select option_value from $wpdb->options where option_name = 'afi_cookie_check'" );

}
?>
