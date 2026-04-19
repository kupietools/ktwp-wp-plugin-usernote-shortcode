<?php
/*
 * Plugin Name:       KupieTools Usernote Shortcode
 * Plugin URI:        https://michaelkupietz.com/plugins/ktwp-usernote-shortcode/
 * Description:       Display inline WordPress content within posts or pages only to specific users or roles.
 * Version:           1.0.0 alpha
 * Author:            Michael Kupietz
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Michael Kupietz
 * Author URI:        https://michaelkupietz.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 * Update URI:        https://michaelkupietz.com/plugins/updates/ktwp-usernote-shortcode/
 * Text Domain:       ktwp-plugin
 * Domain Path:       /languages
 */

/* To-dos:
 * Add admin panel in Kupietools settings page for settings and get rid of hard-coded plugin defaults here.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin defaults.
 * 
 * Used if no parameters are specified, like [usernote]blahblahblah[/usernote]
 *
 * Edit these values directly in the plugin file as needed.
 * Future version will include a panel
 *
 *
 * Notes:
 * - users accepts login names (user_login) or numeric user IDs
 * - users does not accept display names or email addresses
 * - roles accepts role slugs
 * - "all" in either users or roles means all logged-in users
 */
function show_to_get_defaults() {
    return array(
        'users'  => '',
        'roles'  => '',
    );
}

/**
 * Parse comma-separated shortcode/default values into an array.
 *
 * @param string $value
 * @return array
 */
function show_to_parse_csv_attr($value) {
    if (!is_string($value) || trim($value) === '') {
        return array();
    }

    $parts = array_map('trim', explode(',', $value));
    $parts = array_filter($parts, function ($item) {
        return $item !== '';
    });

    return array_values($parts);
}

/**
 * Check whether the current user matches any provided user identifiers.
 *
 * Supported identifiers:
 * - user ID
 * - username (user_login, not display name)
 * - "all" for all logged-in users
 *
 * @param WP_User $current_user
 * @param array   $user_list
 * @return bool
 */
function show_to_user_matches_list($current_user, $user_list) {
    if (empty($user_list)) {
        return false;
    }

    foreach ($user_list as $item) {
        $item_lc = strtolower(trim($item));

        if ($item_lc === 'all') {
            return true;
        }

        if (is_numeric($item) && intval($item) === intval($current_user->ID)) {
            return true;
        }

        if (strcasecmp($item, $current_user->user_login) === 0) {
            return true;
        }
    }

    return false;
}

/**
 * Check whether the current user has any of the specified roles.
 *
 * Supported:
 * - role slugs
 * - "all" for all logged-in users
 *
 * @param WP_User $current_user
 * @param array   $role_list
 * @return bool
 */
function show_to_user_matches_roles($current_user, $role_list) {
    if (empty($role_list)) {
        return false;
    }

    $user_roles = (array) $current_user->roles;

    foreach ($role_list as $role) {
        $role_lc = strtolower(trim($role));

        if ($role_lc === 'all') {
            return true;
        }

        foreach ($user_roles as $user_role) {
            if (strtolower($user_role) === $role_lc) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Shortcode handler.
 *
 * Usage:
 * [show_to users="alice,bob,25" roles="editor,administrator"]Content[/show_to]
 * [show_to users="all"]Content[/show_to]
 * [show_to roles="all"]Content[/show_to]
 * [show_to]Content[/show_to]
 *
 * Behavior:
 * - If users and/or roles are provided, content is shown if ANY match.
 * - If neither users nor roles attributes are present, plugin defaults are used.
 * - If users and/or roles attributes are present, even if empty, they override plugin defaults.
 * - If the effective users/roles lists are both empty, content is shown to nobody.
 *
 * Notes:
 * - users accepts login names (user_login) or numeric user IDs
 * - roles accepts role slugs
 * - "all" in users or roles means all logged-in users
 *
 * @param array       $atts
 * @param string|null $content
 * @return string
 */
function show_to_shortcode($atts, $content = null) {
    if ($content === null) {
        return '';
    }

    $raw_atts = is_array($atts) ? $atts : array();

    $atts = shortcode_atts(array(
        'users'  => '',
        'roles'  => '',
    ), $atts, 'show_to');

    if (!is_user_logged_in()) {
        return '';
    }

    $current_user = wp_get_current_user();
    if (!$current_user || empty($current_user->ID)) {
        return '';
    }

    $users = show_to_parse_csv_attr($atts['users']);
    $roles = show_to_parse_csv_attr($atts['roles']);

    $users_attr_present = array_key_exists('users', $raw_atts);
    $roles_attr_present = array_key_exists('roles', $raw_atts);

    // Only use plugin defaults if neither attribute was provided at all.
    if (!$users_attr_present && !$roles_attr_present) {
        $defaults = show_to_get_defaults();

        $users = show_to_parse_csv_attr($defaults['users']);
        $roles = show_to_parse_csv_attr($defaults['roles']);
    }

    // If effective rules are empty, show to nobody.
    if (empty($users) && empty($roles)) {
        return '';
    }

    $user_match = show_to_user_matches_list($current_user, $users);
    $role_match = show_to_user_matches_roles($current_user, $roles);

    if ($user_match || $role_match) {
        return '<span class="ktwp_usernote"><span class="ktwp_usernote_title">Usernote:</span> '.do_shortcode($content).'</span>';
    }

    return '';
}

add_shortcode('usernote', 'show_to_shortcode');