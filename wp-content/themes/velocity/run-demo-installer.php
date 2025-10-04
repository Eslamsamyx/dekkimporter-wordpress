<?php
/**
 * CLI Demo Content Installer
 *
 * Run with: wp eval-file run-demo-installer.php
 */

// Check if running in WP-CLI
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    die( 'This script can only be run via WP-CLI' );
}

// Load demo content files
require_once __DIR__ . '/inc/demo-content.php';
require_once __DIR__ . '/inc/demo-content-pages.php';

WP_CLI::log( '🚀 Starting Velocity Theme Demo Content Installation...' );
WP_CLI::log( '' );

// Check if already installed
if ( velocity_is_demo_installed() ) {
    WP_CLI::warning( 'Demo content is already installed!' );
    WP_CLI::log( 'Installed on: ' . get_option( 'velocity_demo_installed_date' ) );

    $confirm = WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );
    if ( ! $confirm ) {
        WP_CLI::error( 'Use --force to reinstall demo content' );
    }

    WP_CLI::log( 'Proceeding with reinstallation...' );
}

// Run the installer
WP_CLI::log( '⏳ Installing demo content...' );

try {
    velocity_install_demo_content();

    WP_CLI::success( '✅ Demo Content Installed Successfully!' );
    WP_CLI::log( '' );
    WP_CLI::log( '📦 Created Content:' );
    WP_CLI::log( '  ✅ 6 Pages (Home, About, Services, Portfolio, Contact, Blog)' );
    WP_CLI::log( '  ✅ 8 Blog Posts with categories and tags' );
    WP_CLI::log( '  ✅ 2 Navigation Menus (Primary + Footer)' );
    WP_CLI::log( '  ✅ Widget Areas populated (Sidebar + 4 Footer columns)' );
    WP_CLI::log( '  ✅ Homepage configured as static front page' );
    WP_CLI::log( '  ✅ All pages include full HTML designs' );
    WP_CLI::log( '' );
    WP_CLI::log( '🌐 View your site at: http://localhost:8080' );

} catch ( Exception $e ) {
    WP_CLI::error( 'Installation failed: ' . $e->getMessage() );
}
