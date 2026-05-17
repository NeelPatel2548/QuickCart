<?php
/**
 * QuickCart - Product Image Helper
 * 
 * Provides a reusable function to safely resolve product image paths,
 * with automatic fallback to a default placeholder when files are missing.
 * 
 * Usage:
 *   require_once 'includes/image_helper.php';
 *   $img_src = get_product_image($product['image']);
 *   // => "img/mouse_1.png" or "img/prod_default.jpg"
 * 
 * @package QuickCart
 * @since   1.0
 */

// Default fallback image filename (must exist in /img/ folder)
define('DEFAULT_PRODUCT_IMAGE', 'prod_default.jpg');

/**
 * Resolves a product image filename to a valid, web-ready path.
 *
 * Checks if the given image file physically exists inside the /img/ directory.
 * If found, returns the relative path (e.g., "img/mouse_1.png").
 * If missing or empty, returns the path to the default placeholder image.
 *
 * @param  string|null $filename  The image filename stored in the database (e.g., "mouse_2.png")
 * @param  string      $img_dir   The image directory relative to project root (default: "img")
 * @return string                 The safe, relative image path for use in <img src="...">
 */
function get_product_image(?string $filename, string $img_dir = 'img'): string
{
    // Determine the project root (one level up from /includes/)
    $base_path = dirname(__DIR__);

    // Guard: if filename is empty/null, return default immediately
    if (empty($filename)) {
        return $img_dir . '/' . DEFAULT_PRODUCT_IMAGE;
    }

    // Sanitize: strip any directory traversal attempts
    $safe_filename = basename($filename);

    // Build the absolute filesystem path and check existence
    $full_path = $base_path . DIRECTORY_SEPARATOR . $img_dir . DIRECTORY_SEPARATOR . $safe_filename;

    if (file_exists($full_path) && is_file($full_path)) {
        return $img_dir . '/' . $safe_filename;
    }

    // File not found — return default placeholder
    return $img_dir . '/' . DEFAULT_PRODUCT_IMAGE;
}

/**
 * Returns a complete HTML <img> tag with fallback support.
 *
 * @param  string|null $filename   The image filename from the database
 * @param  string      $alt_text   Alt text for accessibility
 * @param  string      $css_class  Optional CSS class(es)
 * @param  string      $style      Optional inline styles
 * @return string                  Complete HTML <img> tag
 */
function product_image_tag(?string $filename, string $alt_text = '', string $css_class = '', string $style = ''): string
{
    $src = get_product_image($filename);
    $alt = htmlspecialchars($alt_text, ENT_QUOTES, 'UTF-8');
    $class_attr = $css_class ? ' class="' . htmlspecialchars($css_class) . '"' : '';
    $style_attr = $style ? ' style="' . htmlspecialchars($style) . '"' : '';

    return sprintf(
        '<img src="%s" alt="%s"%s%s loading="lazy">',
        $src,
        $alt,
        $class_attr,
        $style_attr
    );
}
