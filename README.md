<p align="center"><a href="https://pay2.hu" target="_blank"><img src="https://iconocoders.s3.amazonaws.com/site/com-assets/frontend/facebook_cover_photo_2.png" width="400"></a></p>

## Pay2.hu Client
A Pay2.hu egy gyors, egyszerűen integrálható fizetési kapu szolgáltatás OTP Simple és Barion fizetési rendszerekhez. A rendszert partnereink igényei alapján hoztuk létre, az integráció és a jóváhagyás akár 1 munkanap alatt** megtörténhet.

Amennyiben felkeltettük érdeklődését, lépjen velünk kapcsolatba lérhetőségeink egyikén és fogadjon el **akár már ma bankkártyás fizetést webáruházában!

**Amennyiben minden technikai és szerződési feltétel rendelkezésre áll.

## Version

0.4.0 (v2)
- Barion updates, fixes (callback)
- Coupon (discount) support
- Fixes, tweaks
- new API domain from this version

0.3.0
- Fixes, tweaks
- Updated client js support
- Now support vendor js (Barion)

0.2.0
- Pay2.hu now supports custom products
- Added payment status api endpoint for check transactions
- Added client js / metrics (not mandatory)

0.1.8
- $paymentTosHtml is not a mandatory parameter anymore
- send client version to API

0.1.7
With $useLegacyDotenv you can use vlucas/phpdotenv 3.x in older projects/frameworks versions

## Requirements

 * PHP 7.2+

## Installation

Pay2 Client is available at packagist.org, so you can use composer to download this library.

```
{
    "require": {
        "progcode/pay2-client": "dev-develop"
    }
}
```

## Source code
https://github.com/progcode/pay2-client
