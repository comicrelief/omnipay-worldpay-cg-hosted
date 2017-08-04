# Omnipay: Worldpay Hosted Corporate Gateway (XML)

## NOT PRODUCTION READY!

**WORK IN PROGRESS August 2017**

This fork from teaandcode/omnipay-worldpay-xml is in active dev, and is not yet tested.

**WorldPay Hosted driver for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/omnipay/omnipay) is a framework agnostic,
multi-gateway payment processing library for PHP 5.6+. This package implements WorldPay XML Corporate Gateway Hosted support for Omnipay.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply
add it to your `composer.json` file:

```json
{
    "require": {
        "comicrelief/omnipay-worldpay-cg-hosted": "~1.0"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## Basic Usage

The following gateways are provided by this package:

* WorldPay Corporate Gateway Hosted

For general usage instructions, please see the main
[Omnipay](https://github.com/omnipay/omnipay) repository.

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](https://stackoverflow.com/). Be sure to add the
[omnipay tag](https://stackoverflow.com/questions/tagged/omnipay) so it can be
easily found.

If you want to keep up to date with release anouncements, discuss ideas for the
project, or ask more detailed questions, there is also a
[mailing list](https://groups.google.com/forum/#!forum/omnipay) which you can
subscribe to.

If you believe you have found a bug, please report it using the
[issue tracker](https://github.com/comicrelief/omnipay-worldpay-cg-hosted/issues), or
better yet, fork the library and submit a pull request.
