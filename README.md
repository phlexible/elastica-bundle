PhlexibleElasticaBundle
=======================

The PhlexibleElasticaBundle adds support for elasticsearch indexes in phlexible.

Installation
------------

1. Download PhlexibleElasticaBundle using composer
2. Enable the Bundle
3. Configure the PhlexibleElasticaBundle
4. Clear the symfony cache

### Step 1: Download PhlexibleElasticaBundle using composer

Add PhlexibleElasticaBundle by running the command:

``` bash
$ php composer.phar require phlexible/elastica-bundle "~1.0.0"
```

Composer will install the bundle to your project's `vendor/phlexible` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Phlexible\Bundle\ElasticaBundle\PhlexibleElasticaBundle(),
    );
}
```

### Step 3: Configure the PhlexibleElasticaBundle

Now that the bundle is enabled, you need to configure the PhlexibleElasticaBundle.
Add the following configuration to your config.yaml file.

``` yaml
# app/config/config.yaml
phlexible_elastica:
    clients:
        default:
            host: your_elasticsearch_host
            port: 9200
            logger: logger
    indexes:
        default:
            index_name: your_index_name
```

### Step 4: Clear the symfony cache

If you access your phlexible application with environment prod, clear the cache:

``` bash
$ php app/console cache:clear --env=prod
```
