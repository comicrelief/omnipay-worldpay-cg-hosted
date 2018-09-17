# Omnipay: Worldpay Hosted Corporate Gateway (XML)

[![Build Status](https://travis-ci.org/catharsisjelly/omnipay-worldpay-cg-hosted.png?branch=master)](https://travis-ci.org/catharsisjelly/omnipay-worldpay-cg-hosted)

**WorldPay Hosted driver for the Omnipay PHP payment processing library**

This was forked from [comicrelief/omnipay-worldpay-cg-hosted](https://github.com/comicrelief/omnipay-worldpay-cg-hosted)
which is now not a maintained library. [@catharsisjelly](https://github.com/catharsisjelly) did offer to take over the
library and continue development of this but so far has been unsuccessful. For more information on the
changes implemented please see the [CHANGELOG](./CHANGELOG.md)
 
[Omnipay](https://github.com/omnipay/omnipay) is a framework agnostic,
multi-gateway payment processing library for PHP 5.6+. This package implements WorldPay XML Corporate Gateway Hosted
support for Omnipay. This implementation has been guided by the [documentation available on WorldPay](http://support.worldpay.com/support/kb/gg/corporate-gateway-guide/content/home.htm) 

## Installation

This library is best installed via [Composer](http://getcomposer.org/) e.g.

```bash
composer require catharsisjelly/omnipay-worldpay-cg-hosted
```

## Basic Usage

The following gateways are provided by this package:

* WorldPay Corporate Gateway Hosted

For general usage instructions, please see the main
[Omnipay](https://github.com/omnipay/omnipay) repository.

## Order description

We currently set the order description to "Merchandise" by default.

To set another one, use `PurchaseRequest::setDescription()`.

## Notification setup

This library aims to be able to give sensible answers for `isValid()`, `isAuthorised()`, `isPending()` and `isCancelled()`, if you select almost every possible type on this screen:

![Notification setup](./docs/Notification%20setup.png "Notification setup")

Generally it's good to tick everything you can, so as not to miss notifications you might care about.

However we've had to **not tick _SENT_FOR_AUTHORISATION_** because of an apparent Worldpay bug that sends two _AUTHORISED_ notifications when this is selected, instead of sending the additional type.

We have not yet had confirmation from Worldpay on whether this is expected to affect the production gateway or if it will be fixed, so it's safer not to enable this notification for now.

## Notification verification

Worldpay recommend the following to receive notifications and check they came from them:

1. Use server SSL - this should be standard for any payment app!
2. Check the IP's reverse DNS records - this actually isn't very secure and can be spoofed, but is implemented in this library as a first line of defence. There is an IP fallback for known Worldpay subnets, in case your environment can't do public reverse DNS lookups.
3. Request for them to send, and validate on your server, their client TLS certificate.

Unfortunately there's no easy server- and environment-agnostic way to implement client TLS verification (point 3) within this library, and for our apps' current infrastructure it wasn't feasible. Some of the work would generally live in your web server anyway rather than this library.

We also considered an additional measure, [inquiry requests](http://support.worldpay.com/support/kb/gg/corporate-gateway-guide/content/manage/inquiryrequests.htm), but as this document explains they're not enabled by default and are also nowhere near real-time (~5 minutes latency). This means they are not suitable for checking notifications' validity unless you queue them and accept much longer delays.

This means that for now you should _not_ consider notifications shown as 'valid' in this library to be guaranteed valid, i.e. **do not ship goods based on this without a later check**, using inquiry requests or the Worldpay UI, that gives a matching result.

Any ideas or contributions that you think might improve this situation would be very welcome!

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](https://stackoverflow.com/). Be sure to add the
[omnipay tag](https://stackoverflow.com/questions/tagged/omnipay) so it can be
easily found.

If you want to keep up to date with release announcements, discuss ideas for the
project, or ask more detailed questions, there is also a
[mailing list](https://groups.google.com/forum/#!forum/omnipay) which you can
subscribe to.

If you believe you have found a bug, please report it using the
[issue tracker](https://github.com/catharsisjelly/omnipay-worldpay-cg-hosted/issues), or
better yet, fork the library and submit a pull request.
