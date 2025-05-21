<?php
declare(strict_types=1);

/**
 * Plugin Name: Bystra Analytics
 * Plugin URI: https://bystra.com/plugins/analytics
 * Description: Script injections for the Header, After Opening Body and Footer. e.g. Facebook Pixel Code, Google Tag Manager, Linkedin insight tag and similar.
 * Version: 1.4
 * Author: Bystra Team
 * Author URI: https://bystra.com
 * License: GPL2
 * Text Domain: bystra-analytics
 * Domain Path: /languages
 *
 * @package Bystra\Analytics
 * @copyright Copyright (c) 2025, Bystra Team
 * @license GPL2
 */

namespace Bystra\Analytics;

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 * 
 * Handles all functionality for the Bystra Analytics plugin, including:
 * - Script injection in header, body, and footer
 * - Admin settings page with CodeMirror editor
 * - Secure script storage and retrieval
 */
class BystraAnalytics {
    /**
     * Plugin version
     */
    private const VERSION = '1.4';

    /**
     * Option name for header scripts
     */
    private const OPTION_HEADER_SCRIPTS = 'bystra_header_scripts';

    /**
     * Option name for body open scripts
     */
    private const OPTION_BODY_SCRIPTS = 'bystra_body_scripts';

    /**
     * Option name for footer scripts
     */
    private const OPTION_FOOTER_SCRIPTS = 'bystra_footer_scripts';

    /**
     * Script locations
     */
    private const LOCATIONS = [
        'header' => 'Header',
        'body' => 'After Opening Body Tag',
        'footer' => 'Footer'
    ];

    /**
     * Plugin singleton instance
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor, initialize hooks
     */
    private function __construct() {
        // Hook frontend scripts
        add_action('wp_head', [$this, 'outputHeaderScripts'], 10);
        add_action('wp_body_open', [$this, 'outputBodyScripts'], 10);
        add_action('wp_footer', [$this, 'outputFooterScripts'], 10);

        // Hook admin functionality if we're in the admin area
        if (is_admin()) {
            add_action('admin_menu', [$this, 'addAdminMenu']);
            add_action('admin_init', [$this, 'registerSettings']);
            add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        }

        // Register activation hook
        register_activation_hook(__FILE__, [$this, 'activate']);
    }

    /**
     * Plugin activation
     */
    public function activate(): void {
        // Create default empty options if they don't exist
        if (false === get_option(self::OPTION_HEADER_SCRIPTS)) {
            add_option(self::OPTION_HEADER_SCRIPTS, '');
        }
        
        if (false === get_option(self::OPTION_BODY_SCRIPTS)) {
            add_option(self::OPTION_BODY_SCRIPTS, '');
        }
        
        if (false === get_option(self::OPTION_FOOTER_SCRIPTS)) {
            add_option(self::OPTION_FOOTER_SCRIPTS, '');
        }
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueueAdminAssets(string $hook): void {
        if ('settings_page_bystra-analytics' !== $hook) {
            return;
        }

        // Add CodeMirror for script editing with syntax highlighting
        wp_enqueue_code_editor(['type' => 'text/html']);
        
        // Enqueue inline script to initialize CodeMirror
        wp_add_inline_script('code-editor', 'jQuery(function($) {
            var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
            editorSettings.codemirror = _.extend({}, editorSettings.codemirror, {
                mode: "htmlmixed",
                lineNumbers: true,
                lineWrapping: true,
                indentUnit: 2,
                tabSize: 2,
                autoRefresh: true
            });
            
            var headerEditor = wp.codeEditor.initialize($("#bystra_header_scripts"), editorSettings);
            var bodyEditor = wp.codeEditor.initialize($("#bystra_body_scripts"), editorSettings);
            var footerEditor = wp.codeEditor.initialize($("#bystra_footer_scripts"), editorSettings);
        });');
        
        // Custom styles
        wp_enqueue_style('bystra-analytics-admin', admin_url('css/code-editor.min.css'));
    }

    /**
     * Add admin menu
     */
    public function addAdminMenu(): void {
        add_options_page(
            'Bystra Analytics Settings',
            'Bystra Analytics',
            'manage_options',
            'bystra-analytics',
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Register settings
     */
    public function registerSettings(): void {
        register_setting(
            'bystra_analytics_settings',
            self::OPTION_HEADER_SCRIPTS,
            [
                'sanitize_callback' => [$this, 'sanitizeScript'],
                'default' => '',
            ]
        );

        register_setting(
            'bystra_analytics_settings',
            self::OPTION_BODY_SCRIPTS,
            [
                'sanitize_callback' => [$this, 'sanitizeScript'],
                'default' => '',
            ]
        );

        register_setting(
            'bystra_analytics_settings',
            self::OPTION_FOOTER_SCRIPTS,
            [
                'sanitize_callback' => [$this, 'sanitizeScript'],
                'default' => '',
            ]
        );

        // Add settings sections
        add_settings_section(
            'bystra_analytics_general',
            'Script Injection Settings',
            [$this, 'renderSettingsSectionDescription'],
            'bystra-analytics'
        );

        // Add settings fields for each location
        foreach (self::LOCATIONS as $key => $label) {
            add_settings_field(
                'bystra_' . $key . '_scripts',
                $label . ' Scripts',
                [$this, 'render' . ucfirst($key) . 'Field'],
                'bystra-analytics',
                'bystra_analytics_general'
            );
        }
    }

    /**
     * Settings section description
     */
    public function renderSettingsSectionDescription(): void {
        echo '<p>' . esc_html__(
            'Add your tracking and analytics scripts here. These will be injected into your site at the appropriate locations.', 
            'bystra-analytics'
        ) . '</p>';
    }

    /**
     * Render the header field
     */
    public function renderHeaderField(): void {
        $value = get_option(self::OPTION_HEADER_SCRIPTS, '');
        $this->renderCodeEditor(self::OPTION_HEADER_SCRIPTS, $value, 'Scripts to be placed in the <head> section');
    }
    
    /**
     * Render the body field
     */
    public function renderBodyField(): void {
        $value = get_option(self::OPTION_BODY_SCRIPTS, '');
        $this->renderCodeEditor(self::OPTION_BODY_SCRIPTS, $value, 'Scripts to be placed immediately after the opening <body> tag');
    }
    
    /**
     * Render the footer field
     */
    public function renderFooterField(): void {
        $value = get_option(self::OPTION_FOOTER_SCRIPTS, '');
        $this->renderCodeEditor(self::OPTION_FOOTER_SCRIPTS, $value, 'Scripts to be placed before the closing </body> tag');
    }

    /**
     * Common method to render code editor
     */
    private function renderCodeEditor(string $name, string $value, string $description): void {
        ?>
<div class="bystra-code-editor-wrapper">
    <p class="description"><?php echo esc_html($description); ?></p>
    <textarea id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>" class="large-text code" rows="10"><?php echo esc_textarea($value); ?></textarea>
</div>
<?php
    }

    /**
     * Sanitize script content
     * 
     * @param string $input The input script content
     * @return string Sanitized script content
     */
    public function sanitizeScript($input): string {
        // Scripts often contain HTML and JavaScript that would be removed by wp_kses_post
        // For script tags, we'll use a less aggressive sanitization approach
        // Note: This approach prioritizes functionality over maximum security
        // In a production environment, consider using a more robust sanitization approach
        
        // Basic XSS prevention by removing PHP and similar tags
        $disallowed = [
            '<?php', '<?=', '<?', '<%',  // PHP opening tags
            '?>', // PHP closing tag
'<script\x20type="text /php"', // Alternate PHP opening tags '<%=', '%>' , // ASP tags 'javascript:alert' , // Basic JavaScript alerts 'javascript:eval' , // Eval functions (high risk) 'data:text/html' , // Data URI HTML execution 'base64' , // Often used in obfuscation 'document.cookie' , // Cookie theft 'onload=' , // Inline event handlers 'onerror=' ]; return str_replace($disallowed, '' , $input); } /** * Render settings page */ public function renderSettingsPage(): void { // Check user capability if (!current_user_can('manage_options')) { return; } // Add admin notice if settings were updated if (isset($_GET['settings-updated'])) { add_settings_error( 'bystra_analytics_messages' , 'bystra_analytics_message' , __('Settings Saved', 'bystra-analytics' ), 'updated' ); } // Start the settings form ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
                // Add settings fields with nonce for security
                settings_fields('bystra_analytics_settings');
                do_settings_sections('bystra-analytics');
                submit_button('Save Settings');
                ?>
        </form>

        <div class="bystra-help-section">
            <h2>Help</h2>
            <p>Use this plugin to add analytics and tracking scripts to your WordPress site without editing theme files.</p>
            <h3>Examples:</h3>
            <ul>
                <li><strong>Header:</strong> Google Tag Manager initialization, meta tags, preload resources</li>
                <li><strong>Body:</strong> Google Tag Manager noscript, initial viewport scripts</li>
                <li><strong>Footer:</strong> Facebook Pixel, chat widgets, other tracking codes</li>
            </ul>
            <p>For Google Tag Manager, paste the <code>&lt;script&gt;</code> part in the Header section and the <code>&lt;noscript&gt;</code> part in the Body section.</p>

            <h3>Common Script Examples:</h3>
            <div class="bystra-examples">
                <h4>Google Tag Manager (Header)</h4>
                <pre>&lt;script&gt;(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXX');&lt;/script&gt;</pre>

                <h4>Google Tag Manager (Body)</h4>
                <pre>&lt;noscript&gt;&lt;iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXX"
height="0" width="0" style="display:none;visibility:hidden"&gt;&lt;/iframe&gt;&lt;/noscript&gt;</pre>

                <h4>Facebook Pixel (Footer)</h4>
                <pre>&lt;script&gt;
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', 'YOUR-PIXEL-ID');
fbq('track', 'PageView');
&lt;/script&gt;</pre>
            </div>
        </div>
    </div>
    <style>
            .bystra-code-editor-wrapper {
                margin-bottom: 20px;
            }
            .bystra-code-editor-wrapper .CodeMirror {
                height: 250px;
                border: 1px solid #ddd;
            }
            .bystra-help-section {
                margin-top: 40px;
                background: #fff;
                padding: 20px;
                border-left: 4px solid #007cba;
            }
            .bystra-examples pre {
                background: #f5f5f5;
                padding: 10px;
                border: 1px solid #ddd;
                overflow: auto;
                font-size: 13px;
            }
        </style>
    <?php
    }

    /**
     * Output header scripts
     */
    public function outputHeaderScripts(): void {
        echo $this->getScriptContent(self::OPTION_HEADER_SCRIPTS);
    }
    
    /**
     * Output body scripts
     */
    public function outputBodyScripts(): void {
        echo $this->getScriptContent(self::OPTION_BODY_SCRIPTS);
    }
    
    /**
     * Output footer scripts
     */
    public function outputFooterScripts(): void {
        echo $this->getScriptContent(self::OPTION_FOOTER_SCRIPTS);
    }
    
    /**
     * Get script content
     */
    private function getScriptContent(string $option): string {
        $script = get_option($option, '');
        
        if (empty($script)) {
            return '';
        }
        
        // Check if it's one of our valid script options
        if (!in_array($option, [
            self::OPTION_HEADER_SCRIPTS,
            self::OPTION_BODY_SCRIPTS,
            self::OPTION_FOOTER_SCRIPTS
        ])) {
            return '';
        }
        
        // Output as-is, sanitization happens on input
        return $script;
    }
}

// Initialize the plugin
BystraAnalytics::getInstance();