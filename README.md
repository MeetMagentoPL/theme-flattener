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
# --area ::
# --dest ::

dev:theme:rebuild-flattened-theme
dev:theme:rebuild-flattened-themes
```

### Examples

Flatten the theme `Magento_blank` into a directory `xx/magento-blank-flat`:

```
bin/magento dev:theme:flatten Magento_blank
```

Flatten the theme `Magento_luma` into a directory `luma-work-dir`:

```
bin/magento dev:theme:flatten Magento_luma --dest luma-work-dir
```

Flatten the theme `Magento_backend` from the admin area into a directory `xx/magento-backend-flat`:

```
bin/magento dev:theme:flatten --area admin Magento_backend
```

Flatten the theme `Magento_backend` from the admin area into a directory `admin-theme`:

```
bin/magento dev:theme:flatten --area admin Magento_backend --dest admin-theme
```

Flatten the view files from the modules under `app/code` into a directory `xx/modules-flat`:

```
bin/magento dev:theme:flatten modules
```

Flatten the view files from the modules under `app/code` into a directory `base` (using files):

```
bin/magento dev:theme:flatten modules --dest base
```

Look at the file `xx/magento-blank-flat/.flatten` and rerun the command listed in there:

```
bin/magento dev:theme:rebuild-flattened-theme xx/magento-blank-flat
```

Look at the files `xx/*/.flatten` and rerun all the commands:

```
bin/magento dev:theme:rebuild-flattened-themes
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

Creating a file in the work dir will trigger the following process:

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
