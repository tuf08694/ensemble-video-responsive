<?php
/*
Plugin Name: Ensemble Video Responsive Plugin
Description: Easily embed Ensemble Videos in your site. This version includes responsive embed codes and several embedding options.
Version: 5.6.4
*/

class Ensemble_Video {

    // Constructor
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_shortcode( 'ensemblevideo', array( $this, 'ensemblevideo_shortcode' ) );

        if ( ! get_option( 'ensemble_video' ) ) {
            if ( $this->is_network_activated() ) {
                update_site_option( 'ensemble_video', $this->default_options() );
            } else {
                update_option( 'ensemble_video', $this->default_options() );
            }
        }
    }

    // Check if the plugin is network activated
    public function is_network_activated() {
        if ( is_multisite() ) {
            return (bool) get_site_option( 'ensemble_video_network_activated' );
        }

        return false;
    }

    // Default plugin options
    public function default_options() {
        $options = array(
            'responsive' => true,
            'width' => 640,
            'height' => 360,
            'showinfo' => false,
            'modestbranding' => true,
            'autoplay' => false,
            'loop' => false,
            'rel' => false
        );

        return apply_filters( 'ensemble_video_default_options', $options );
    }

    // Shortcode function
    public function ensemblevideo_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => '',
            'width' => '',
            'height' => '',
            'responsive' => '',
            'showinfo' => '',
            'modestbranding' => '',
            'autoplay' => '',
            'loop' => '',
            'rel' => ''
        ), $atts );

        $options = get_option( 'ensemble_video' );
        $args = array();

        if ( $atts['id'] ) {
            $args['id'] = $atts['id'];
        }

        if ( $atts['width'] ) {
            $args['width'] = $atts['width'];
        } else {
            $args['width'] = $options['width'];
        }

        if ( $atts['height'] ) {
            $args['height'] = $atts['height'];
        } else {
            $args['height'] = $options['height'];
        }

        if ( $atts['responsive'] ) {
            $args['responsive'] = true;
        } else {
            $args['responsive'] = $options['responsive'];
        }

        if ( $atts['showinfo'] ) {
            $args['showinfo'] = true;
        } else {
            $args['showinfo'] = $options['showinfo'];
        }

        if ( $atts['modestbranding'] ) {
            $args['modestbranding'] = true;
        } else {
            $args['modestbranding'] = $options['modestbranding'];
        }

        if ( $atts['autoplay'] ) {
            $args['autoplay'] = true;
        } else {
            $args['autoplay'] = $options['autoplay'];
        }

        if ( $atts['loop'] ) {
            $args['loop'] = true;
        } else {
            $args['loop'] = $options['loop'];
        }

        if


	/**
	 * Returns the server version by contacting the hapi/info api
	 * @param
	 * $url - The url provided by the user
	 *
	 * @return
	 *  version from the server if successful
	 */
	function get_server_version( $url ) {
		$versionUrl = rtrim( $url, '/' ) . '/hapi/info';
		$curl       = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $versionUrl );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$server_output = curl_exec( $curl );
		$vars          = json_decode( $server_output );
		curl_close( $curl );

		return $vars->applicationVersion;
	}

	/**
	 * @param $baseurl - The base url (without params)
	 * @param $fullurl - The full url, could have branding on the end (or be empty)
	 *
	 * @return
	 * The branding guid from the server.
	 */
	function get_branding_guid( $baseurl, $fullurl ) {
		$brandingsUrl = rtrim( $baseurl, '/' ) . '/hapi/v1/brandings?url=' . rtrim( $fullurl, '/' );
		$curl         = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $brandingsUrl );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$server_output = curl_exec( $curl );
		$vars          = json_decode( $server_output );
		curl_close( $curl );

		return $vars->institutionId;
	}

	/**
	 * This function is not used because it does not work on 5.4.  When all servers are on 5.5, use this
	 * @param $baseurl - the base url from the server
	 * @param $brandingName - The branding name from the url
	 *
	 * @returns the institution id
	 * The branding guid from the server.
	 */
	function get_institution_guid( $baseurl, $brandingName ) {
		$brandingsUrl = rtrim( $baseurl, '/' ) . '/hapi/v1/Brandings/Current';
		$testHeader   = 'X-EV-BrandingUrl:' . rtrim( $baseurl, '/' ) . '/' . $brandingName;
		$curl         = curl_init();
		$headers      = array( $testHeader );
		curl_setopt( $curl, CURLOPT_URL, $brandingsUrl );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$server_output = curl_exec( $curl );

		$vars = json_decode( $server_output );
		curl_close( $curl );

		return $vars->institutionId;
	}

	function update_options( $options ) {
		if ( $this->is_network_activated() ) {
			return update_site_option( 'ensemble_video', $options );
		}
		return update_option( 'ensemble_video', $options );
	}

	function admin_init() {
		//  Debug can be turned on to see what is going on with the version and branding settings
		$debug = false;
		register_setting( 'ensemble_video_options_group', 'ensemble_video', array( &$this, 'validate_options' ) );
		add_settings_section( 'ensemble_video', 'General Settings', array(
			&$this,
			'display_options_description'
		), 'ensemble_video' );
		add_settings_field( 'ensemble_video_ensemble_url', 'Ensemble Video URL', array(
			&$this,
			'display_ensemble_url_option'
		), 'ensemble_video', 'ensemble_video' );
		if ( $debug == true ) {
			add_settings_field( 'ensemble_video_base_url', 'Ensemble Video URL', array(
				&$this,
				'display_ensemble_base_url_option'
			), 'ensemble_video', 'ensemble_video' );
			add_settings_field( 'ensemble_video_ensemble_branding_name', 'Branding Name', array(
				&$this,
				'display_ensemble_branding_name'
			), 'ensemble_video', 'ensemble_video' );
			add_settings_field( 'ensemble_video_ensemble_institution_guid', 'Institution GUID', array(
				&$this,
				'display_ensemble_institution_guid'
			), 'ensemble_video', 'ensemble_video' );
		}
		add_settings_field( 'ensemble_video_ensemble_version', 'Ensemble Version', array(
			&$this,
			'display_ensemble_version'
		), 'ensemble_video', 'ensemble_video' );
	}

	function display_options_description() {
		?>
        <!-- <p>Configure your Ensemble Video embed defaults.</p> -->
		<?php
	}

	/**
	 * The one item that the customer needs to fill out.
	 */
	function display_ensemble_url_option() {
		$options = $this->get_options();
		?>
        <input id="ensemble_video_ensemble_url" name="ensemble_video[ensemble_url]" class="regular-text"
               value="<?php echo $options['ensemble_url']; ?>"/>
        <p class="description">Example: https://demo.ensemblevideo.com. If you don’t know your Ensemble Video URL,
            please review the <a href="https://help.ensemblevideo.com/hc/en-us/articles/360046670252">What is my Login
                URL?</a> article.
        </p>
		<?php
	}

	function display_ensemble_base_url_option() {
		$options = $this->get_options();
		echo $options['ensemble_base_url'];
	}

	function display_ensemble_branding_name() {
		$options = $this->get_options();
		echo $options['ensemble_branding_name'];
	}

	function display_ensemble_institution_guid() {
		$options = $this->get_options();
		?>
        <input id="ensemble_video_ensemble_institution_guid" name="ensemble_video[ensemble_institution_guid]"
               class="regular-text"
               value="<?php echo $options['ensemble_institution_guid']; ?>"/>
        <p class="description">This is the branding GUID from your ensemble setup. Contact your ensemble administrator
            for this value.</p>
		<?php
	}

	function display_ensemble_version() {
		$options = $this->get_options();
		if ( empty ( $options['ensemble_version'] ) ) {
			echo "Please click \"Save Changes\" to verify your ensemble version ";
		}
		echo $options['ensemble_version'];
	}

	// Save network settings
	function display_options_page() {
		$post_page = $this->is_network_activated() ? 'edit.php?action=ensemble_video' : 'options.php';
		?>
        <div class="wrap">
			<?php screen_icon( "options-general" ); ?>
            <h2>Ensemble Video Settings</h2>
            <form action="<?php echo $post_page; ?>" method="post">

				<?php settings_fields( 'ensemble_video_options_group' ); ?>
				<?php do_settings_sections( 'ensemble_video' ); ?>
                <p class="submit">
                    <input name="Submit" type="submit" class="button-primary"
                           value="<?php esc_attr_e( 'Save Changes' ); ?>"/>
                </p>
            </form>
        </div>
		<?php
	}

	function network_admin_notices() {
		$screen = get_current_screen();

		// if updated and the right page
		if ( isset( $_GET['updated'] ) &&
		     'settings_page_ensemble_video-network' === $screen->id
		) {
			$message = __( 'Options saved.', 'ensemble_video' );
			echo '<div id="message" class="updated"><p>' . $message . '</p></div>';
		}
	}

	function ensemblevideo_shortcode( $atts ) {
		$options = $this->get_options();
		if ( ! function_exists( 'ensemble_shortcode' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'inc/ensemble-shortcode.php';
		}

		return ensemble_shortcode( $atts, $options );
	}
}

/* Initialise outselves */
$GLOBALS['ensemble_video'] = new Ensemble_Video();
