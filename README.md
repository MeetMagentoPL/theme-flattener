
## Features

* Flattening of Theme web files into one work directory with symlinks
* Flattening of "base" theme from app/code/*/*/view/web into work directory using files, so they can be copied over into theme work directories (see below)
* Adding files to theme work directories creates those files in the linked theme, based on a file naming convention, creating missing directories along the way.

Theme files that are not part of a module are symlinked into the work directory as is, for example
app/design/frontend/Magento/luma/web/css/source/_buttons.less
is symlinked to 
<workdir>/css/_buttons.less


## Commands:

bin/magento dev:theme:flatten Magento_blank

This would flatten the theme Magento_blank into a directory xx/magento-blank-flat


bin/magento dev:theme:flatten Magento_luma --dest luma-work-dir

This would flatten the theme Magento_luma into a directory luma-work-dir


bin/magento dev:theme:flatten --area admin Magento_backend

This would flatten the theme Magento_backend from the admin area into a directory xx/magento-backend-flat


bin/magento dev:theme:flatten --area admin Magento_backend --dest admin-theme

This would flatten the theme Magento_backend from the admin area into a directory admin-theme


bin/magento dev:theme:flatten modules

This would flatten the view files from the modules under app/code into a directory xx/modules-flat


bin/magento dev:theme:flatten modules --dest base

This would flatten the view files from the modules under app/code into a directory base (using files)


bin/magento dev:theme:rebuild-flattened-theme xx/magento-blank-flat

That would look at the file xx/magento-blank-flat/.flatten and rerun the command listed in there


bin/magento dev:theme:rebuild-flattened-themes 

That would look at the files xx/*/.flatten and rerun all the commands

------



The compilation step is not involved or changed.

Theme flattening example:

## Example 1:

The file
app/design/Magento/luma/Magento_Checkout/web/css/source/_module.less
is symlinked to
<workdir>/css/Magento_Checkout_module.less

## Example 2:

The file 

app/design/frontend/Magento/luma/Magento_Checkout/web/css/source/module/checkout/_shipping.less
is symlinked to
<workdir>/css/Magento_Checkout_module_checkout_shipping.less

**Note:**
In case of doubt assume a less or sass file is meant to be a partial (with a leading _).


## Creation of new files in flattened themes:

Creating a file in the work dir will trigger the following process:

1. move the new file to the matching directory in the linked theme, creating missing directories if necessary
2. create a symlink from the moved file in the theme to the work directory

## Creation of new files in flattened base theme:

Nothing happens

## Deletion of files in flattened themes:

The file will be re-linked from the source theme

## Deletion of files in flattened base theme:

Nothing happens

## Deleting of file in theme:

The symlinked file in the flattened theme is deleted

## Deleting of file in base theme:

Nothing happens


