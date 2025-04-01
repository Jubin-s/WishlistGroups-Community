<?php

namespace Egits\WishlistGroups\Block;

use Magento\Framework\View\Element\Template;
use Egits\WishlistGroups\Model\ResourceModel\WishlistItem\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\View\Element\Template\Context;
use Egits\WishlistGroups\Model\WishlistItem;

class Index extends Template
{
    protected $wishlistItemCollectionFactory;
    protected $customerSession;
    protected $_productRepository; // Declare ProductRepository
    protected $wishlistItem;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CollectionFactory $wishlistItemCollectionFactory
     * @param Session $customerSession
     * @param ProductRepository $productRepository
     * @param WishlistItem $wishlistItem
     */
    public function __construct(
        Context $context,
        CollectionFactory $wishlistItemCollectionFactory,
        Session $customerSession,
        ProductRepository $productRepository, // Inject ProductRepository
        WishlistItem $wishlistItem
    ) {
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->customerSession = $customerSession;
        $this->_productRepository = $productRepository; // Assign ProductRepository to property
        $this->wishlistItem = $wishlistItem; // Assign ProductRepository to property
        parent::__construct($context);
    }

    /**
     * Get the current customer's wishlist
     *
     * @param int $wishlistId
     * @return array|null
     */
    public function getWishlist($wishlistId)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        
        // Get the wishlist item collection for the customer, filtered by wishlist_id
        $wishlistCollection = $this->wishlistItemCollectionFactory->create()
            ->addFieldToFilter('wishlist_id', $wishlistId);

        // Initialize an empty array to hold product IDs
        $productIds = [];

        // If the collection contains items, loop through and fetch the product IDs
        if ($wishlistCollection->getSize() > 0) {
            foreach ($wishlistCollection as $item) {
                $productIds[] = $item->getProductId();  // Add product ID to the array
            }
            return $productIds;  // Return the array of product IDs
        }

        return null;  // No wishlist items found
    }

    /**
     * Get product data by product ID
     *
     * @param int $productId
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProductData($productId)
    {
        try {
            // Fetch the product using the product repository by ID
            $product = $this->_productRepository->getById($productId);
            return $product; // Return the product object
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Handle case where the product is not found (e.g., invalid product ID)
            return null;
        }
    }
    public function getCustomerData()
    {
        if ($this->customerSession->isLoggedIn()) {
            // Get the customer model from the session
            return $this->customerSession->getCustomer();
    }}
    public function getProductQty($productId)
    {
        $wishlistCollection = $this->wishlistItemCollectionFactory->create()
            ->addFieldToFilter('product_id', $productId)->getFirstItem();
            return $wishlistCollection->getQty();}

}
