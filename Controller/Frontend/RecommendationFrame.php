<?php

namespace Omega\Cyberkonsultant\Controller\Frontend;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Omega\Cyberkonsultant\Mapper\ProductMapper;
use Omega\Cyberkonsultant\Provider\CategoriesProvider;
use Omega\Cyberkonsultant\Provider\ProductsProvider;

class RecommendationFrame extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var ProductsProvider
     */
    protected $productsProvider;

    /**
     * @var ProductMapper
     */
    protected $productMapper;

    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * @var CategoriesProvider
     */
    protected $categoriesProvider;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context            $context,
        PageFactory        $resultPageFactory,
        JsonFactory        $jsonFactory,
        ProductsProvider   $productsProvider,
        ApiClient          $apiClient,
        CategoriesProvider $categoriesProvider
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonFactory = $jsonFactory;
        $this->productsProvider = $productsProvider;
        $this->apiClient = $apiClient;
        $this->categoriesProvider = $categoriesProvider;
    }

    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];

        try {
            $result['frames'] = $this->apiClient->getRecommendationFrames(
                $this->getRequest()->getCookie(UuidCookie::COOKIE_NAME)
            );
            $result['success'] = true;
        } catch (\Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }

        return $this->jsonFactory->create()->setData($result);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
