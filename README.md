# Simple Maintenance Mode

**Contributors:** dig.id  
**Tags:** maintenance mode, under construction, admin bar, ACF, shortcodes  
**Requires at least:** 5.8  
**Tested up to:** 6.4  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** [http://www.gnu.org/licenses/gpl-2.0.txt](http://www.gnu.org/licenses/gpl-2.0.txt)

A simple and professional plugin to put your site into maintenance mode with one click. Perfect for site updates, construction, or any situation where you need to hide the site from visitors.

---

## 🚀 Features

- **Admin Bar button** to enable/disable maintenance mode with a single click
- **Distinctive colors** on the button: red when active, green when inactive
- **Template preview** directly on the site, even for administrators (using `?smm_preview=1` parameter)
- **Admin notice** displayed when maintenance mode is active
- **Full shortcode support** – any shortcode from other plugins (Contact Form 7, WooCommerce, etc.) works in the template
- **Automatic inheritance of active theme styles** – the template uses `wp_head()` and `wp_footer()`, inheriting all CSS and JavaScript from the theme
- **Customizable template** – can be overridden in the active theme (without fear of losing changes on plugin updates)
- **ACF Pro integration** – create an options page and customize titles, messages, contact info, etc.
- **Organized code following WordPress Plugin Boilerplate** – easy to extend and maintain
- **Modern compilation with Laravel Mix + Tailwind CSS + Sass** (optional, but included)

---

## 📦 Installation

1. Download the plugin and place the `simple-maintenance-mode` folder in `/wp-content/plugins/`
2. Go to the WordPress admin panel under **Plugins** and activate **Simple Maintenance Mode**
3. A new item will appear in the Admin Bar to enable/disable maintenance mode

---

## 🎨 Customizing the Template

### Override in Active Theme (Recommended)

If you want to customize the maintenance page HTML without losing changes on plugin updates:

1. Create the folder `/wp-content/themes/your-active-theme/simple-maintenance-mode/`
2. Copy the file `public/partials/smm-public-display.php` from the plugin to that folder and optionally rename it to `maintenance.php`
3. Edit the copied file as desired. You can use `get_header()`, `get_footer()`, `wp_head()`, `wp_footer()`, shortcodes, and all the power of WordPress

The plugin will prioritize this file over its default template.

### Plugin Default Template

If no override exists in the theme, the plugin uses the template located at:  
`public/partials/smm-public-display.php`

You can edit this file directly, but changes will be lost on the next plugin update.

---

## 🔌 ACF Pro Integration

To make the template even more flexible, the plugin supports custom fields via **ACF Pro**. Simply create an options page with fields whose names begin with `smm_`.

### Recommended Steps:

1. Install and activate **ACF Pro**
2. Create an options page (e.g., "Maintenance") – you can use the code below in your theme or helper plugin
3. Add fields with the following names (examples):
   - `smm_title` – main title
   - `smm_description` – description/message
   - `smm_address` – address
   - `smm_email` – contact email
   - `smm_phone` – phone number
   - (any other field you want, as long as the prefix is `smm_`)

### Example Code to Create Options Page (Optional):

```php
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title' => 'Maintenance Mode',
        'menu_title' => 'Maintenance',
        'menu_slug'  => 'smm-options',
        'capability' => 'manage_options',
        'redirect'   => false
    ));
}
```

Then, in the template, values are automatically retrieved by the `smm_get_content()` function (globally available). Example:

```php
<h1><?php echo smm_get_content( 'title', 'Under Maintenance' ); ?></h1>
<p><?php echo smm_get_content( 'description', 'We will be back soon.' ); ?></p>
```

If the ACF field doesn't exist, the function returns the default value (second parameter). You can also use filters to customize via code.

---

## 💬 Using Shortcodes in the Template

The template supports any shortcode registered in WordPress. Simply insert it directly in the PHP file:

```php
<?php echo do_shortcode( '[contact-form-7 id="123" title="Contact Form"]' ); ?>
```

Since the template includes `wp_head()` and `wp_footer()`, all styles and scripts required for the shortcode will be loaded automatically.

---

## 📁 Plugin Structure (For Developers)

The plugin follows the WordPress Plugin Boilerplate model generated at [https://wppb.me](https://wppb.me), with clear separation of responsibilities:

```
simple-maintenance-mode/
├── admin/                         # Admin area logic
│   ├── css/                       # Admin-specific styles
│   ├── js/                        # Admin-specific scripts
│   └── class-smm-admin.php        # Admin bar, notices, toggle
├── includes/                      # Plugin core
│   ├── class-smm.php              # Main class (orchestrator)
│   ├── class-smm-common.php       # Common functionality (shared CSS/JS)
│   ├── class-smm-utils.php        # Utility functions (e.g., smm_get_content)
│   └── ... (loader, i18n, etc.)
├── public/                        # Public-facing logic
│   ├── css/                       # Public-specific styles
│   ├── js/                        # Public-specific scripts
│   ├── partials/                  # Templates
│   │   └── smm-public-display.php # Maintenance template
│   └── class-smm-public.php       # template_redirect and enqueue
├── assets/                        # Compiled resources (common CSS/JS)
│   └── css/
│       └── smm-common.css         # Plugin bootstrap styles
├── languages/                     # Translation files
├── LICENSE.txt                    # License file
├── smm.php                        # Plugin bootstrap
├── package.json                   # NPM dependencies
├── webpack.mix.js                 # Laravel Mix configuration
├── tailwind.config.js             # Tailwind CSS configuration
├── uninstall.php                  # Uninstall cleanup
├── README.txt                     # WordPress.org readme
└── README.md                      # This file
```

### Organizational Highlights:

- **class-smm-common.php** – contains styles and scripts that should be loaded in both admin and public areas (e.g., global CSS). It's good practice to separate these features to keep code cleaner
- **class-smm-utils.php** – houses helper functions (like `smm_get_content`), avoiding pollution of the main class
- **class-smm.php** – orchestrates everything, loading dependencies and defining main hooks

---

## ⚙️ Requirements for Advanced Customization (ACF)

| Item | Detail |
|------|--------|
| **ACF Pro Plugin** | Only required if you want to use the options page to manage content |
| **Options Page** | Must be created with slug `smm-options` (or another, but the `smm_get_content` function will look for fields with `smm_` prefix in the 'option' option) |
| **Fields** | All fields must have names starting with `smm_` (e.g., `smm_title`, `smm_description`, `smm_address`, `smm_email`, `smm_phone`) |

If ACF is not active, the `smm_get_content` function falls back to filters, allowing customization via theme or plugin.

---

## 🛠️ Development with Laravel Mix / Tailwind / Sass

The plugin includes optional support for compiling assets with Laravel Mix, Tailwind CSS, and Sass. To start developing:

```bash
cd simple-maintenance-mode
npm install
npm run watch   # or npm run dev / npm run build
```

Source files are located in:

- `admin/sass/` – for admin Sass (compiles to `dist/admin/css/smm-admin.css`)
- `public/sass/` – for public Sass (compiles to `dist/public/css/smm-public.css`)

These assets are automatically enqueued in the appropriate hooks.

### NPM Scripts:

```bash
npm run dev       # Development build
npm run watch     # Watch files and rebuild on changes
npm run production # Production build (minified)
```

---

## 🔄 Migrating to Vite (Recommended)

**Note:** Laravel Mix is deprecated and has security vulnerabilities. We strongly recommend migrating to Vite for:

- ⚡ 10-100x faster builds
- 🔥 Hot Module Replacement (instant updates)
- 🔒 Zero security vulnerabilities
- 🚀 Modern tooling and active maintenance

See our [Vite Migration Guide](MIGRATION_GUIDE.md) for step-by-step instructions.

---

## 🌍 Internationalization

The plugin is translation-ready. To translate:

1. Use a plugin like Loco Translate or Poedit
2. Find the `.pot` file in the `languages/` folder
3. Create translations for your language
4. Save the `.po` and `.mo` files in the `languages/` folder

---

## 🐛 Troubleshooting

### Maintenance mode won't activate
- Check if you have the necessary permissions (administrator role)
- Verify the plugin is properly activated
- Clear your browser cache

### Template not showing correctly
- Ensure your theme properly supports `wp_head()` and `wp_footer()`
- Check for JavaScript errors in the browser console
- Try disabling other plugins to identify conflicts

### ACF fields not working
- Verify ACF Pro is installed and activated
- Check that field names start with `smm_`
- Confirm the options page exists with the correct slug

---

## 📝 License

GPLv2 or later. See the LICENSE file for more details.

---

## 🤝 Contributing

Report issues or suggest improvements on the plugin page (to be defined).

---

## 📚 Documentation

For more detailed documentation:

- [Vite Migration Guide](MIGRATION_GUIDE.md) - How to migrate from Laravel Mix to Vite
- [Quick Reference](QUICK_REFERENCE.md) - Common commands and workflows
- [ACF Integration Guide](#-acf-pro-integration) - Setting up custom fields

---

## 🙏 Credits

Made with ❤️ by [dig.id](https://dig.id)

---

## 📋 Changelog

### 1.0.0
- Initial release
- Admin bar toggle button
- Template override support
- ACF Pro integration
- Shortcode support
- Theme style inheritance
- Laravel Mix compilation setup

---

## 🔮 Roadmap

- [ ] Visual template editor
- [ ] Multiple template presets
- [ ] Countdown timer widget
- [ ] Social media links
- [ ] Email subscription integration
- [ ] Coming soon mode (separate from maintenance)
- [ ] Role-based access exceptions
- [ ] Scheduled maintenance periods

---

## ❓ FAQ

**Q: Will search engines index the maintenance page?**  
A: No, the plugin sends proper HTTP 503 status codes, telling search engines the site is temporarily unavailable.

**Q: Can logged-in users bypass maintenance mode?**  
A: Yes, administrators and users with the `manage_options` capability can view the site normally.

**Q: Can I preview the maintenance page before activating it?**  
A: Yes! Add `?smm_preview=1` to any URL while logged in as administrator.

**Q: Does this work with page builders?**  
A: Yes, since the template supports shortcodes, you can use page builder shortcodes in the template.

**Q: Will this slow down my site?**  
A: No, the plugin is lightweight and only loads when maintenance mode is active.

**Q: Can I use custom fonts or icons?**  
A: Yes, since the template inherits your theme's styles, all your fonts and icons will work automatically.

---

## 💡 Tips & Tricks

### Using Custom Logos

```php
<img src="<?php echo get_template_directory_uri(); ?>/images/logo.png" alt="Logo">
```

### Adding Countdown Timer

```php
<?php echo do_shortcode('[countdown date="2024-12-31 23:59:59"]'); ?>
```

### Custom Background Color

Override the template and add your own styles or use ACF to store a color picker value.

### Social Media Links

Use ACF fields:
- `smm_facebook`
- `smm_twitter`
- `smm_instagram`

Then in your template:
```php
<?php if ( smm_get_content('facebook') ) : ?>
    <a href="<?php echo smm_get_content('facebook'); ?>">Facebook</a>
<?php endif; ?>
```

---

## 🎯 Use Cases

- **Site Maintenance** - Planned updates or server maintenance
- **Coming Soon** - Launch a teaser page before site launch
- **Site Migration** - Hide site during migration to new hosting
- **Development** - Work on changes without affecting live site
- **Emergency Downtime** - Quick way to take site offline during issues
- **Seasonal Closures** - For businesses with seasonal operations

---

## 🔐 Security

The plugin follows WordPress security best practices:

- Proper capability checks (`manage_options`)
- Nonce verification for state changes
- Data sanitization and escaping
- No user input stored without validation
- Regular security updates

---

## 📞 Support

For support, please:

1. Check the [FAQ section](#-faq)
2. Review the [troubleshooting guide](#-troubleshooting)
3. Contact us at [support@dig.id](mailto:support@dig.id)

---

**Enjoy using Simple Maintenance Mode! ⚡**