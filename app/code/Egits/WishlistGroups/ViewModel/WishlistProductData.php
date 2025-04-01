<?php

namespace Egits\WishlistGroups\ViewModel;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Egits\WishlistGroups\Model\WishlistItemFactory;
use Egits\WishlistGroups\Model\ResourceModel\Wishlist\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class WishlistProductData
 *
 */
class WishlistProductData implements ArgumentInterface
{
    private Session $customerSession;
    private CustomerRepositoryInterface $customerRepository;
    private LoggerInterface $logger;
    private WishlistItemFactory $wishlistItemFactory;
    private CollectionFactory $wishlistFactory;
    /**
     * CustomerStatus constructor.
     *
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param WishlistItemFactory $wishlistItemFactory
     * @param CollectionFactory $wishlistFactory
     */
    public function __construct(
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        WishlistItemFactory $wishlistItemFactory,
        CollectionFactory $wishlistFactory
    )
    {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->wishlistFactory = $wishlistFactory;
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return Customer
     */
    public function getCustomerDetails()
    {
        return $this->customerSession->getCustomer();
    }
    public function getWishlistGroups()
    {
        // Get the customer ID
        $customerId = $this->getCustomerDetails()->getId();
        
        // Get the wishlist collection for the current customer
        $wishlistCollection = $this->wishlistFactory->create()->addFieldToFilter('customer_id', $customerId);
        
        // Check if there are any items in the collection
        if ($wishlistCollection->getSize() > 0) {
            // Create an array to store the wishlist data (ID and name)
            $wishlistGroups = [];
    
            foreach ($wishlistCollection as $wishlistItem) {
                // Add both the ID and name of the wishlist
                $wishlistGroups[] = [
                    'id' => $wishlistItem->getId(),
                    'name' => $wishlistItem->getName()
                ];
            }
    
            // Return the wishlist groups as an array
            return $wishlistGroups;
        }
    
        // Return null if no wishlists found
        return null;
    }
    


   

}
