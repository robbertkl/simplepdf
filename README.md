# SimplePdf

[![Latest Stable Version](https://poser.pugx.org/robbertkl/simplepdf/v/stable.png)](https://packagist.org/packages/robbertkl/simplepdf)

Internal PDF geometry (and therefore ZendPdf as well) uses "points" (1/72 of an inch), with [0, 0] being the bottom left corner of a page.
This library, which extends ZendPdf, changes this by using arbitrary units (e.g. inches or centimeters) and going from top-to-bottom, which makes much more sense from a user's persepective.
Also, very basic functionality has been added, like text alignment and word wrap.

SimplePdf is [PSR-0](http://www.php-fig.org/psr/psr-0/), [PSR-1](http://www.php-fig.org/psr/psr-1/) and [PSR-2](http://www.php-fig.org/psr/psr-2/) compliant.

[Semantic Versioning](http://semver.org/) is used for releases / tags.

## Requirements

* PHP 5.3 or newer
* [ZendPdf](https://github.com/zendframework/ZendPdf) component, which it extends

## Installation

The easiest way to install is using [Composer](http://getcomposer.org) / [Packagist](https://packagist.org/packages/robbertkl/simplepdf) by adding this to you `composer.json` file:

```json
"require": {
    "robbertkl/simplepdf": "dev-master"
}
```

Alternatively, you could manually include/autoload the appriate files from the `classes/` dir.

## Documentation

See the [examples/](examples/) dir for usage examples.
Also, check out the [API documentation](http://robbertkl.github.io/simplepdf/), generated using [ApiGen](http://apigen.org).

## Known Limitations

* The extension is not complete at all; only specific methods (to accommodate my needs) have been overridden to handle custom units + top-to-bottom geometry
* The word-wrap code is still pretty basic; for example, it doesn't do word-breaking or handle certain border cases
* Unfortunately, ZendPdf was kicked out of ZF2, and doesn't seem to be maintained (although it still works fine)

## Authors

* Robbert Klarenbeek, <robbertkl@renbeek.nl>

## License

SimplePdf is published under the [MIT License](http://www.opensource.org/licenses/mit-license.php).
