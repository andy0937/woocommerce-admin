<?php
/**
 * WooCommerce Onboarding
 * NOTE: DO NOT edit this file in WooCommerce core, this is generated from woocommerce-admin.
 *
 * @package Woocommerce Admin
 */

/**
 * Contains backend logic for the onboarding profile and checklist feature.
 */
class WC_Admin_Onboarding {
	/**
	 * Class instance.
	 *
	 * @var WC_Admin_Onboarding instance
	 */
	protected static $instance = null;

	/**
	 * Get class instance.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook into WooCommerce.
	 */
	public function __construct() {
		add_action( 'woocommerce_components_settings', array( $this, 'component_settings' ), 20 ); // Run after WC_Admin_Loader.
		add_filter( 'woocommerce_admin_is_loading', array( $this, 'is_loading' ) );
	}

	/**
	 * Returns true if the profiler should be displayed (not completed and not skipped).
	 *
	 * @return bool
	 */
	public function should_show_profiler() {
		$onboarding_data = get_option( 'wc_onboarding_profile', array() );

		$is_completed = isset( $onboarding_data['completed'] ) && true === $onboarding_data['completed'];
		$is_skipped   = isset( $onboarding_data['skipped'] ) && true === $onboarding_data['skipped'];

		// @todo When merging to WooCommerce Core, we should set the `completed` flag to true during the upgrade progress.
		// https://github.com/woocommerce/woocommerce-admin/pull/2300#discussion_r287237498.
		return $is_completed || $is_skipped ? false : true;
	}

	/**
	 * Get a list of allowed industries for the onboarding wizard.
	 *
	 * @return array
	 */
	public static function get_allowed_industries() {
		return apply_filters(
			'woocommerce_admin_onboarding_industries',
			array(
				'fashion-apparel-accessories' => __( 'Fashion, apparel & accessories', 'woocommerce-admin' ),
				'health-beauty'               => __( 'Health & beauty', 'woocommerce-admin' ),
				'art-music-photography'       => __( 'Art, music, & photography', 'woocommerce-admin' ),
				'electronics-computers'       => __( 'Electronics & computers', 'woocommerce-admin' ),
				'food-drink'                  => __( 'Food & drink', 'woocommerce-admin' ),
				'home-furniture-garden'       => __( 'Home, furniture & garden', 'woocommerce-admin' ),
				'other'                       => __( 'Other', 'woocommerce-admin' ),
			)
		);
	}

	/**
	 * Get a list of allowed product types for the onboarding wizard.
	 *
	 * @return array
	 */
	public static function get_allowed_product_types() {
		$product_types = self::append_product_data(
			array(
				'physical'      => array(
					'label'       => __( 'Physical products', 'woocommerce-admin' ),
					'description' => __( 'Products you ship to customers.', 'woocommerce-admin' ),
				),
				'downloads'     => array(
					'label'       => __( 'Downloads', 'woocommerce-admin' ),
					'description' => __( 'Virtual products that customers download.', 'woocommerce-admin' ),
				),
				'subscriptions' => array(
					'label'   => __( 'Subscriptions', 'woocommerce-admin' ),
					'product' => 'woocommerce-subscriptions',
				),
				'memberships'   => array(
					'label'   => __( 'Memberships', 'woocommerce-admin' ),
					'product' => 'woocommerce-memberships',
				),
				'composite'     => array(
					'label'   => __( 'Composite Products', 'woocommerce-admin' ),
					'product' => 'woocommerce-composite-products',
				),
				'bookings'      => array(
					'label'   => __( 'Bookings', 'woocommerce-admin' ),
					'product' => 'WooCommerce Bookings',
				),
			)
		);

		return apply_filters( 'woocommerce_admin_onboarding_product_types', $product_types );
	}

	/**
	 * Get a list of themes for the onboarding wizard.
	 *
	 * @return array
	 */
	public static function get_themes() {
		$theme_data_transient_name = 'wc_onboarding_themes';
		$theme_data                = get_transient( $theme_data_transient_name );
		if ( false === $theme_data ) {
			// @todo This should be replaced with the real wccom URL once
			// https://github.com/Automattic/woocommerce.com/pull/6035 is merged and deployed.
			$theme_data = wp_remote_get( 'http://woocommerce.test/wp-json/wccom-extensions/1.0/search?category=themes' );
			set_transient( $theme_data_transient_name, $theme_data, DAY_IN_SECONDS );
		}

		$theme_data = json_decode( $theme_data['body'] );

		return apply_filters( 'woocommerce_admin_onboarding_themes', $theme_data->products );
	}

	/**
	 * Append dynamic product data from API.
	 *
	 * @param array $product_types Array of product types.
	 * @return array
	 */
	public static function append_product_data( $product_types ) {
		$product_data_transient_name = 'wc_onboarding_product_data';
		$woocommerce_products        = get_transient( $product_data_transient_name );
		if ( false === $woocommerce_products ) {
			$woocommerce_products = wp_remote_get( 'https://woocommerce.com/wp-json/wccom-extensions/1.0/search?category=product-type' );
			set_transient( $product_data_transient_name, $woocommerce_products, DAY_IN_SECONDS );
		}
		$product_data = json_decode( $woocommerce_products['body'] );
		$products     = array();

		// Map product data by slug.
		foreach ( $product_data->products as $product_datum ) {
			$products[ $product_datum->slug ] = $product_datum;
		}

		// Loop over product types and append data.
		foreach ( $product_types as $key => $product_type ) {
			if ( isset( $product_type['product'] ) ) {
				/* translators: Amount of product per year (e.g. Bookings - $240.00 per year) */
				$product_types[ $key ]['label']      .= sprintf( __( ' — %s per year', 'woocommerce-admin' ), html_entity_decode( $products[ $product_type['product'] ]->price ) );
				$product_types[ $key ]['description'] = $products[ $product_type['product'] ]->excerpt;
				$product_types[ $key ]['more_url']    = $products[ $product_type['product'] ]->link;
			}
		}

		return $product_types;
	}

	/**
	 * Add profiler items to component settings.
	 *
	 * @param array $settings Component settings.
	 */
	public function component_settings( $settings ) {
		$profile = get_option( 'wc_onboarding_profile', array() );

		$settings['onboarding'] = array(
			'industries' => self::get_allowed_industries(),
			'profile'    => $profile,
		);

		// Only fetch if the onboarding wizard is incomplete.
		if ( $this->should_show_profiler() ) {
			$settings['onboarding']['productTypes'] = self::get_allowed_product_types();
			$settings['onboarding']['themes']       = self::get_themes();
		}

		return $settings;
	}

	/**
	 * Let the app know that we will be showing the onboarding route, so wp-admin elements should be hidden while loading.
	 *
	 * @param bool $is_loading Indicates if the `woocommerce-admin-is-loading` should be appended or not.
	 * @return bool
	 */
	public function is_loading( $is_loading ) {
		$show_profiler = $this->should_show_profiler();
		if ( ! $show_profiler ) {
			return $is_loading;
		}
		return true;
	}
}

new WC_Admin_Onboarding();
