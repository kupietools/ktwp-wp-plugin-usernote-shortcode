# KTWP Usernote Shortcode

A simple WordPress plugin that displays enclosed shortcode content only to specific logged-in users or roles.

## Description

KTWP Usernote Shortcode lets you wrap content in a shortcode and show it only to:

- specific usernames
- specific user IDs
- specific role slugs
- all logged-in users

If no shortcode attributes are provided, the plugin can also use defaults set directly in the plugin file.

Only logged-in users can ever see the content. Logged-out visitors see nothing.

## Features

- Show content to specific users by `user_login`
- Show content to specific users by numeric user ID
- Show content to specific roles by role slug
- Show content to all logged-in users using `all`
- Optional plugin-wide defaults set in the PHP file
- Supports nested shortcodes inside the displayed content

## Requirements

- **WordPress:** 2.5+
- **PHP:** 5.3+

## Installation

1. Upload the plugin file to your WordPress plugins directory.
2. Activate the plugin in **Plugins**.
3. Optionally edit the default values in the plugin file:
   - `users`
   - `roles`

## Shortcode

The plugin registers this shortcode:

- `[usernote]...[/usernote]`

## Usage

### Show content to specific users

```text
[usernote users="alice,bob,25"]This is only for Alice, Bob, or user ID 25.[/usernote]
```

### Show content to specific roles

```text
[usernote roles="editor,administrator"]This is only for editors or administrators.[/usernote]
```

### Show content to all logged-in users

```text
[usernote users="all"]This is visible to any logged-in user.[/usernote]
```

or:

```text
[usernote roles="all"]This is also visible to any logged-in user.[/usernote]
```

### Show content when either users or roles match

```text
[usernote users="alice,25" roles="editor"]This is shown if the current user matches any listed user or role.[/usernote]
```

### Use plugin defaults

```text
[usernote]This uses the default users/roles defined in the plugin file.[/usernote]
```

## How Matching Works

### Users
The `users` attribute accepts:

- usernames (`user_login`)
- numeric user IDs
- `all`

Examples:

```text
[usernote users="alice"]
[usernote users="12"]
[usernote users="alice,bob,12"]
[usernote users="all"]
```

**Note:** `users` does **not** accept display names or email addresses.

### Roles
The `roles` attribute accepts:

- role slugs
- `all`

Examples:

```text
[usernote roles="editor"]
[usernote roles="editor,administrator"]
[usernote roles="all"]
```

## Behavior Rules

- If `users` and/or `roles` are provided, content is shown if **any** match.
- If neither attribute is provided, the plugin uses the defaults set in the plugin file.
- If an attribute is present, even if empty, it overrides the plugin defaults.
- If the effective `users` and `roles` lists are both empty, the content is shown to nobody.
- If the visitor is not logged in, the content is never shown.

## Default Settings

Defaults are defined in the plugin file here:

```php
function show_to_get_defaults() {
    return array(
        'users'  => '',
        'roles'  => '',
    );
}
```

You can edit these values directly. For example:

```php
function show_to_get_defaults() {
    return array(
        'users'  => 'alice,bob',
        'roles'  => 'administrator',
    );
}
```

Then this shortcode:

```text
[usernote]Hidden content[/usernote]
```

will use those defaults automatically.

## Output Markup

When a match is found, the plugin outputs:

```html
<div class="ktwp_usernote">
  <span class="ktwp_usernote_title">Usernote:</span>
  ...
</div>
```

This allows you to style the note with your theme or custom CSS.

## Example CSS

```css
.ktwp_usernote {
    padding: 12px 16px;
    margin: 1em 0;
    background: #fff8cc;
    border-left: 4px solid #d4b106;
}

.ktwp_usernote_title {
    font-weight: bold;
    margin-right: 6px;
}
```

## Notes

- The plugin function is named `show_to_shortcode`, but the registered shortcode tag is `[usernote]`.
- Shortcode content is passed through `do_shortcode()`, so nested shortcodes can be used.
- Matching usernames is case-insensitive.
- Matching roles is case-insensitive.
- Matching user IDs is numeric.

## Limitations

- No admin settings page is included. This will be included in a future finalized version.
- Defaults must be edited directly in the plugin file. This will be changed in a future finalized version
- Content is only shown to logged-in users.
- There is no option to show alternate content to users who do not match.

## Changelog

### 1.0.0 alpha
- Initial release

## License
GNU v3 or higher. See accompanying LICENSE file. 
