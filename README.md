## Contents

1. [Features](#features)
1. [Commands](#commands)
1. [Examples](#examples)
1. [Expected Behavior](#expected-behavior)
1. [Contributors](#contributors)

-----

## Features

- Flatten "custom" theme files into `<workdir>` using symlinks.
- Flatten "base" theme files from core modules in `app/code/*/*/view/web` into `<workdir>` (by copying files). This allows copying actual files (in your IDE) to your custom theme instead of copying symlinks.
- Creating files in the `<workdir>` for custom themes will create those files in the reverse-symlinked location. It is based on a file naming convention and should create missing directories along the way.

**Note:** The CSS compilation step is not involved or changed.

-----

## Commands

```sh
bin/magento dev:theme:flatten --area --dest

# OPTIONS
# --area :: frontend' (default) or admin
# --dest :: where

dev:theme:rebuild-flattened-theme

dev:theme:rebuild-flattened-themes
```

### Examples

**Note:** the `<workdir>` is hardcoded to `xx` and is created in the Magento root.

#### Themes

Flatten the `Magento_blank` theme into `<workdir>/magento-blank-flat`:

```sh
bin/magento dev:theme:flatten Magento_blank
```

Flatten the `Magento_luma` theme into `luma-work-dir`:

```sh
bin/magento dev:theme:flatten Magento_luma --dest luma-work-dir
```

#### Areas

Flatten the `Magento_backend` theme (from the admin area) into `<workdir>/magento-backend-flat`:

```sh
bin/magento dev:theme:flatten --area admin Magento_backend
```

Flatten the theme `Magento_backend` (from the admin area) into a new working directory `admin-theme`:

```sh
bin/magento dev:theme:flatten --area admin Magento_backend --dest admin-theme
```

#### Module Files

Flatten the view files from the modules under `app/code` into `<workdir>/modules-flat`:

```sh
bin/magento dev:theme:flatten modules
```

Flatten the view files from the modules under `app/code` into a custom working directory named `base`:

```sh
bin/magento dev:theme:flatten modules --dest base
```

#### Rebuild

Look at the files `<workdir>/*/.flatten` and rerun all the commands:

```sh
bin/magento dev:theme:rebuild-flattened-themes
```

Look at the file `<workdir>/magento-blank-flat/.flatten` and rerun the command listed in there:

```sh
bin/magento dev:theme:rebuild-flattened-theme path/to/directory

# Example
# bin/magento dev:theme:rebuild-flattened-theme <workdir>/magento-blank-flat
```

-----

## Examples

**Note:** assume any Less/Sass file is meant to be a partial (with a leading `_`).

Theme flattening example:

### Example 1

The file:

```
app/design/Magento/luma/Magento_Checkout/web/css/source/_module.less
```

Is symlinked to:

```
<workdir>/css/Magento_Checkout_module.less
```

### Example 2

```
app/design/frontend/Magento/luma/Magento_Checkout/web/css/source/module/checkout/_shipping.less
```

Is symlinked to:

```
<workdir>/css/Magento_Checkout_module_checkout_shipping.less
```

### Example 3

Theme files that are not part of a module are symlinked into the `<workdir>` as is, for example:

```
app/design/frontend/Magento/luma/web/css/source/_buttons.less
```

Is symlinked to:

```
<workdir>/css/_buttons.less
```

-----

## Expected Behavior

### Creation of new files in flattened themes:

Creating a file in the `<workdir>` will trigger the following process:

1. Move the new file to the matching directory in the linked theme (creating missing directories if necessary).
2. Create a symlink from the moved file in the theme to the work directory.

### Creation of new files in flattened base theme:

Nothing happens.

### Deletion of files in flattened themes:

The file will be re-linked from the source theme.

### Deletion of files in flattened base theme:

Nothing happens.

### Deleting of file in theme:

The symlinked file in the flattened theme is deleted.

### Deleting of file in base theme:

Nothing happens.

-----

# Contributors

- [Anna Karon](https://github.com/anqaka)
- [Brendan Falkowski](https://github.com/brendanfalkowski)
- [Vinai Kopp](https://github.com/Vinai)
- [Wiktor Jarka](https://github.com/wjarka)
