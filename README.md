# Magento 2 GraphQL Search Fallback

A simple and lightweight Magento 2 module that provides a fallback mechanism for GraphQL product queries when the search engine (Elasticsearch/OpenSearch) is disabled.

## Why this module was created
We initially used the `zepgram/module-disable-search-engine` module to disable the search engine, but quickly discovered that product reviews were no longer available via GraphQL. This revealed that the search engine was still required for product queries in GraphQL. This module solves that problem by providing a fallback mechanism, ensuring that product queries (including reviews) continue to work even when the search engine is disabled.

## Features
- **Complete search engine bypass**: Directly uses Magento product collections instead of search engine
- **Filter support**: Works with all standard GraphQL product filters (SKU, name, price, categories, etc.)
- **Zero configuration**: Works out of the box after installation

## Installation
### Via Composer (Recommended)
```bash
composer require justbetter/magento2-graphql-search-fallback
php bin/magento module:enable JustBetter_GraphqlSearchFallback
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## Usage
The module works automatically once installed. It intercepts all GraphQL `products` queries and provides results using direct database queries instead of the search engine.

### Supported GraphQL Filters
- `sku: {eq: "product-sku"}`
- `name: {match: "product name"}`
- `price: {from: 10, to: 100}`
- `category_id: {eq: "123"}`
- `category_uid: {eq: "encoded-category-uid"}`
- Any custom product attribute with operators: `eq`, `in`, `match`, `from`, `to`

## Technical Details
### How It Works
1. **Plugin Interception**: Uses `aroundResolve` plugin on `Magento\CatalogGraphQl\Model\Resolver\Products`
2. **Direct Collection**: Creates product collection directly via `CollectionFactory`
3. **Filter Mapping**: Maps GraphQL filter operators to Magento collection filters
4. **Standard Response**: Returns the same response format as the original resolver

## Requirements
- Magento 2.4.8 or higher
- PHP 8.1 or higher

## Support
This module is developed and maintained by [JustBetter](https://justbetter.nl).

## License
[MIT License](LICENSE)
