<?php

namespace Egits\WishlistGroups\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Egits\WishlistGroups\Model\ResourceModel\Wishlist\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Egits\WishlistGroups\Model\Wishlist as EgitsWishlist;
use Egits\WishlistGroups\Model\WishlistItem as EgitsWishlistItem;
use Egits\WishlistGroups\Model\ResourceModel\Wishlist as WishlistResourceModel;
use Egits\WishlistGroups\Model\ResourceModel\WishlistItem as WishlistItemResourceModel;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Save
 * @package Egits\WishlistGroups\Controller\Index
 */
class Save implements ActionInterface
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
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EgitsWishlist
     */
    protected $egitsWishlist;

    /**
     * @var WishlistItem
     */
    protected $egitsWishlistItem;

    /**
     * @var WishlistResourceModel
     */
    protected $wishlistResourceModel;

    /**
     * @var WishlistItemResourceModel
     */
    protected $wishlistItemResourceModel;

    /**
     *  @var ManagerInterface 
     */
    protected $messageManager;

    public function __construct(
        JsonFactory $jsonFactory,
        Session $customerSession,
        WishlistFactory $wishlistFactory,
        ProductRepository $productRepository,
        RequestInterface $request,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        EgitsWishlist $egitsWishlist,
        EgitsWishlistItem $egitsWishlistItem,
        WishlistResourceModel $wishlistResourceModel,
        WishlistItemResourceModel $wishlistItemResourceModel,
        ManagerInterface $messageManager
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
        $this->wishlistFactory = $wishlistFactory;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->egitsWishlist = $egitsWishlist;
        $this->egitsWishlistItem = $egitsWishlistItem;
        $this->wishlistResourceModel = $wishlistResourceModel; 
        $this->wishlistItemResourceModel = $wishlistItemResourceModel; 
        $this->messageManager = $messageManager;
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
            $wishlistName = $this->request->getParam('name');
            $productId = $this->request->getParam('product');

            $this->logger->info("Received Data: Wishlist Name - " . $wishlistName . ", Product ID - " . $productId);

            if (!$wishlistName) {
                throw new LocalizedException(__('Wishlist name is required.'));
            }
            if (!$productId) {
                throw new LocalizedException(__('Product ID is required.'));
            }

            // Get the wishlist count
            $wishlistCollection = $this->collectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId);
            $wishlistCountInDb = $wishlistCollection->getSize();
            $wishlistCount = $this->scopeConfig->getValue(
                'wishlist_groups/wishlist/wishlist_count',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            );

            if ($wishlistCount < $wishlistCountInDb) {
                $this->messageManager->addErrorMessage(__("The number of wishlist that can be created is limited to  " . $wishlistCount . " wishlists. If you want to create a new one, please delete an existing one and try again."));
                return $result->setData([
                    'success' => false,
                    'message' => __("You already created " . $wishlistCountInDb . " wishlists. If you want to create a new one, please delete an existing one and try again.")
                ]);
            }

            // Create the Wishlist and save it using the resource model
            $this->egitsWishlist->setCustomerId($customerId)
                ->setName($wishlistName);
            $this->wishlistResourceModel->save($this->egitsWishlist);

            $wishlistId = $this->egitsWishlist->getId();
            $this->logger->info("Wishlist id: " . $wishlistId);
            
            $product = $this->productRepository->getById($productId);

            if (!$product->getId()) {
                throw new LocalizedException(__('Invalid product.'));
            }

            // Create the Wishlist Item and save it using the resource model
            $this->egitsWishlistItem->setWishlistId($wishlistId)
                ->setProductId($productId)
                ->setQty(1);
            $this->wishlistItemResourceModel->save($this->egitsWishlistItem); 

            $this->messageManager->addSuccessMessage(__('Wishlist Created & Product Added Successfully.'));
            return $result->setData([
                'success' => true,
                'message' => __('Wishlist Created & Product Added Successfully.')
            ]);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to create wishlist'));
            $this->logger->error("Wishlist Error: " . $e->getMessage());
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
