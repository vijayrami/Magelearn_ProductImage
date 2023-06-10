<?php

declare(strict_types = 1);

namespace Magelearn\ProductImage\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

class Image extends AbstractModifier
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        if (isset($meta['content']['children']['container_product_label_image'])) {
            $meta['content']['children']['container_product_label_image']['children'] = array_replace_recursive(
                $meta['content']['children']['container_product_label_image']['children'],
                [
                    'product_label_image' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'visible' => 0,
                                ],
                            ]
                        ]
                    ]
                ]
            );
        }

        return $meta;
    }
}
