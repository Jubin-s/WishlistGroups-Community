<?php

namespace Egits\WishlistGroups\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultInterface;
use Egits\WishlistGroups\Model\WishlistFactory;
use Egits\WishlistGroups\Model\WishlistItem as ResourWishlistFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use Egits\WishlistGroups\Model\ResourceModel\WishlistItem\CollectionFactory;

/**
 * Class Existing
 * @package Egits\WishlistGroups\Controller\Index
 */
class Existing implements ActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ResourWishlistFactory
     */
    protected $resourceFactory;
     /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    public function __construct(
        JsonFactory $jsonFactory,
        Session $customerSession,
        WishlistFactory $wishlistFactory,
        ProductRepository $productRepository,
        RequestInterface $request,
        LoggerInterface $logger,
        ResourWishlistFactory $resourceFactory,
        CollectionFactory $collectionFactory
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
        $this->wishlistFactory = $wishlistFactory;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->logger = $logger;
        $this->resourceFactory = $resourceFactory;
        $this->collectionFactory = $collectionFactory;

    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData([
                'success' => false,
                'message' => __('You must be logged in to add items to the wishlist.')
            ]);
        }

        try {
            $customerId = $this->customerSession->getCustomerId();

            // Fetch request parameters
            $wishlistId = $this->request->getParam('wishlist_id');
            $productId = $this->request->getParam('product_id'); // Ensure this is product_id

            // Log incoming data for debugging
            $this->logger->info("Received Data: Wishlist ID - " . $wishlistId . ", Product ID - " . $productId);

            if (!$wishlistId) {
                throw new LocalizedException(__('Wishlist ID is required.'));
            }
            if (!$productId) {
                throw new LocalizedException(__('Product ID is required.'));
            }

            // Load product
            $product = $this->productRepository->getById($productId);
            if (!$product->getId()) {
                throw new LocalizedException(__('Invalid product.'));
            }

            // Load the wishlist by ID
            $wishlist = $this->wishlistFactory->create()->load($wishlistId);
            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                throw new LocalizedException(__('Invalid wishlist.'));
            }
            // Fetch the collection of wishlist items
// Fetch the collection of wishlist items based on wishlist_id and product_id
$isAlreadyPresent = $this->collectionFactory->create()
    ->addFieldToFilter('wishlist_id', $wishlistId)
    ->addFieldToFilter('product_id', $productId);

$this->logger->info("Checking Wishlist ID: " . $wishlistId . " with Product ID: " . $productId);
$this->logger->info("Query for existing item: " . $isAlreadyPresent->getSelect()->__toString()); // Log SQL query for debugging

// Log the collection data
$this->logger->info("Wishlist items: " . print_r($isAlreadyPresent->getData(), true));

$existingItem = $isAlreadyPresent->getFirstItem(); // Retrieve the first matching item
$existingItemId = $existingItem->getWishlistId();
// Check if the item exists in the collection
if ($existingItemId == $wishlistId) {
    $existingQty = $existingItem->getQty();  // Access the qty field from the item
    $this->logger->info("Current Quantity: " . $existingQty);

    // Increase the quantity by 1
    $existingItem->setQty($existingQty + 1);
    $existingItem->save(); // Save the updated item
    $this->logger->info("Updated Quantity: " . $existingItem->getQty());
} else {
    // If no matching item found, add a new item with qty = 1
    $this->resourceFactory->setWishlistId($wishlistId)
        ->setProductId($productId)
        ->setQty(1)  // Set initial quantity
        ->save();
    $this->logger->info("New item added with qty 1.");
}

return $result->setData([
    'success' => true,
    'message' => __('Product added to the selected wishlist successfully.')
]);

        } catch (\Exception $e) {
            $this->logger->error("Wishlist Error: " . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
