# Theme Flattener for Magento 2

The **theme flattener** adds Magento commands so frontend developers don't need to navigate Magento 2's deep directory tree to `view` files. It creates a working directory in the Magento root, then symlinks or copies specified themes and module files into subfolders. Developers can use this shallow tree for faster development and reference to the core.

## Contents

1. [Features](#features)
1. [Commands](#commands)
  - [dev:theme:flatten (theme files)](#devthemeflatten-theme-files)
  - [dev:theme:flatten (module files)](#devthemeflatten-module-files)
  - [dev:theme:rebuild-flattened-theme](#devthemerebuild-flattened-theme)
  - [dev:theme:rebuild-flattened-themes](#devthemerebuild-flattened-themes)
1. [Example Output](#example-output)
1. [Expected Behavior](#expected-behavior)
1. [Contributors](#contributors)

-----

## Features

### Completed

- [x] Flatten "custom" theme files into `<workdir>` using symlinks.

### Todo

- [ ] Flatten "base" theme files from core modules in `app/code/*/*/view/web` into `<workdir>` (by copying files). This allows copying actual files (in your IDE) to your custom theme instead of copying symlinks.
- [ ] Creating files in the `<workdir>` for custom themes will create those files in the reverse-symlinked location. It is based on a file naming convention and should create missing directories along the way.

**Note:** the `<workdir>` is hardcoded to `xx` and is created in the Magento root. All the flattened files will live here.

**Note:** CSS compilation is not involved or changed because files are only modified via symlinks.

-----

## Commands

```sh
# Flatten a theme
bin/magento dev:theme:flatten <package_theme>

# Flatten core modules
bin/magento dev:theme:flatten modules

# Rebuild a theme
bin/magento dev:theme:rebuild-flattened-theme <workdir>/<flattened-theme-dir>

# Rebuild all themes
bin/magento dev:theme:rebuild-flattened-theme <workdir>
```

### dev:theme:flatten &lt;package_theme&gt;

```sh
bin/magento dev:theme:flatten <package_theme> -a="..." -d="..."

# Options:
# -a (alias: --area) 'frontend' (default) or 'adminhtml'
# -d (alias: --dest) path to place files relative to Magento root. Default is <workdir>.
```

**Example: defaults**

Flatten the `Magento_blank` theme into `<workdir>/magento-blank-flat`:

```sh
bin/magento dev:theme:flatten Magento_blank
```

**Example: override destination**

Flatten the `Magento_luma` theme into `luma-work-dir`:

```sh
bin/magento dev:theme:flatten Magento_luma --d=luma-work-dir
```

**Example: override area**

Flatten the `Magento_backend` theme (from the `adminhtml` area) into `<workdir>/magento-backend-flat`:

```sh
bin/magento dev:theme:flatten Magento_backend -a=adminhtml
```

**Example: override destination and area**

Flatten the theme `Magento_backend` (from the `adminhtml` area) into a new working directory `admin-theme`:

```sh
bin/magento dev:theme:flatten Magento_backend -a=adminhtml -dest=admin-theme
```

### dev:theme:flatten modules

```sh
bin/magento dev:theme:flatten modules -d="..."

# Options:
# -d (alias: --dest) :: path to place files relative to Magento root. Default is <workdir>.
```

**Example: default**

Flatten every module's view files from `app/code` into `<workdir>/modules-flat`:

```sh
bin/magento dev:theme:flatten modules
```

**Example: override area**

Flatten every module's view files from `app/code` into a custom working directory named `xyz-working-dir`:

```sh
bin/magento dev:theme:flatten modules -d=xyz-working-dir
```

### dev:theme:rebuild-flattened-theme <workdir>/<flattened-theme-dir>

Look at the file `<workdir>/<flattened-theme-dir>/.flatten` and rerun the listed commands:

```sh
bin/magento dev:theme:rebuild-flattened-theme <workdir>/<flattened-theme-dir>

# Example
# bin/magento dev:theme:rebuild-flattened-theme <workdir>/magento-blank-flat
```

### dev:theme:rebuild-flattened-theme <workdir>

Look at the files `<workdir>/*/.flatten` and rerun all the commands:

```sh
bin/magento dev:theme:rebuild-flattened-themes <workdir>
```

-----

## Example Output

Example output from using flattening.

**Note:** assumes any Less/Sass file is a partial (with a leading `_`).

### Example 1

```
Given file: app/design/Magento/luma/Magento_Checkout/web/css/source/_module.less
Symlink to: <workdir>/css/Magento_Checkout_module.less
```

### Example 2

```
Given file: app/design/frontend/Magento/luma/Magento_Checkout/web/css/source/module/checkout/_shipping.less
Symlink to: <workdir>/css/Magento_Checkout_module_checkout_shipping.less
```

### Example 3

Theme files that are not part of a module are symlinked into the `<workdir>` as is, for example:

```
Given file: app/design/frontend/Magento/luma/web/css/source/_buttons.less
Symlink to: <workdir>/css/_buttons.less
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
