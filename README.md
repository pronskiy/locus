# Locus

Installs a per-project `php` binary to the `./vendor/bin/` based on `composer.json` requirements.

## Setup and Installation

Make sure you have a PHP version constraint specified in your `composer.json`:

```json
"require": {
  "php": "^8.3"
},
```

```bash
composer require pronskiy/locus --dev 
```

You'll be asked to allow the composer plugin, reply `y`. Or if you are adding the dependency manually, add the following to your `composer.json`:
```json 
"config": {
  "allow-plugins": {
    "pronskiy/locus": true
  }
}
```

## Usage

Now you always have your per-project `php` binary to execute PHP scripts directly from the command line.

```bash
./vendor/bin/php --version
```

## Credits

This package entirely relies on https://github.com/static-php. 
