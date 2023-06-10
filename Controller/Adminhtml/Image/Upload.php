<?php

declare(strict_types = 1);

namespace Magelearn\ProductImage\Controller\Adminhtml\Image;

use Exception;
use Magelearn\ProductImage\Model\ImageIO;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Upload extends Action
{
    /**
     * @var ImageIO
     */
    private $imageIO;

    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param ImageIO $imageIO
     */
    public function __construct(
        Context $context,
        ImageIO $imageIO
    ) {
        $this->imageIO = $imageIO;
        parent::__construct($context);
    }
    
    /**
     * @return mixed
     */
    public function _isAllowed() {
        return $this->_authorization->isAllowed('Magelearn_ProductImage::upload');
    }
    
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $imageId = $this->_request->getParam('param_name', 'product_label_image');
        
        try {
            $result = $this->imageIO->saveFileToTmpDir($imageId);
            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (Exception $e) {
            $result = [
                'error'     => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
