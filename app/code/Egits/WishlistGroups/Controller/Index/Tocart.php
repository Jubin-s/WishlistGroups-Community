<?php
namespace Egits\WishlistGroups\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\SessionFactory;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Tocart
 */
class Tocart extends Action implements HttpPostActionInterface
{
    /** @var SessionFactory */
    private $checkoutSession;

        /** @var Cart */
        private $cart;

    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Json */
    private $json;

    /** @var JsonFactory */
    protected $jsonFactory;

    /** @var LoggerInterface */
    protected $logger;

    /** @var ManagerInterface */
    protected $messageManager;

    /**
     * Tocart constructor.
     * @param Context $context
     * @param Json $json
     * @param SessionFactory $checkoutSession
     * @param Cart $cart
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Json $json,
        SessionFactory $checkoutSession,
        Cart $cart,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        JsonFactory $jsonFactory,
        LoggerInterface $logger,
        ManagerInterface $messageManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quote = $cart;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->json = $json;
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            $result = $this->jsonFactory->create();

            // Retrieve the product IDs from the request
            $formData = $this->getRequest()->getParam('itemData'); // Array of product data
            $this->logger->info('Item Data: ' . json_encode($formData));
            
            // Initialize the quote and session
            $session = $this->checkoutSession->create();
            // $quote = $session->getQuote();
            $quote = $this->quote;
            // Loop through each product in itemData and add it to the cart
            foreach ($formData as $item) {
                $productId = $item['id']; // Assuming 'id' is the product ID
                $qty = isset($item['qty']) ? $item['qty'] : 1; // Default to quantity of 1 if not set
                
                // Load the product by ID
                $product = $this->productRepository->getById($productId);
                if ($product) {
                    // Add the product to the quote (cart)
                    $quote->addProduct($product, $qty);
                }
            }

            // Save the updated quote to the cart repository
            $this->cartRepository->save($quote);

            // Replace the quote in the session
            $session->replaceQuote($quote)->unsLastRealOrderId();
            $this->messageManager->addSuccessMessage(__('Products added to cart'));
            // Return success response
            return $result->setData([
                'success' => true,
                'message' => __('Products successfully added to your cart.')
            ]);
        } catch (\Exception $e) {
            // Log and handle any errors
            $this->logger->error('Error adding products to cart: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__($e->getMessage()));

            return $result->setData([
                'success' => false,
                'message' => __('Unable to add products to cart.')
            ]);
        }
    }
}
