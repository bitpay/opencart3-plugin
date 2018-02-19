# Notice

This is a Community-supported project.

If you are interested in becoming a maintainer of this project, please contact us at support@bitpay.com. Developers at BitPay will attempt to work along the new maintainers to ensure the project remains viable for the foreseeable future.

# BitPay for OpenCart

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/bitpay/opencart-plugin/master.svg?style=flat-square)](https://travis-ci.org/bitpay/opencart-plugin)

## Last OpenCart Version Tested: 3.0.2.0 (not compatible with v2 branch)

## Installation

Follow the instructions found in the [BitPay for OpenCart Guide](GUIDE.md)

## Server requirements
PHP > 5.5 or PHP > 7.0, with the following PHP plugins enabled:
* GMP or BCMATH
* OpenSSL
* JSON
* CURL


## Development Setup

``` bash
# Clone the repo
$ git clone https://github.com/bitpay/opencart3-plugin.git
$ cd ./opencart3-plugin

# Install dependencies via Composer
$ composer install

# Set Environment Variables (variables needed can be found in .env.sample)
$ cp .env.sample .env

# After modifying the Environment Variables for your environment setup OpenCart
$ ./bin/robo setup
```

## Development Workflow

``` bash
# Run PHP Server of OpenCart installation and redirect bash I/O
$ ./bin/robo server &

# Watch for source code changes and copy them to the OpenCart installation
$ ./bin/robo watch
```

## Testing

``` bash
$ ./bin/robo test
```

## Build

``` bash
$ ./bin/robo build

# Outputs:
# ./build/bitpay-opencart - the distribution files
# ./build/bitpay-opencart.ocmod.zip - the distribution archive
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Support

**BitPay Support:**

* Last OpenCart Version Tested: 3.2.0.2 (not compatible with v2 branch)
* [Support](https://support.bitpay.com/hc/en-us/articles/115003000543-How-do-I-accept-bitcoin-with-Opencart-)
  * BitPay merchant support documentation

**OpenCart Support:**

* [Homepage](http://www.opencart.com)
* [Forums](http://forum.opencart.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
