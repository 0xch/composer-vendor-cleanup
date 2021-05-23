[![Latest Version on Packagist](https://img.shields.io/packagist/v/0xch/composer-vendor-cleanup.svg?style=flat-square)](https://packagist.org/packages/0xch/composer-vendor-cleanup)
[![Total Downloads](https://img.shields.io/packagist/dt/0xch/composer-vendor-cleanup.svg?style=flat-square)](https://packagist.org/packages/0xch/composer-vendor-cleanup)
# Composer vendor cleanup
This is a simple script for the Composer to remove unnecessary files (documentation/examples/tests etc.) from included vendor packages.
Therefore it's a script it can be easily used as part of a deploy script.

In my projects it saves about 20-30% of vendor size.

It uses predefined whitelist (`rules.json`) to remove files. So the risk of not working on included packages is reduced.
Script is based on rules from barryvdh's package https://github.com/barryvdh/composer-cleanup-plugin .

Feel free to submit pull requests with new rules or features.

### Installation
Add to composer:
```
composer require 0xch/composer-vendor-cleanup
```

Then add to your `composer.json`:
```
"scripts": {
    "cleanVendor": [
        "Oxch\\Composer\\CleanupScript::cleanVendor"
    ]
}
```

(optional) Copy `rules.json` to custom dir and modify for your case and pass filename as argument to composer.

### Usage
Once installed just run command (defined in **Installation** step):
```
composer cleanVendor    #use default rules config file
composer cleanVendor customRules.json    #use custom rules config file    
```
It will go via all installed packages which are on the whitelist and remove unnecessary files.

### Look for big files
You can use this command to display possible unnecessary files which can be deleted by adding to your custom rules config file.
```
du -hd 5 vendor/ | sort -h | grep -Pi "/(tests?|examples?|samples?)$"
```
