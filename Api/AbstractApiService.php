<?php
namespace Omega\Cyberkonsultant\Api;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Omega\Cyberkonsultant\Helper\Configuration;
use Omega\Cyberkonsultant\Mapper\AttributeMapper;
use Omega\Cyberkonsultant\Mapper\CategoryMapper;
use Omega\Cyberkonsultant\Mapper\ProductMapper;
use Omega\Cyberkonsultant\Provider\AttributesProvider;
use Omega\Cyberkonsultant\Provider\CategoriesProvider;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractApiService
{
    protected $categoriesProvider;
    protected $categoryMapper;
    protected $attributesProvider;
    protected $attributeMapper;
    protected $resourceConnection;
    protected $collectionFactory;
    protected $productMapper;
    protected $configuration;

    public function __construct(
        CategoriesProvider $categoriesProvider,
        CategoryMapper $categoryMapper,
        AttributesProvider $attributesProvider,
        AttributeMapper $attributeMapper,
        ResourceConnection $resourceConnection,
        CollectionFactory $collectionFactory,
        ProductMapper $productMapper,
        Configuration $configuration
    ) {
        $this->categoriesProvider = $categoriesProvider;
        $this->categoryMapper = $categoryMapper;
        $this->attributesProvider = $attributesProvider;
        $this->attributeMapper = $attributeMapper;
        $this->resourceConnection = $resourceConnection;
        $this->collectionFactory = $collectionFactory;
        $this->productMapper = $productMapper;
        $this->configuration = $configuration;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return Request::createFromGlobals();
    }

    public function checkPermission()
    {
        $secret = base64_decode($this->configuration->getGeneralConfig('remote_command_secret'));
        $public = base64_decode($this->getRequest()->get('key'));

        @openssl_sign(null, $signature, $secret, "sha256WithRSAEncryption");
        $verified = @openssl_verify(null, $signature, $public, OPENSSL_ALGO_SHA256);

        if (!$verified) {
            $this->jsonResponse(['error' => 'Access denied'], 405);
        }
    }

    public function jsonResponse($data, $statusCode = 200)
    {
        header_remove();
        http_response_code($statusCode);
        header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
        header('Content-Type: application/json');
        $status = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported',
        ];
        header('Status: '.$statusCode.' '.$status[$statusCode]);
        echo json_encode($data);
        exit;
    }
}
