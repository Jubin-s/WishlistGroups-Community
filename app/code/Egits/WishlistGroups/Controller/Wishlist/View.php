<?php

namespace Egits\WishlistGroups\Controller\Wishlist;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class View implements ActionInterface
{
    protected $wishlistFactory;
    protected $customerSession;
    protected $resultPageFactory;
    protected $request;

    /**
     * Constructor
     *
     * @param Context $context
     * @param WishlistFactory $wishlistFactory
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        WishlistFactory $wishlistFactory,
        Session $customerSession,
        PageFactory $resultPageFactory,
        RequestInterface $request
    )
    {
        $this->wishlistFactory = $wishlistFactory;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
    }

    /**
     * Execute the controller action
     */
    public function execute()
    {
        // Get the wishlist ID from the URL
        $wishlistId = (int)$this->request->getParam('wishlist_id');

        // Ensure the customer is logged in
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }

        // Retrieve the wishlist by its ID and customer
        $customerId = $this->customerSession->getCustomer()->getId();
        $wishlist = $this->wishlistFactory->create()->load($wishlistId);

        $page = $this->resultPageFactory->create();
        $block = $page->getLayout()->getBlock('wishlistgroups');
        $block->setData('wishlist_id', $wishlistId);
        return $page;
    }
}
