<?php
/**
 * Standalone Demo Content Installer for Velocity Theme
 *
 * Run this file directly to install demo content without WordPress admin
 *
 * Usage: php install-demo-content.php
 * Or visit: http://your-site.local/install-demo-content.php
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

// Security check - only allow if user is logged in as admin
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('ERROR: You must be logged in as an administrator to run this script.');
}

// Check if Velocity theme is active
$current_theme = wp_get_theme();
if ($current_theme->get('TextDomain') !== 'velocity' && $current_theme->parent() && $current_theme->parent()->get('TextDomain') !== 'velocity') {
    die('ERROR: Velocity theme must be activated first!');
}

// Include the demo content files
require_once get_template_directory() . '/inc/demo-content.php';
require_once get_template_directory() . '/inc/demo-content-pages.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Velocity Theme - Demo Content Installer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #6C5CE7;
            margin-top: 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #6C5CE7;
            color: white;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            margin: 10px 5px;
        }
        .button:hover {
            background: #5a4ec5;
        }
        .button-secondary {
            background: #00B894;
        }
        .button-secondary:hover {
            background: #00a07d;
        }
        ul {
            line-height: 1.8;
        }
        .status {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #6C5CE7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Velocity Theme - Demo Content Installer</h1>

<?php
// Check if demo is already installed
$already_installed = velocity_is_demo_installed();

if (isset($_GET['action']) && $_GET['action'] === 'install' && !$already_installed) {
    echo '<div class="info"><strong>⏳ Installing demo content...</strong> Please wait...</div>';

    // Run the installer
    velocity_install_demo_content();

    echo '<div class="success">';
    echo '<h2>✅ Demo Content Installed Successfully!</h2>';
    echo '<p>The following content has been created:</p>';
    echo '<ul>';
    echo '<li>✅ 6 Pages (Home, About, Services, Portfolio, Contact, Blog)</li>';
    echo '<li>✅ 8 Blog Posts with categories and tags</li>';
    echo '<li>✅ 2 Navigation Menus (Primary + Footer)</li>';
    echo '<li>✅ Widget Areas populated (Sidebar + 4 Footer columns)</li>';
    echo '<li>✅ Homepage configured as static front page</li>';
    echo '<li>✅ All pages include full HTML designs</li>';
    echo '</ul>';
    echo '</div>';

    echo '<div class="status">';
    echo '<p><strong>📍 What to do next:</strong></p>';
    echo '<ol>';
    echo '<li>Visit your homepage to see the demo in action</li>';
    echo '<li>Go to Pages → All Pages to see all created pages</li>';
    echo '<li>Go to Posts → All Posts to see blog posts</li>';
    echo '<li>Go to Appearance → Menus to see configured menus</li>';
    echo '<li>Start customizing with your own content!</li>';
    echo '</ol>';
    echo '</div>';

    echo '<p>';
    echo '<a href="' . home_url() . '" class="button">👁️ View Your Site</a>';
    echo '<a href="' . admin_url() . '" class="button button-secondary">🎛️ Go to Admin</a>';
    echo '</p>';

} elseif ($already_installed) {
    echo '<div class="info">';
    echo '<h2>ℹ️ Demo Content Already Installed</h2>';
    echo '<p>Demo content was installed on: <strong>' . get_option('velocity_demo_installed_date') . '</strong></p>';
    echo '<p>Your site already has the demo content. Visit your homepage to see it!</p>';
    echo '</div>';

    echo '<p>';
    echo '<a href="' . home_url() . '" class="button">👁️ View Your Site</a>';
    echo '<a href="' . admin_url() . '" class="button button-secondary">🎛️ Go to Admin</a>';
    echo '<a href="' . admin_url('themes.php?page=velocity-demo-content') . '" class="button button-secondary">⚙️ Demo Content Settings</a>';
    echo '</p>';

} else {
    echo '<div class="info">';
    echo '<p><strong>Ready to install demo content?</strong></p>';
    echo '<p>This will create:</p>';
    echo '<ul>';
    echo '<li>✅ <strong>6 Pages</strong> with full HTML designs (Home, About, Services, Portfolio, Contact, Blog)</li>';
    echo '<li>✅ <strong>8 Blog Posts</strong> with professional content, categories, and tags</li>';
    echo '<li>✅ <strong>2 Navigation Menus</strong> (Primary menu in header + Footer menu)</li>';
    echo '<li>✅ <strong>Widget Areas</strong> (Sidebar widgets + 4 Footer columns)</li>';
    echo '<li>✅ <strong>Homepage Setup</strong> (Static front page configured)</li>';
    echo '<li>✅ <strong>All Settings</strong> (Menus assigned, widgets populated)</li>';
    echo '</ul>';
    echo '<p><strong>⚠️ Note:</strong> This will NOT delete any existing content. It only adds new demo content.</p>';
    echo '</div>';

    echo '<p>';
    echo '<a href="?action=install" class="button">🚀 Install Demo Content Now</a>';
    echo '<a href="' . admin_url() . '" class="button button-secondary">← Back to Admin</a>';
    echo '</p>';
}
?>

        <hr style="margin: 40px 0; border: none; border-top: 1px solid #ddd;">

        <h3>📚 Documentation</h3>
        <p>For more information, check out these guides:</p>
        <ul>
            <li><a href="<?php echo get_template_directory_uri(); ?>/README.md" target="_blank">Complete Documentation (README.md)</a></li>
            <li><a href="<?php echo get_template_directory_uri(); ?>/DEMO-ENHANCED.md" target="_blank">Demo Content Features (DEMO-ENHANCED.md)</a></li>
            <li><a href="<?php echo get_template_directory_uri(); ?>/QUICK-START.md" target="_blank">Quick Start Guide</a></li>
        </ul>

        <p style="margin-top: 40px; color: #636E72; font-size: 0.875rem;">
            <strong>Velocity Theme v1.0.0</strong> | High-Performance WordPress Theme
        </p>
    </div>
</body>
</html>
