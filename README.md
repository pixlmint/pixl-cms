# pixl-cms
A lightweight CMS API powered by Markdown and Nachos 

## Installation
### Option 1 (recommended): Composer
The best way to add this to your project is by installing it through composer: `composer require pixlmint/pixl-cms`

### Option 2
It's also possible to manually include the cms' source code in your project
For that to work you'll still need to have composer working on your site. In your `composer.json`, under `autoload > psr-4` add `"PixlMint\\CMS\\": "path/to/cms/src"`
-The important part being the part before the colon, as otherwise the CMS code won't work

Once you're ready to get to work you'll have to execute `composer dump-autoload`

### Finally (either way)
Then create a `index.php` file at the root of your website:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use PixlMint\CMS\CmsCore;
</IfModule>

CmsCore::init();
```

And I recommend adding the following `.haccess` file in the root of your project:
```apacheconf
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteRule ^(src|.vscode|content|node_modules|CHANGELOG\.md|.secret|users.json|composer\.(json|lock|phar))(/|$) index.php
    # Enable URL rewriting
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule .? index.php [L]

# Prevent file browsing
Options -Indexes -MultiViews
```

And that's it! You now have a powerful CMS API at your fingertips.

## API Documentation
View the full API documentation here: [PixlCms Documentation](https://documenter.getpostman.com/view/17116882/2s93sf2B7k)

## Plugins
Plugins are a great way to extend the functionality of the base CMS to your specific needs. 

I am actively developing 2 plugins right now, one for journaling, and one for running a wiki

### First Party Plugins
#### pixl-wiki
[PixlMint Wiki GitHub Page](https://github.com/pixlmint/pixlcms-wiki-plugin)

#### pixl-journal
[PixlMint Journal GitHub Page](https://github.com/pixlmint/pixlcms-journal-plugin)

### Configuration
*name*

The Name of the plugin. If `install_method` is set to `sourcecode`, this needs to be the folder name within the `plugins` directory.

*install_method

A string which defines the method in which the plugin was installed. The available options are:
- `composer` - If the plugin is installed as a composer plugin.
- `sourcecode` - The plugin is located in the `/plugins` directory.

*enabled*

Whether the plugin is enabled. Defaults to `true`. If it's set to `false`, the cms won't load anything from the plugin.

*config*

The Plugin configuration - the best practice is to just `require_once` the plugins config.php file.

### Plugin Development
The best way to start developing plugins is to take a look at one of my first-party plugins.

## Configuration
As PixlCMS is built with the [Nacho Framework](https://github.com/pixlmint/Nacho) so for full configuration info look at the Nacho Wiki

### Setting a custom Frontend Controller
The Frontend Controller is the Controller that handles all routes that don't point to an actual file (like an image/ video), and that don't start with `/api`. 

**Type:** `string`

**Default:** `None`

**Config:** `base > frontendController`

### Enable debug mode
Debug mode makes it easier to develop plugins by printing out full PHP errors

**Type:** `boolean`

**Default:** `false`

**config:** `base > debugEnabled`

