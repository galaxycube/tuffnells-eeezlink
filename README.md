# Tuffnells / Eeezlink Wrapper

Board out of my frustration with a lack of an API for [Tuffnells Courier](https://tuffnells.co.uk/) I have created this lovely package that wraps around their Eeezlink web application.

Right now it allows you to CREATE, AMEND, DELETE, and TRACK consignments.  You can also get ZPL Labels and using the [Labelary](http://labelary.com/viewer.html) api you can get PDF/PNG versions of the labels.

It's been tested with Mainland U.K addresses only just now.  So it does not support creation of consignments for Northern Ireland with the whole Brexit/Europe issues in supplying commercial invoices.

## Installation

Install via composer

```bash
composer require galaxycube/tuffnells-eeezlink
```

## Usage

See examples folder for usage examples.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

Standard MIT License