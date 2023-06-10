<?php

declare(strict_types = 1);

namespace Magelearn\ProductImage\Model\Product\Attribute\Backend;

use Magelearn\ProductImage\Model\ImageIO;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

class ImageUploader extends AbstractBackend
{
    /**
     * @var ImageIO
     */
    private $imageIO;

    /**
     * ImageUploader constructor.
     *
     * @param ImageIO $imageIO
     */
    public function __construct(
        ImageIO $imageIO
    ) {
        $this->imageIO = $imageIO;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($object)
    {
        $attribute = $this->getAttribute();
        $attrCode  = $attribute->getAttributeCode();
        $value     = $object->getData($attrCode);

        if ($value && is_array($value) && isset($value[0])) {
            if (!isset($value[0]['id'])) {
                $object->setData($attrCode, null);
            } elseif (isset($value[0]['url']) && $this->isWysiwygPath($value[0]['url'])) {
                $object->setData($attrCode, $value[0]['url']);
            } elseif (isset($value[0]['file'])) {
                $object->setData($attrCode, $value[0]['file']);
            }
        }

        return parent::beforeSave($object);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());
        
        if ($value && is_array($value) && isset($value[0]) && isset($value[0]['url'])) {
            $imagepath = (string)$value['0']['url'];
            if($this->isWysiwygPath($imagepath)) {
                $this->imageIO->moveFileFromTmp($imagepath);
            }
        } elseif ($value && !$this->isWysiwygPath($value)) {
            $this->imageIO->moveFileFromTmp($value);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function afterLoad($object)
    {
        $attrName = $this->getAttribute()->getName();

        if ($value = $object->getData($attrName)) {
            $isWysiwygPath = $this->isWysiwygPath($value);

            $object->setData(
                $attrName,
                [
                    [
                        'name' => $isWysiwygPath ? $this->getFileNameFromPath($value) : $value,
                        'url'  => $this->imageIO->getImageUrl($value, $isWysiwygPath),
                        'type' => 'image',
                    ]
                ]
            );
        }

        return parent::afterLoad($object);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function isWysiwygPath(string $path): bool
    {
        return (bool) strpos($path, 'wysiwyg');
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getFileNameFromPath(string $path): string
    {
        $parts = explode('/', $path);

        return end($parts);
    }
}
