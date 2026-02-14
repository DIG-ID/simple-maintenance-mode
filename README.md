# Simple Maintenance Mode

**Version:** 1.0.0  
**Author:** dig.id  
**License:** GPLv2 or later  
**Requires WordPress:** 5.8+  
**Tested up to:** 6.4  

A lightweight and professional WordPress plugin to toggle maintenance mode with a single click. Perfect for site updates, maintenance work, or any situation where you need to temporarily hide your site from visitors.

---

## 🚀 Features

### Core Functionality
- **One-Click Toggle** - Enable/disable maintenance mode from the WordPress Admin Bar
- **Visual Status Indicator** - Red button when active, green when inactive
- **Admin Notice** - Clear notification in dashboard when maintenance mode is active
- **Preview Mode** - View maintenance page while logged in using `?smm_preview=1`
- **Smart Bypass** - Administrators automatically see the normal site
- **Proper HTTP Headers** - Sends 503 status code with Retry-After for SEO compliance

### Flexibility & Customization
- **Template Override System** - Copy template to your theme to customize without losing changes on updates
- **ACF Pro Integration** - Manage content via custom fields (title, description, contact info)
- **Full Shortcode Support** - Use any WordPress shortcode in the maintenance template
- **Theme Style Inheritance** - Automatically includes `wp_head()` and `wp_footer()`
- **Developer-Friendly Filters** - Customize behavior via WordPress hooks

### Security & Performance
- **Nonce Verification** - All state changes protected with security nonces
- **Capability Checks** - Proper WordPress permissions (`manage_options`)
- **Smart Exception Handling** - Automatically allows login, cron, AJAX, and REST API (configurable)
- **No Cache Interference** - Sets proper no-cache headers
- **Clean Code** - Built on WordPress Plugin Boilerplate standards

---

## 📦 Installation

### Automatic Installation
1. Upload the `simple-maintenance-mode` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Look for the toggle button in the Admin Bar

### Manual Installation
1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin

---

## 🎯 Usage

### Basic Usage

1. **Activate Maintenance Mode**
   - Look for the toggle button in the WordPress Admin Bar (top of screen)
   - Click the button to activate - it will turn red
   - Visitors now see the maintenance page

2. **Deactivate Maintenance Mode**
   - Click the red toggle button again
   - Button turns green - site is live

3. **Preview Maintenance Page**
   - Click "View Preview" in the Admin Bar dropdown
   - Or add `?smm_preview=1` to any URL while logged in

### Who Can See What?

| User Type | Sees |
|-----------|------|
| **Visitors (logged out)** | Maintenance page (503 error) |
| **Administrators** | Normal site (bypass) |
| **Logged-in users** | Normal site (if they have `manage_options` or `edit_others_pages`) |
| **Login page** | Always accessible |
| **WP Cron** | Always runs |
| **AJAX requests** | Allowed for logged-in users |
| **REST API** | Allowed for logged-in users |

---

## 🎨 Customizing the Template

### Method 1: Theme Override (Recommended)

This method preserves your changes when updating the plugin:

1. Create this folder in your theme:
   ```
   /wp-content/themes/your-theme/simple-maintenance-mode/
   ```

2. Copy the default template:
   ```
   From: /wp-content/plugins/simple-maintenance-mode/public/partials/smm-public-display.php
   To: /wp-content/themes/your-theme/simple-maintenance-mode/maintenance.php
   ```

3. Edit `maintenance.php` as desired - it's now your custom template!

### Method 2: Direct Edit (Not Recommended)

Edit the file directly in the plugin:
```
/wp-content/plugins/simple-maintenance-mode/public/partials/smm-public-display.php
```

**Warning:** Changes will be lost when updating the plugin.

### Template Variables

The template has access to these variables via `get_query_var('smm_vars')`:

```php
$smm_vars = array(
    'site_name'  => 'Your Site Name',
    'home_url'   => 'https://yoursite.com/',
    'login_url'  => 'https://yoursite.com/wp-login.php',
    'is_preview' => true/false
);
```

---

## 🔌 ACF Pro Integration

### Setup

1. Install and activate **ACF Pro**
2. Create an options page (add to your theme's `functions.php`):

```php
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title' => 'Maintenance Settings',
        'menu_title' => 'Maintenance',
        'menu_slug'  => 'smm-options',
        'capability' => 'manage_options',
        'redirect'   => false
    ));
}
```

3. Add field groups with fields named starting with `smm_`:
   - `smm_title` - Main heading
   - `smm_description` - Message text
   - `smm_address` - Contact address
   - `smm_email` - Contact email
   - `smm_phone` - Contact phone

### Using ACF Fields in Template

```php
<h1><?php echo smm_get_content( 'title', 'Default Title' ); ?></h1>
<p><?php echo smm_get_content( 'description', 'Default message' ); ?></p>
```

The `smm_get_content()` function:
- First checks for ACF field `smm_{key}` in options
- Falls back to the default value if field doesn't exist
- Can also be filtered via `smm_content_{key}` hook

---

## 💬 Using Shortcodes

The template fully supports WordPress shortcodes. Examples:

### Contact Form 7
```php
<?php echo do_shortcode( '[contact-form-7 id="123" title="Contact"]' ); ?>
```

### WooCommerce
```php
<?php echo do_shortcode( '[products limit="4" columns="4"]' ); ?>
```

### Custom Shortcodes
```php
<?php echo do_shortcode( '[your_custom_shortcode]' ); ?>
```

Since the template includes `wp_head()` and `wp_footer()`, all required styles and scripts will load automatically.

---

## 🛠️ Developer Hooks

### Filters

**Override Maintenance Status**
```php
add_filter( 'smm_enabled', function( $enabled ) {
    // Force maintenance mode based on custom logic
    return true;
});
```

**Customize Bypass Logic**
```php
add_filter( 'smm_user_can_bypass', function( $can_bypass ) {
    // Allow specific users
    if ( current_user_can( 'editor' ) ) {
        return true;
    }
    return $can_bypass;
});
```

**Allow Specific REST API Endpoints**
```php
add_filter( 'smm_allow_rest_request', function( $allow, $uri ) {
    // Allow specific API endpoint
    if ( strpos( $uri, '/wp-json/custom/v1/' ) !== false ) {
        return true;
    }
    return $allow;
}, 10, 2 );
```

**Allow Specific AJAX Actions**
```php
add_filter( 'smm_allow_ajax_action', function( $allow, $action ) {
    // Allow specific AJAX action
    if ( $action === 'my_custom_action' ) {
        return true;
    }
    return $allow;
}, 10, 2 );
```

**Allow Specific Requests**
```php
add_filter( 'smm_allow_request', function( $allow, $uri ) {
    // Allow specific page
    if ( strpos( $uri, '/special-page/' ) !== false ) {
        return true;
    }
    return $allow;
}, 10, 2 );
```

**Override Template Content**
```php
add_filter( 'smm_content_title', function( $default ) {
    return 'Custom Title from Filter';
});

add_filter( 'smm_content_description', function( $default ) {
    return 'Custom description without ACF';
});
```

**Change Template Path**
```php
add_filter( 'smm_maintenance_template', function( $template ) {
    return '/custom/path/to/template.php';
});
```

---

## 📁 File Structure

```
simple-maintenance-mode/
├── admin/                          # Admin-specific code
│   ├── class-smm-admin.php         # Admin bar, notices, toggle handler
│   ├── css/                        # Admin styles
│   ├── js/                         # Admin scripts
│   └── sass/                       # Admin Sass source
├── public/                         # Public-facing code
│   ├── class-smm-public.php        # Template redirect logic
│   ├── partials/
│   │   └── smm-public-display.php  # Default maintenance template
│   ├── css/                        # Public styles
│   ├── js/                         # Public scripts
│   └── sass/                       # Public Sass source
├── includes/                       # Core plugin classes
│   ├── class-smm.php               # Main orchestrator
│   ├── class-smm-loader.php        # Hooks registration
│   ├── class-smm-common.php        # Shared functionality
│   ├── class-smm-utils.php         # Helper functions
│   ├── class-smm-activator.php     # Activation logic
│   ├── class-smm-deactivator.php   # Deactivation logic
│   └── class-smm-i18n.php          # Internationalization
├── assets/                         # Shared assets
│   ├── css/                        # Common styles
│   └── sass/                       # Common Sass source
├── languages/                      # Translation files
├── smm.php                         # Main plugin file
├── uninstall.php                   # Cleanup on uninstall
├── webpack.mix.js                  # Laravel Mix config
├── tailwind.config.js              # Tailwind CSS config
└── package.json                    # NPM dependencies
```

---

## ⚙️ Development Setup

The plugin uses Laravel Mix + Tailwind CSS + Sass for asset compilation.

### Requirements
- Node.js 14+
- npm or yarn

### Setup
```bash
cd wp-content/plugins/simple-maintenance-mode
npm install
```

### Available Commands
```bash
npm run dev           # Development build
npm run production    # Production build (minified)
```

### Source Files
- Admin Sass: `admin/sass/smm-admin.sass`
- Public Sass: `public/sass/smm-public.sass`
- Common Sass: `assets/sass/smm-common.sass`

### Compiled Files
- Admin CSS: `admin/css/smm-admin.css`
- Public CSS: `public/css/smm-public.css`
- Common CSS: `assets/css/smm-common.css`

---

## 🔐 Security

The plugin follows WordPress security best practices:

- ✅ Nonce verification for all state changes
- ✅ Capability checks (`manage_options`)
- ✅ Input sanitization and output escaping
- ✅ Direct file access prevention
- ✅ SQL injection prevention (uses Options API)
- ✅ XSS prevention (proper escaping)

---

## 🌍 Translation Ready

The plugin is fully translatable:

- Text domain: `smm`
- Translation files: `languages/smm.pot`

To translate:
1. Use Loco Translate or Poedit
2. Create translation from `languages/smm.pot`
3. Save `.po` and `.mo` files to `languages/` folder

---

## ❓ FAQ

**Q: Will search engines index my maintenance page?**  
A: No. The plugin sends HTTP 503 status with `Retry-After` header and `X-Robots-Tag: noindex`, telling search engines the site is temporarily unavailable.

**Q: Can I allow specific users to bypass maintenance mode?**  
A: Yes! Use the `smm_user_can_bypass` filter. By default, users with `manage_options` or `edit_others_pages` capabilities can bypass.

**Q: What happens to WP Cron jobs?**  
A: They run normally. The plugin automatically allows `wp-cron.php` requests.

**Q: Can I use this with REST API?**  
A: Yes! REST API requests work for logged-in users. Use the `smm_allow_rest_request` filter to allow specific endpoints for non-logged-in users.

**Q: Does this work with caching plugins?**  
A: Yes. The plugin sets proper no-cache headers when maintenance mode is active.

**Q: Can I schedule maintenance mode?**  
A: Not built-in, but you can use `do_action` with WP Cron to programmatically toggle it:
```php
update_option( 'smm_enabled', 1 ); // Enable
update_option( 'smm_enabled', 0 ); // Disable
```

**Q: What happens if I deactivate the plugin while maintenance mode is active?**  
A: The plugin automatically disables maintenance mode when deactivated, preventing your site from being locked.

---

## 🐛 Troubleshooting

### Issue: Toggle button doesn't appear
- **Solution:** Check that you're logged in and have administrator privileges

### Issue: Maintenance page shows but I can't access admin
- **Solution:** Go directly to `/wp-admin/` or `/wp-login.php` - these are always accessible

### Issue: Styles not showing on maintenance page
- **Solution:** Ensure your theme properly calls `wp_head()` and `wp_footer()`

### Issue: Shortcodes not working
- **Solution:** Make sure the template includes `wp_head()` and `wp_footer()` hooks

---

## 📝 Changelog

### Version 1.0.0
- Initial release
- One-click toggle from Admin Bar
- Admin notice when active
- Preview mode support
- Template override system
- ACF Pro integration
- Full shortcode support
- Developer hooks and filters
- Laravel Mix + Tailwind CSS setup
- Security best practices
- Translation ready

---

## 🚧 Roadmap

Future features being considered:

- Countdown timer widget
- Scheduled maintenance (auto enable/disable)
- Multiple template presets
- Role-based bypass settings
- IP whitelist
- Maintenance activity log
- Email notifications
- Social media links widget
- Coming soon mode (separate from maintenance)

---

## 🤝 Contributing

This plugin is developed and maintained by dig.id for internal use. 

If you find bugs or have feature suggestions:
- Internal team: Contact via Slack
- External: Create an issue (if/when open sourced)

---

## 📄 License

GPLv2 or later. See [LICENSE.txt](LICENSE.txt) for details.

---

## 💡 Credits

Built with ❤️ by [dig.id](https://dig.id)

Based on [WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate)

---

## 📞 Support

For internal team support: Contact the development team via Slack

---

**Made with care for professional WordPress development** ⚡