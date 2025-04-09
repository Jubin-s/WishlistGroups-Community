<?php

namespace Egits\WishlistGroups\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultInterface;
use Egits\WishlistGroups\Model\WishlistFactory;
use Egits\WishlistGroups\Model\ResourceModel\WishlistItem as WishlistItemResource;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use Egits\WishlistGroups\Model\ResourceModel\WishlistItem\CollectionFactory;
use Magento\Framework\Message\ManagerInterface;
use Egits\WishlistGroups\Model\WishlistItemFactory; // Import the factory

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
     * @var WishlistItemResource
     */
    protected $wishlistItemResource;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var ManagerInterface 
     */
    protected $messageManager;
    /**
     * @var WishlistItemFactory
     */
    protected $wishlistItemFactory; // Inject the factory


    public function __construct(
        JsonFactory $jsonFactory,
        Session $customerSession,
        WishlistFactory $wishlistFactory,
        ProductRepository $productRepository,
        RequestInterface $request,
        LoggerInterface $logger,
        WishlistItemResource $wishlistItemResource,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        WishlistItemFactory $wishlistItemFactory // Inject the model factory here
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
        $this->wishlistFactory = $wishlistFactory;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->logger = $logger;
        $this->wishlistItemResource = $wishlistItemResource;
        $this->collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        $this->wishlistItemFactory = $wishlistItemFactory; // Assign the factory
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

            $wishlistId = $this->request->getParam('wishlist_id');
            $productId = $this->request->getParam('product_id');
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

            $wishlist = $this->wishlistFactory->create()->load($wishlistId);
            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                throw new LocalizedException(__('Invalid wishlist.'));
            }

            $isAlreadyPresent = $this->collectionFactory->create()
                ->addFieldToFilter('wishlist_id', $wishlistId)
                ->addFieldToFilter('product_id', $productId);

            $existingItem = $isAlreadyPresent->getFirstItem();
            $existingItemId = $existingItem->getWishlistId();

            // If item already exists, update the quantity
            if ($existingItemId == $wishlistId) {
                $existingQty = $existingItem->getQty();
                $this->logger->info("Current Quantity: " . $existingQty);
                $existingItem->setQty($existingQty + 1);
                $this->wishlistItemResource->save($existingItem);  // Save using resource model
                $this->logger->info("Updated Quantity: " . $existingItem->getQty());
            } else {
                // If item is not present, create a new item using the factory
                $newItem = $this->wishlistItemFactory->create(); // Use the factory to create the model
                $newItem->setWishlistId($wishlistId)
                        ->setProductId($productId)
                        ->setQty(1);
                $this->wishlistItemResource->save($newItem);  // Save the new item using resource model
                $this->logger->info("New item added with qty 1.");
            }

            $this->messageManager->addSuccessMessage(__('Product added to the selected wishlist successfully.'));
            return $result->setData([
                'success' => true,
                'message' => __('Product added to the selected wishlist successfully.')
            ]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to add product to the selected wishlist'));
            $this->logger->error("Wishlist Error: " . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
