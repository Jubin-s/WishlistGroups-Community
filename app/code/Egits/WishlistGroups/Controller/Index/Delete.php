<?php

namespace Egits\WishlistGroups\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultInterface;
use Egits\WishlistGroups\Model\ResourceModel\WishlistItem\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;


/**
 * Class Delete
 * @package Egits\WishlistGroups\Controller\Index
 */
class Delete implements ActionInterface
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
     * @var WishlistItemCollectionFactory
     */
    protected $wishlistItemCollectionFactory;
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Delete constructor.
     *
     * @param JsonFactory $jsonFactory
     * @param Session $customerSession
     * @param WishlistItemCollectionFactory $wishlistItemCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Session $customerSession,
        WishlistItemCollectionFactory $wishlistItemCollectionFactory,
        RequestInterface $request
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->request = $request;
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
                'message' => __('You must be logged in to delete items from the wishlist.')
            ]);
        }

        try {
            $wishlistId = $this->request->getParam('wishlistId');
            $customerId = $this->customerSession->getCustomerId();
            $wishlistItemCollection = $this->wishlistItemCollectionFactory->create()
                ->addFieldToFilter('wishlist_id', $wishlistId);

            if ($wishlistItemCollection->getSize() == 0) {
                return $result->setData([
                    'success' => false,
                    'message' => __('No items found for the provided wishlist ID.')
                ]);
            }

            // Delete the wishlist items
            foreach ($wishlistItemCollection as $item) {
                $item->delete();
            }

            return $result->setData([
                'success' => true,
                'message' => __('Wishlist items deleted successfully.')
            ]);

        } catch (LocalizedException $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => __('An error occurred while deleting the wishlist items.' . $e->getMessage())
            ]);
        }
    }
}
