
# LM - File System

To store and delete files and also folder on cloud server likes Amazon S3 , Digital Ocean Space etc.

----------

# Getting started

- `php` : >=7.4

## Installation and Configuration

Command to install package.

    composer require logisticmall/filesystem

Register the FileSystemServiceProvider in bootstrap >> app.php.

    $app->register(\LogisticMall\FileSystem\FileSystemServiceProvider::class);

Add config/filesystems.php through on adding script in composer.json or vendor:publish command line.

- On adding script in composer.json

      "scripts": {
              "post-package-install": [
                   "cp -a vendor/logisticmall/filesystem/config/filesystems.php config/filesystems.php"
              ]
      }

- Or on using command line

      php artisan vendor:publish --provider="LogisticMall\FileSystem\FileSystemServiceProvider"

## Project Dependencies

- "league/flysystem-aws-s3-v3": "^1.0",
- "guzzlehttp/guzzle": "^7.4",
- "intervention/image": "^2.7"   

