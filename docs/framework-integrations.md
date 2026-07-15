# Framework Integrations

Pagerfanta has a broad footprint across the PHP ecosystem, from bundles that wire it directly into a framework, to major open source content management systems, e-commerce platforms, and libraries that use it as their pagination layer under the hood.

## Direct Integrations

The following packages are purpose-built to integrate Pagerfanta into a specific framework or CMS, typically providing configuration, service wiring, and framework-native conventions on top of the core library.

- [`BabDevPagerfantaBundle`](https://github.com/BabDev/PagerfantaBundle) - Symfony Framework
- [`typo3_pagerfanta`](https://github.com/sabbelasichon/typo3_pagerfanta) - TYPO3

## Used Across the Ecosystem

Beyond direct framework bundles, Pagerfanta is depended upon by a wide range of projects, including several with a large install base of their own. A sample of notable projects is highlighted below by category.

### Content Management & Commerce Platforms

- [Ibexa DXP](https://www.ibexa.co) - Digital experience platform built on Symfony (successor to eZ Publish / eZ Platform)
- [Concrete CMS](https://www.concretecms.com) - Open source content management system
- [Kunstmaan CMS](https://cms.kunstmaan.be) - Symfony-based CMS bundles
- [Sylius](https://sylius.com) - E-commerce platform built on Symfony

### Applications

- [`shlinkio/shlink`](https://packagist.org/packages/shlinkio/shlink) - Self-hosted URL shortener with a CLI and REST API
- [`wallabag/wallabag`](https://packagist.org/packages/wallabag/wallabag) - Self-hostable read-it-later application
- [`novosga/novosga`](https://packagist.org/packages/novosga/novosga) - Customer queue management system

### Libraries & SDKs

- [`league/fractal`](https://packagist.org/packages/league/fractal) - Widely used library for transforming data for API output; one of the most-installed packages depending on Pagerfanta
- [`friendsofsymfony/elastica-bundle`](https://packagist.org/packages/friendsofsymfony/elastica-bundle) - Elasticsearch integration for Symfony
- [`willdurand/hateoas`](https://packagist.org/packages/willdurand/hateoas) - Library for building HATEOAS-compliant REST representations

<div class="docs-note">This list is a curated sample, not an exhaustive one. You can browse a full list of dependents on Packagist for <a href="https://packagist.org/packages/pagerfanta/core/dependents" target="_blank" rel="noopener nofollow">pagerfanta/core</a> and <a href="https://packagist.org/packages/pagerfanta/pagerfanta/dependents" target="_blank" rel="noopener nofollow">pagerfanta/pagerfanta</a>.</div>
