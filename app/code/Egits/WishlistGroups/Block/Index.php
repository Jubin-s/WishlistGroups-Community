<?php

namespace Egits\WishlistGroups\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Egits\WishlistGroups\Model\ResourceModel\WishlistItem\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\View\Element\Template\Context;
use Egits\WishlistGroups\Model\WishlistItem;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Index
 * @package Egits\WishlistGroups\Block
 */
class Index extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $wishlistItemCollectionFactory;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var ProductRepository
     */
    protected $_productRepository;
    /**
     * @var WishlistItem
     */
    protected $wishlistItem;
    /**
     * @var ProductRepositoryInterface
     */
    private $productrepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CollectionFactory $wishlistItemCollectionFactory
     * @param Session $customerSession
     * @param ProductRepository $productRepository
     * @param WishlistItem $wishlistItem
     * @param ProductRepositoryInterface $productrepository
     * @param StoreManagerInterface $storemanager
     */
    public function __construct(
        Context $context,
        CollectionFactory $wishlistItemCollectionFactory,
        Session $customerSession,
        ProductRepository $productRepository,
        WishlistItem $wishlistItem,
        ProductRepositoryInterface $productrepository,
        StoreManagerInterface $storemanager

    )
    {
        $this->wishlistItemCollectionFactory = $wishlistItemCollectionFactory;
        $this->customerSession = $customerSession;
        $this->_productRepository = $productRepository;
        $this->wishlistItem = $wishlistItem;
        $this->productrepository = $productrepository;
        $this->_storeManager = $storemanager;
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
        $wishlistCollection = $this->wishlistItemCollectionFactory->create()
            ->addFieldToFilter('wishlist_id', $wishlistId);

        $productIds = [];

        // If the collection contains items, loop through and fetch the product IDs
        if ($wishlistCollection->getSize() > 0) {
            foreach ($wishlistCollection as $item) {
                $productIds[] = $item->getProductId();
            }
            return $productIds;
        }

        return null;
    }

    /**
     * Get product data by product ID
     *
     * @param int $productId
     * @return Product|null
     */
    public function getProductData($productId)
    {
        try {
            $product = $this->_productRepository->getById($productId);
            return $product;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @return Customer
     */
    public function getCustomerData()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer();
        }
    }

    /**
     * @param $wishlistId
     * @return mixed
     */
    public function getProductQty($wishlistId,$productId)
    {
        $wishlistCollection = $this->wishlistItemCollectionFactory->create()
            ->addFieldToFilter('wishlist_id', $wishlistId)
            ->addFieldToFilter('product_id', $productId)
            ->getFirstItem();
        return $wishlistCollection->getQty();
    }

    /**
     * @param $productId
     * @return string
     * @throws NoSuchEntityException
     */
    Public function getProductImageUsingCode($productId)
    {
        $store = $this->_storeManager->getStore();
        $product = $this->productrepository->getById($productId);
        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
    }

}
