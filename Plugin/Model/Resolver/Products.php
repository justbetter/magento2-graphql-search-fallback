<?php

declare(strict_types=1);

namespace JustBetter\GraphqlSearchFallback\Plugin\Model\Resolver;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products as ProductsResolver;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Products
{
    public function __construct(
        private CollectionFactory $productCollectionFactory,
        private Uid $uidEncoder
    ) {
    }

    public function aroundResolve(
        ProductsResolver $subject,
        callable $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        return $this->getFallbackResult($args);
    }

    private function getFallbackResult(array $args): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        
        if (isset($args['filter'])) {
            $this->applyFilters($collection, $args['filter']);
        }
        
        $currentPage = $args['currentPage'] ?? 1;
        $pageSize = $args['pageSize'] ?? 20;
        $collection->setPageSize($pageSize)->setCurPage($currentPage);
        
        $products = [];
        foreach ($collection as $product) {
            $products[] = $product->getData() + ['model' => $product];
        }
        
        return [
            'total_count' => $collection->getSize(),
            'items' => $products,
            'suggestions' => [],
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage,
                'total_pages' => ceil($collection->getSize() / $pageSize)
            ],
            'search_result' => null,
            'layer_type' => 'catalog'
        ];
    }

    private function applyFilters($collection, array $filters): void
    {
        foreach ($filters as $field => $condition) {
            if ($field === 'category_uid') {
                $categoryId = $this->uidEncoder->decode((string)$condition['eq']);
                $collection->addCategoriesFilter(['eq' => $categoryId]);
            } elseif ($field === 'category_id') {
                $collection->addCategoriesFilter($condition);
            } else {
                foreach ($condition as $operator => $value) {
                    $filter = match($operator) {
                        'match' => ['like' => '%' . $value . '%'],
                        'eq' => ['eq' => $value],
                        'in' => ['in' => $value],
                        'from' => ['gteq' => $value],
                        'to' => ['lteq' => $value],
                        default => [$operator => $value]
                    };
                    $collection->addAttributeToFilter($field, $filter);
                }
            }
        }
    }
}
