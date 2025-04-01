<?php

namespace Egits\WishlistGroups\Controller\Wishlist;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    protected $wishlistFactory;
    protected $customerSession;
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param WishlistFactory $wishlistFactory
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        WishlistFactory $wishlistFactory,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute the controller action
     */
    public function execute()
    {
        // Get the wishlist ID from the URL
        $wishlistId = (int) $this->getRequest()->getParam('wishlist_id');
        
        // Ensure the customer is logged in
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }

        // Retrieve the wishlist by its ID and customer
        $customerId = $this->customerSession->getCustomer()->getId();
        $wishlist = $this->wishlistFactory->create()->load($wishlistId);

        // Check if the wishlist exists and belongs to the logged-in customer
        // if ($wishlist->getCustomerId() != $customerId) {
        //     $this->_redirect('wishlist');
        //     return;
        // }

        // Pass the wishlist to the layout
        // $this->_view->getLayout()->getBlock('wishlist.view')->setWishlist($wishlist);

        // Return the result page
        // return $this->resultPageFactory->create();
        $page = $this->resultPageFactory->create();
        $block = $page->getLayout()->getBlock('wishlistgroups');
                $block->setData('wishlist_id', $wishlistId);
                return $page;
    }
}
