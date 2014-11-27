#!/bin/sh
php console.php

php ../shell/indexer.php --reindex catalog_category_product
php ../shell/indexer.php --reindex catalog_product_price
php ../shell/indexer.php --reindex cataloginventory_stock
php ../shell/indexer.php --reindex catalog_url
