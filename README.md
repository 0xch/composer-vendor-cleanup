# Composer vendor cleanup
This is a simple script for the Composer to remove unnecessary files (documentation/examples/tests etc.) from included vendor packages.
Therefore it's a script it can be easily used as part of a deploy script.

It uses predefined whitelist (`src/CleanupRules.php`) to remove files. So the risk of not working on included packages is reduced.
Script is based on rules from barryvdh's package https://github.com/barryvdh/composer-cleanup-plugin .

I just needed a script to execute manually instead of a plugin which is executing every time.

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

### Usage
Once installed just run command (defined in **Installation** step):
```
composer cleanVendor
```
It will go via all installed packages which are on the whitelist and remove unnecessary files.
If you are curious how much space is saved in your case - you can check it by executing command `du -hs vendor/` before and after this script.
