<?php


class thfo_messeinfo_shortcode {
	function __construct() {
		add_shortcode( 'messesinfo', array( $this, 'messesinfo_shortcode' ) );
		add_shortcode( 'messesinfo_search', array( $this, 'messesinfo_shortcode_search' ) );
	}

	public function messesinfo_shortcode( $atts ) {

		$a = shortcode_atts( array( 'id' => 'plaisir 78', 'result' => '5' ), $atts );

		$id = esc_html( $atts['id'] );

	//	$id             = explode( '/', $atts['id'] );
		$atts['result'] = intval( $atts['result'] );

		/**
		 * Transient deletion if shortcode params changed
		 */

		$search_old = get_option('messeinfo_search_shortcode' );

		if ( ! $search_old || $search_old =! $atts['id'] ){
			update_option('messeinfo_search_shortcode', $atts['id'] );
		}

		if ($search_old != $atts['id']) { delete_transient('messesinfo_data_sc'); }

		/**
		 * Transient creation to store data 24h
		 */

		$url = "http://www.messes.info/api/v2$id?userkey=messesinfo&format=json";
		$mass = get_transient( 'messesinfo_data_sc' );
		if ( ! $mass ) {
			//$url  = file_get_contents( "http://www.messes.info/api/v2/horaires/" . $id[0] . "%20" . $id[1] . "?userkey=messesinfo&format=json" );
			$url  = file_get_contents( $url );
			$mass = json_decode( $url, true );
			set_transient( 'messesinfo_data_sc', $mass, 86400 );
		}

		$shortcode = '';
		if ( isset( $mass['errorMessage'] ) ) {
			echo '<p>' . $mass['errorMessage'] . '</p>';
		} else {
			foreach ( $mass as $m ) {
				if ( is_array( $m ) ) {
					$i = 1;
					foreach ( $m as $mess ) {
						if ( $i <= $atts['result'] ){
				//		for ( $i = 0; $i <= $atts['result']; $i++ ) {
							$newdate   = date_timestamp_get( date_create( $mess['date'] ) );
							$shortcode .= '<div class="messeinfo messeinfo-' . $i . '">
                                <div class="mass-infos mass-infos-' . $i . '">
                                    <p class="mass-date mass-date-' . $i . '">
                                        <strong>' . date_i18n( 'l d F Y', $newdate ) . ' - ' . $mess['time'] . '</strong>
                                    </p>

								    ';
							if ( $mess['timeType'] === 'SUNDAYMASS' ) {
								$type      = __( 'Weekly Mass', 'messesinfo' );
								$shortcode .= '<p>' . $type . '</p>';
							};

							$shortcode .= '<a href="http://egliseinfo.catholique.fr/lieu/' . $mess['locality']['id'] . '"
                                       target="_blank">
                                        <p>' . $mess['locality']['type'] . ' ' . $mess['locality']['name'] . '</p>
                                    </a>

                                    <p>' . $mess['locality']['address'] . $mess['locality']['zipcode'] . ' ' . $mess['locality']['city'] . '</p>

                                    <a href="http://egliseinfo.catholique.fr/communaute/' . $mess['communityId'] . '"
                                       target="_blank">
                                        <p>' . $mess['locality']['sectorType'] . ' de ' . $mess['locality']['sector'] . '</p>
                                    </a>

                                </div>

                            </div>
                            <div class="clear"></div>';
						}

						$i++;
					}
				}
			}

		}

		$shortcode .= messesinfos_widget::messesinfo_promote();
		return $shortcode;
	}

	public function messesinfo_shortcode_search($atts){

		$a = shortcode_atts( array( 'result' => '5' ), $atts );
		$atts['result'] = intval( $atts['result'] );

		/**
		 * Add a form to search input
		 */
		$shortcode = '<form action="#" method="post">';
		$shortcode .= '<input type="text" name="messesinfo_search_field" >';
		$shortcode .= '<input type="submit" name="messesinfo_search_submit" >';
		$shortcode .= '</form>';

		$search_field = sanitize_text_field($_POST['messesinfo_search_field']);
		$url =file_get_contents( 'http://www.messes.info/api/v2/horaires/'. $search_field .'?userkey=messesinfos&format=json');

		$mass = json_decode( $url, true );

		if ( isset( $mass['errorMessage'] ) ) {
			echo '<p>' . $mass['errorMessage'] . '</p>';
		} else {
			foreach ( $mass as $m ) {
				if ( is_array( $m ) ) {
					$i = 1;
					foreach ( $m as $mess ) {
						//var_dump($mess);
						if ( $i <= $atts['result'] ){
							//		for ( $i = 0; $i <= $atts['result']; $i++ ) {
							$newdate   = date_timestamp_get( date_create( $mess['date'] ) );
							$shortcode .= '<div class="messeinfo messeinfo-' . $i . '">
                                <div class="mass-infos mass-infos-' . $i . '">
                                    <p class="mass-date mass-date-' . $i . '">
                                        <strong>' . date_i18n( 'l d F Y', $newdate ) . ' - ' . $mess['time'] . '</strong>
                                    </p>

								    ';
							if ( $mess['timeType'] === 'SUNDAYMASS' ) {
								$type      = __( 'Weekly Mass', 'messesinfo' );
								$shortcode .= '<p>' . $type . '</p>';
							};

							$shortcode .= '<a href="http://egliseinfo.catholique.fr/lieu/' . $mess['locality']['id'] . '"
                                       target="_blank">
                                        <p>' . $mess['locality']['type'] . ' ' . $mess['locality']['name'] . '</p>
                                    </a>

                                    <p>' . $mess['locality']['address'] . $mess['locality']['zipcode'] . ' ' . $mess['locality']['city'] . '</p>

                                    <a href="http://egliseinfo.catholique.fr/communaute/' . $mess['communityId'] . '"
                                       target="_blank">
                                        <p>' . $mess['locality']['sectorType'] . ' de ' . $mess['locality']['sector'] . '</p>
                                    </a>
                                    <div class="acf-map">
	<div class="marker" data-lat="'. $mess['locality']['latitude'].'" data-lng="'. $mess['locality']['longitude'].'"></div>
</div>

                                </div>

                            </div>
                            <div class="clear"></div>';
						}

						$i++;
					}
				}
			}

		}

		return $shortcode;
	}

	public function messesinfos_display_results($mass){
		if ( isset( $mass['errorMessage'] ) ) {
			echo '<p>' . $mass['errorMessage'] . '</p>';
		} else {
			foreach ( $mass as $m ) {
				if ( is_array( $m ) ) {
					$i = 1;
					foreach ( $m as $mess ) {
						var_dump($mess);
						if ( $i <= $atts['result'] ){
							//		for ( $i = 0; $i <= $atts['result']; $i++ ) {
							$newdate   = date_timestamp_get( date_create( $mess['date'] ) );
							$shortcode .= '<div class="messeinfo messeinfo-' . $i . '">
                                <div class="mass-infos mass-infos-' . $i . '">
                                    <p class="mass-date mass-date-' . $i . '">
                                        <strong>' . date_i18n( 'l d F Y', $newdate ) . ' - ' . $mess['time'] . '</strong>
                                    </p>

								    ';
							if ( $mess['timeType'] === 'SUNDAYMASS' ) {
								$type      = __( 'Weekly Mass', 'messesinfo' );
								$shortcode .= '<p>' . $type . '</p>';
							};

							$shortcode .= '<a href="http://egliseinfo.catholique.fr/lieu/' . $mess['locality']['id'] . '"
                                       target="_blank">
                                        <p>' . $mess['locality']['type'] . ' ' . $mess['locality']['name'] . '</p>
                                    </a>

                                    <p>' . $mess['locality']['address'] . $mess['locality']['zipcode'] . ' ' . $mess['locality']['city'] . '</p>

                                    <a href="http://egliseinfo.catholique.fr/communaute/' . $mess['communityId'] . '"
                                       target="_blank">
                                        <p>' . $mess['locality']['sectorType'] . ' de ' . $mess['locality']['sector'] . '</p>
                                    </a>

                                </div>

                            </div>
                            <div class="clear"></div>';
						}

						$i++;
					}
				}
			}

		}

		return $shortcode;

	}

}