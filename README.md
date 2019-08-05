# magento-ceneo-pl
Magento 1 module for Ceneo.pl comparison shopping engine

This module allows you to map the attributes of your products with all attributes from Ceneo.pl from various industries and assign your products to specific categories from Ceneo.pl.

You can use highly configurable mass actions or simply edit products one by one.

Mappings can be run manually, but the Cron Magento also runs them on a daily basis.

As a result You get publicly accessible dynamically generated XML files (one for each store view) that you can add to your accounts at Ceneo.pl.

It's possible to ues generated XML files in other comparison shopping engines e.g. Skapiec.pl, Radar.pl, Okazje.info.pl, Kupujemy.pl, Smartbay.pl and Cenuj.pl.

Funkcje, którymi moduł płatny różni się od darmowego to:

Features:
* Ability to map all attributes of Ceneo.pl (from all product groups) with Magento attributes
* Support for configurable products
* Advanced interface for assigning products to categories from Ceneo.pl
* Automation of assigning products to categories from Ceneo.pl (CRON)
* Import of current Ceneo.pl categories at any time
* Allows using ID in the feed instead of product SKU (Ceneo does not accept certain SKUs, e.g. spaces containing spaces)
* Allows using watermarked images in the feed.