<?php

namespace Egits\WishlistGroups\Controller\Index;

use Magento\Framework\App\ActionInterface;
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
use Magento\Quote\Model\QuoteFactory;
use Egits\WishlistGroups\Model\ResourceModel\WishlistItem\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Class Tocart
 */
class Tocart implements ActionInterface, HttpPostActionInterface
{
    /** 
     * @var SessionFactory 
     */
    private $checkoutSession;

    /** 
     * @var Cart 
     */
    private $cart;

    /** 
     * @var CartRepositoryInterface 
     */
    private $cartRepository;

    /** 
     * @var ProductRepositoryInterface 
     */
    private $productRepository;

    /** 
     * @var Json 
     */
    private $json;

    /** 
     * @var JsonFactory 
     */
    protected $jsonFactory;

    /** 
     * @var LoggerInterface 
     */
    protected $logger;

    /** 
     * @var ManagerInterface 
     */
    protected $messageManager;

    /** 
     * @var QuoteFactory 
     */
    private $quoteFactory;

    /**
     * @var WishlistItemCollectionFactory
     */
    protected $wishlistItemCollectionFactory;

     /**
     * @var RequestInterface
     */
    protected $request;

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
     * @param QuoteFactory $quoteFactory
     * @param WishlistItemCollectionFactory $wishlistItemCollectionFactory
     * @param RequestInterface $request
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
        ManagerInterface $messageManager,
        QuoteFactory $quoteFactory,
        WishlistItemCollectionFactory $wishlistItemCollectionFactory,
        RequestInterface $request
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->json = $json;
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->quoteFactory = $quoteFactory;
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->request = $request;
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $productsAdded = false; 

        try {
            $result = $this->jsonFactory->create();
            $formData = $this->request->getParam('itemData'); // Array of product data
            $this->logger->info('Item Data: ' . json_encode($formData));
            $session = $this->checkoutSession->create();
            $quote = $session->getQuote();

            foreach ($formData as $item) {
                $productId = $item['id']; 
                $qty = isset($item['qty']) ? $item['qty'] : 1; 

                $product = $this->productRepository->getById($productId);
                if ($product) {
                    // Check if the product is a simple product
                    if ($product->getTypeId() !== 'simple') {
                        // If the product is not a simple product, show an error message
                        $this->messageManager->addErrorMessage(__('Product "%1" is not a simple product and cannot be added to the cart.', $product->getName()));
                        continue; 
                    }

                    // Add the product to the quote (cart)
                    $quote->addProduct($product, $qty);
                    $productsAdded = true; 

                    // Remove the product from the wishlist after adding to the cart
                    $wishlistItemCollection = $this->wishlistItemCollectionFactory->create()
                        ->addFieldToFilter('product_id', $productId);
                    foreach ($wishlistItemCollection as $wishlistItem) {
                        // Delete the wishlist item
                        $wishlistItem->delete();
                    }
                }
            }

            // Save the updated quote to the cart repository if any product was added
            if ($productsAdded) {
                $this->cartRepository->save($quote);

                // Replace the quote in the session
                $session->replaceQuote($quote)->unsLastRealOrderId();
                $this->messageManager->addSuccessMessage(__('Products added to cart'));
            }
            return $result->setData([
                'success' => $productsAdded,
                'product_added' => $productsAdded,
                'message' => $productsAdded ? __('Products successfully added to your cart.') : __('No products were added to the cart.')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error adding products to cart: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__($e->getMessage()));

            return $result->setData([
                'success' => false,
                'message' => __('Unable to add products to cart.')
            ]);
        }
    }
}
