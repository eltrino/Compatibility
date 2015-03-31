# Compatibility

This extension is built for Magento 1.x. It allows to use newer extensions built for Magento 2 on older version of Magento.

There are following main parts of this extension:
- Adapters which helps to run new architecture elements (layouts, controllers, helpers, etc) on the old platform
- Extended autoloader
- Class placeholders to retain inheritance between classes

## Installation

Download this extension from Github and unpack it into Magento root folder.

Also it’s possible to install it through composer. Make sure you have added [Firegento  repository](http://packages.firegento.com/) to your `composer.json`.

Add as dependency to your project using composer

```bash
composer require eltrino/compatibility:dev-master
```

## Bugs

Please report all found issues or suggest improvements as Github issues.