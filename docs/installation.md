# Installation & Setup

To install the full package, run the following [Composer](https://getcomposer.org/) command:

```bash
composer require pagerfanta/pagerfanta
```

Pagerfanta is also split into several smaller packages to allow more granular control over the features used and dependencies required. All packages are dependent on the `pagerfanta/core` package which contains all of the interfaces for the Pagerfanta API, the `Pagerfanta\Pagerfanta` class, and the PHP based views and templates.

The following first party packages are available to include additional functionality:

- `pagerfanta/doctrine-collections-adapter`: Provides a pagination adapter for [`Doctrine\Common\Collections\Collection`](https://www.doctrine-project.org/projects/collections.html) and `Doctrine\Common\Collections\Selectable` implementations
- `pagerfanta/doctrine-dbal-adapter`: Provides support for the [Doctrine DBAL](https://www.doctrine-project.org/projects/dbal.html) package
- `pagerfanta/doctrine-mongodb-odm-adapter`: Provides support for the [Doctrine MongoDB ODM](https://www.doctrine-project.org/projects/mongodb-odm.html) package
- `pagerfanta/doctrine-orm-adapter`: Provides support for the [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html) package
- `pagerfanta/doctrine-phpcr-odm-adapter`: Provides support for the [Doctrine PHPCR ODM](https://www.doctrine-project.org/projects/phpcr-odm.html) package
- `pagerfanta/elastica-adapter`: Provides support for [Elastica](https://elastica.io/) (an ElasticSearch PHP client)
- `pagerfanta/solarium-adapter`: Provides support for [Solarium](https://github.com/solariumphp/solarium) (a Solr search client)
- `pagerfanta/twig`: Provides support for [Twig](https://twig.symfony.com/)
