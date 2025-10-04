# Velocity - High-Performance WordPress Theme

A blazing-fast, modern WordPress theme designed for digital agencies and creative studios. Optimized for performance with Elementor and WPBakery compatibility.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0-green.svg)

## 🚀 Features

### Performance Optimized
- ⚡ Lightning-fast load times
- 🎯 Minimal CSS/JS (no jQuery dependency)
- 📱 Native lazy loading for images
- 🔄 Intersection Observer for scroll animations
- 🚫 WordPress bloat removed (emoji scripts, embeds, etc.)
- 💾 Efficient caching-ready structure

### Page Builder Compatible
- ✅ Elementor support with full-width templates
- ✅ WPBakery (Visual Composer) support
- 🎨 Clean content area for seamless page builder integration
- 🔧 No conflicting CSS or JavaScript

### Modern Design
- 🎨 Beautiful, professional design system
- 🌈 CSS Custom Properties for easy customization
- 📐 CSS Grid and Flexbox layouts
- 💫 Smooth animations and transitions
- 🎯 Mobile-first responsive design

### Developer Friendly
- 📝 Clean, well-documented code
- 🔌 WordPress coding standards compliant
- 🎣 Proper hook implementation
- 🛠️ Easy to customize and extend
- 📦 Modular structure

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Modern web browser with CSS Grid support

## 📥 Installation

### Method 1: WordPress Admin (Recommended)
1. Download the `velocity.zip` file
2. Log in to your WordPress admin panel
3. Navigate to **Appearance → Themes → Add New**
4. Click **Upload Theme**
5. Choose the `velocity.zip` file
6. Click **Install Now**
7. Activate the theme

### Method 2: FTP Upload
1. Extract the `velocity.zip` file
2. Upload the `velocity` folder to `/wp-content/themes/`
3. Log in to WordPress admin panel
4. Navigate to **Appearance → Themes**
5. Activate Velocity theme

### Method 3: Development Setup
```bash
# Clone or download to themes directory
cd wp-content/themes/
# If you have the theme files locally
cp -r /path/to/velocity ./
# Set proper permissions
chmod 755 velocity
```

## 🎨 Getting Started

### 1. Basic Setup

After activating the theme:

1. **Set up your menus:**
   - Go to **Appearance → Menus**
   - Create a new menu and add your pages
   - Assign to "Primary Menu" location

2. **Add a custom logo:**
   - Go to **Appearance → Customize → Site Identity**
   - Upload your logo

3. **Configure homepage:**
   - Create a new page and select "Home Page" template
   - Go to **Settings → Reading**
   - Set "Your homepage displays" to "A static page"
   - Select your home page

### 2. Page Templates

Velocity includes 5 custom page templates:

#### Home Page Template
Full-featured homepage with:
- Hero section with CTA buttons
- Services showcase
- Portfolio highlights
- Statistics section
- Call-to-action

#### About Page Template
- Company story section
- Core values cards
- Team member showcase
- Process timeline
- CTA section

#### Services Page Template
- Services overview with images
- Service packages (Starter, Professional, Enterprise)
- Additional services grid
- CTA section

#### Portfolio Page Template
- Project showcase grid
- Featured case study
- Client testimonials
- Results/statistics section
- CTA

#### Contact Page Template
- Contact information with icons
- Contact form
- Business hours
- Social media links
- Map section
- FAQ section

### 3. Using Page Templates

To use a template:
1. Create a new page or edit an existing one
2. In the **Page Attributes** panel (right sidebar)
3. Select the desired template from **Template** dropdown
4. Publish the page

### 4. Page Builder Integration

#### Using with Elementor:
1. Install and activate Elementor plugin
2. Create a new page or edit existing one
3. Click **Edit with Elementor**
4. Build your page with Elementor widgets
5. The theme provides a clean canvas for your designs

#### Using with WPBakery:
1. Install and activate WPBakery Page Builder
2. Create a new page or edit existing one
3. Click **WPBakery Page Builder**
4. Build your page with WPBakery elements
5. Frontend editing is supported

## 🎛️ Customization

### Color Scheme

Edit colors in `style.css` under CSS Custom Properties:

```css
:root {
  --color-primary: #6C5CE7;    /* Primary brand color */
  --color-secondary: #00B894;   /* Secondary color */
  --color-dark: #2D3436;        /* Dark text */
  --color-light: #F8F9FA;       /* Light background */
  --color-accent: #FDCB6E;      /* Accent/CTA color */
}
```

### Typography

Modify font families:

```css
:root {
  --font-primary: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  --font-heading: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
```

### Spacing

Adjust spacing scale:

```css
:root {
  --spacing-xs: 0.5rem;
  --spacing-sm: 1rem;
  --spacing-md: 2rem;
  --spacing-lg: 4rem;
  --spacing-xl: 6rem;
}
```

### Layout Width

Change container width in `style.css`:

```css
:root {
  --container-width: 1200px; /* Adjust as needed */
}
```

## 🔧 Advanced Customization

### Adding Widget Areas

Edit `functions.php` to add custom widget areas:

```php
register_sidebar( array(
    'name'          => esc_html__( 'Custom Widget Area', 'velocity' ),
    'id'            => 'custom-widget',
    'description'   => esc_html__( 'Add widgets here.', 'velocity' ),
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget'  => '</div>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
) );
```

### Custom Post Types

Add to `functions.php`:

```php
function velocity_register_custom_post_type() {
    register_post_type( 'portfolio', array(
        'labels' => array(
            'name' => 'Portfolio',
            'singular_name' => 'Portfolio Item',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array( 'title', 'editor', 'thumbnail' ),
    ) );
}
add_action( 'init', 'velocity_register_custom_post_type' );
```

### Child Theme Creation

Create a child theme for safe customizations:

1. Create folder: `wp-content/themes/velocity-child/`
2. Create `style.css`:

```css
/*
Theme Name: Velocity Child
Template: velocity
*/
```

3. Create `functions.php`:

```php
<?php
function velocity_child_enqueue_styles() {
    wp_enqueue_style( 'velocity-parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'velocity_child_enqueue_styles' );
```

## 📱 Placeholder Images

The theme uses free placeholder images from:
- **Unsplash Source API**: High-quality royalty-free images
- Format: `https://source.unsplash.com/{width}x{height}/?{keywords}`

### Replacing Placeholder Images

To replace with your own images:

1. **Method 1: Upload Featured Images**
   - Edit page/post
   - Set featured image
   - Theme will use it instead of placeholder

2. **Method 2: Edit Template Files**
   - Find placeholder image URLs in template files
   - Replace with your own image URLs or WordPress media

3. **Method 3: Use Page Builders**
   - When using Elementor/WPBakery
   - Replace all images through the builder interface

## 🚀 Performance Tips

### Recommended Plugins

1. **Caching**: WP Super Cache or W3 Total Cache
2. **Image Optimization**: Smush or ShortPixel
3. **CDN**: Cloudflare (free plan available)
4. **Security**: Wordfence Security
5. **SEO**: Yoast SEO or Rank Math

### Optimization Checklist

- ✅ Enable caching plugin
- ✅ Optimize images (compress before upload)
- ✅ Use a CDN for static assets
- ✅ Minimize plugins (quality over quantity)
- ✅ Keep WordPress, theme, and plugins updated
- ✅ Use PHP 8.0+ for better performance
- ✅ Enable Gzip compression
- ✅ Use lazy loading (theme has it built-in)

## 🛠️ Troubleshooting

### Issue: Menu Not Showing

**Solution:**
1. Go to Appearance → Menus
2. Create a menu
3. Assign to "Primary Menu" location

### Issue: Custom Logo Not Displaying

**Solution:**
1. Go to Appearance → Customize → Site Identity
2. Upload logo (recommended size: 400x100px)
3. Or theme will show site title

### Issue: Page Builder Not Working

**Solution:**
1. Ensure Elementor or WPBakery is installed and activated
2. Check if theme is active
3. Try deactivating other plugins temporarily
4. Clear cache

### Issue: Images Not Loading

**Solution:**
1. Check internet connection (placeholder images from Unsplash)
2. Replace with local images if needed
3. Check browser console for errors

### Issue: Styles Not Applying

**Solution:**
1. Clear browser cache (Ctrl+F5 / Cmd+Shift+R)
2. Clear WordPress cache
3. Regenerate CSS in Customizer
4. Check for plugin conflicts

## 📞 Support

### Theme Support

For theme-related questions:
- Check documentation thoroughly
- Review troubleshooting section
- Search WordPress support forums

### WordPress Resources

- [WordPress Codex](https://codex.wordpress.org/)
- [WordPress Support Forums](https://wordpress.org/support/)
- [Elementor Documentation](https://elementor.com/help/)
- [WPBakery Documentation](https://kb.wpbakery.com/)

## 📄 License

This theme is licensed under the GNU General Public License v2 or later.

```
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
```

## 🙏 Credits

### Third-Party Resources

- **Unsplash**: Placeholder images ([unsplash.com](https://unsplash.com))
- **WordPress**: CMS platform ([wordpress.org](https://wordpress.org))
- **Elementor**: Page builder compatibility ([elementor.com](https://elementor.com))
- **WPBakery**: Page builder compatibility ([wpbakery.com](https://wpbakery.com))

### Fonts

- System font stack for optimal performance
- No external font dependencies

## 🔄 Changelog

### Version 1.0.0
- Initial release
- 5 custom page templates
- Elementor compatibility
- WPBakery compatibility
- Performance optimizations
- Responsive design
- Accessibility features
- SEO-friendly structure

## 🗺️ Roadmap

Future updates may include:
- More page templates
- WooCommerce integration
- Additional customizer options
- RTL language support enhancements
- Gutenberg block patterns
- More color scheme presets

---

**Built with ❤️ for the WordPress community**

For the best experience, use this theme with a modern hosting provider and follow WordPress best practices.
