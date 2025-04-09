<?php

namespace Egits\WishlistGroups\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultInterface;
use Egits\WishlistGroups\Model\WishlistFactory; // WishlistFactory to create model
use Egits\WishlistGroups\Model\ResourceModel\Wishlist as WishlistResource; // ResourceModel for deleting
use Egits\WishlistGroups\Model\ResourceModel\WishlistItem\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;

/**
 * Class Deletewishlist
 * @package Egits\WishlistGroups\Controller\Index
 */
class Deletewishlist implements ActionInterface
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
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var WishlistResource
     */
    protected $wishlistResource;

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
     * @param WishlistFactory $wishlistFactory
     * @param WishlistResource $wishlistResource
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Session $customerSession,
        WishlistItemCollectionFactory $wishlistItemCollectionFactory,
        WishlistFactory $wishlistFactory,
        WishlistResource $wishlistResource,
        RequestInterface $request
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistResource = $wishlistResource;
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
            $wishlistData = $this->wishlistFactory->create()
                ->load($wishlistId); // Load the wishlist by its ID

            if (!$wishlistData->getId() || $wishlistData->getCustomerId() != $customerId) {
                return $result->setData([
                    'success' => false,
                    'message' => __('The wishlist does not exist or does not belong to the current customer.')
                ]);
            }

            // Delete the wishlist items
            $wishlistItemCollection = $this->wishlistItemCollectionFactory->create()
                ->addFieldToFilter('wishlist_id', $wishlistId);

            foreach ($wishlistItemCollection as $item) {
                $item->delete(); // Delete each item
            }

            // Now delete the wishlist using the ResourceModel
            $this->wishlistResource->delete($wishlistData);

            return $result->setData([
                'success' => true,
                'message' => __('Wishlist and its items deleted successfully.')
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
