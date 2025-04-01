<?php
namespace Egits\WishlistGroups\Model;

use Egits\WishlistGroups\Api\Data\WishlistItemInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class WishlistItem
 * @package Egits\WishlistGroups\Model
 */
class WishlistItem extends AbstractModel implements WishlistItemInterface, IdentityInterface
{

    /**
     * cache tag
     */
    const CACHE_TAG = 'egitswishlist_wishlistitem';

    /**
     * @var string
     */
    protected $_cacheTag = 'egitswishlist_wishlistitem';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'egitswishlist_wishlistitem';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Egits\WishlistGroups\Model\ResourceModel\WishlistItem');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get wishlist_id
     *
     * return int
     */
    public function getEgitsWishlistItemId()
    {
        return $this->getData(self::WISHLIST_ITEM_ID);
    }

      /**
     * Get wishlist_id
     *
     * return int
     */
    public function getWishlistId()
    {
        return $this->getData(self::WISHLIST_ID);
    }

    /**
     * Get product_id
     *
     * return string
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

     /**
     * Get qty
     *
     * return string
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    public function setEgitsWishlistItemId($wishlistItemId)
    {
        return $this->setData(self::WISHLIST_ITEM_ID, $wishlistItemId);
    }

    public function setWishlistId($wishlistId)
    {
        return $this->setData(self::WISHLIST_ID, $wishlistId);
    }
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID,$productId);
    }
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

}
