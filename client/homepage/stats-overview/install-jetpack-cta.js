/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { Button } from '@wordpress/components';
import { useState } from 'react';
import PropTypes from 'prop-types';

/**
 * WooCommerce dependencies
 */
import { H, Plugins } from '@woocommerce/components';
import { getAdminLink, getSetting } from '@woocommerce/wc-admin-settings';
import { PLUGINS_STORE_NAME, useUserPreferences } from '@woocommerce/data';

/**
 * Internal dependencies
 */
import withSelect from 'wc-api/with-select';
import Connect from 'dashboard/components/connect';
import { recordEvent } from 'lib/tracks';

function InstallJetpackCta( { isJetpackConnected } ) {
	const { updateUserPreferences, ...userPrefs } = useUserPreferences();
	const [ isInstalling, setIsInstalling ] = useState( false );
	const [ isConnecting, setIsConnecting ] = useState( false );
	const [ isDismissed, setIsDismissed ] = useState(
		( userPrefs.homepage_stats || {} ).installJetpackDismissed
	);
	const plugins = getSetting( 'plugins', {
		installedPlugins: [],
		activePlugins: [],
	} );
	const isJetpackInstalled = plugins.installedPlugins.includes( 'jetpack' );
	const isJetpackActivated = plugins.activePlugins.includes( 'jetpack' );

	function install() {
		setIsInstalling( true );
		recordEvent( 'statsoverview_install_jetpack' );
	}

	function dismiss() {
		if ( isInstalling || isConnecting ) {
			return;
		}

		const homepageStats = userPrefs.homepage_stats || {};

		homepageStats.installJetpackDismissed = true;

		updateUserPreferences( { homepage_stats: homepageStats } );

		setIsDismissed( true );
		recordEvent( 'statsoverview_dismiss_install_jetpack' );
	}

	function getPluginInstaller() {
		return (
			<Plugins
				autoInstall
				onComplete={ () => {
					setIsInstalling( false );
					setIsConnecting( ! isJetpackConnected );
				} }
				onError={ () => {
					setIsInstalling( false );
				} }
				pluginSlugs={ [ 'jetpack' ] }
			/>
		);
	}

	function getConnector() {
		return (
			<Connect
				autoConnect
				onError={ () => setIsConnecting( false ) }
				redirectUrl={ getAdminLink(
					'admin.php?page=wc-admin&reset-profiler=0'
				) }
			/>
		);
	}

	if ( isDismissed || ( isJetpackInstalled && isJetpackActivated ) ) {
		return null;
	}

	return (
		<article className="woocommerce-stats-overview__install-jetpack-promo">
			<H>
				{ __( 'Get traffic stats with Jetpack', 'woocommerce-admin' ) }
			</H>
			<p>
				{ __(
					'Keep an eye on your views and visitors metrics with ' +
						'Jetpack. Requires Jetpack plugin and a WordPress.com ' +
						'account.',
					'woocommerce-admin'
				) }
			</p>
			<footer>
				<Button isPrimary onClick={ install } isBusy={ isInstalling }>
					{ ! isJetpackInstalled
						? __( 'Get Jetpack', 'woocommerce-admin' )
						: __( 'Activate Jetpack', 'woocommerce-admin' ) }
				</Button>
				<Button onClick={ dismiss } isBusy={ isInstalling }>
					{ __( 'No thanks', 'woocommerce-admin' ) }
				</Button>
			</footer>

			{ isInstalling && getPluginInstaller() }
			{ isConnecting && getConnector() }
		</article>
	);
}

InstallJetpackCta.propTypes = {
	/**
	 * Is the Jetpack plugin connected.
	 */
	isJetpackConnected: PropTypes.bool.isRequired,
};

export default compose(
	withSelect( ( select ) => {
		const { isJetpackConnected } = select( PLUGINS_STORE_NAME );

		return {
			isJetpackConnected: isJetpackConnected(),
		};
	} )
)( InstallJetpackCta );
