<?php
/**
 * QuickCart - CSRF Protection Helper
 * 
 * Generates and validates CSRF tokens to prevent cross-site request forgery.
 * Tokens are stored in the user's session and must be included in all POST forms.
 *
 * Usage in forms:
 *   <?= csrf_field() ?>
 *
 * Usage in POST handlers:
 *   if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
 *       die('Invalid request.');
 *   }
 *
 * @package QuickCart
 * @since   1.1
 */

/**
 * Generates a new CSRF token and stores it in the session.
 * Returns the existing token if one is already present for this session.
 *
 * @return string The CSRF token (64-character hex string)
 */
function generate_csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validates a submitted CSRF token against the session token.
 * Uses hash_equals() for timing-safe comparison.
 *
 * @param  string $token The token submitted via form POST
 * @return bool          True if valid, false otherwise
 */
function validate_csrf_token(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Outputs a hidden HTML input field with the CSRF token.
 * Drop this inside any <form> to add CSRF protection.
 *
 * @return string HTML hidden input element
 */
function csrf_field(): string
{
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Regenerates the CSRF token. Call after a successful
 * state-changing operation to prevent token reuse.
 *
 * @return string The new CSRF token
 */
function regenerate_csrf_token(): string
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
