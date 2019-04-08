# [Upgrade from v7.1.0 to Unreleased]

This guide contains instructions to upgrade from version v7.1.0 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]
### Tools
- add `product-search-recreate-structure,product-search-export-products` to the end of `test-db-demo` phing target in your `build-dev.xml` for export products data to Elasticsearch while run tests ([#933](https://github.com/shopsys/shopsys/pull/933))


[Upgrade from v7.1.0 to Unreleased]: https://github.com/shopsys/shopsys/compare/v7.1.0...HEAD
