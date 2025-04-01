<?php
namespace Egits\WishlistGroups\Model;

use Egits\WishlistGroups\Api\Data\WishlistInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Wishlist
 * @package Egits\WishlistGroups\Model
 */
class Wishlist extends AbstractModel implements WishlistInterface, IdentityInterface
{

    /**
     * cache tag
     */
    const CACHE_TAG = 'egitswishlist_wishlist';

    /**
     * @var string
     */
    protected $_cacheTag = 'egitswishlist_wishlist';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'egitswishlist_wishlist';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Egits\WishlistGroups\Model\ResourceModel\Wishlist');
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
    public function getEgitsWishlistId()
    {
        return $this->getData(self::WISHLIST_ID);
    }


    /**
     * Get Name
     *
     * return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function setEgitsWishlistId($wishlistId)
    {
        return $this->setData(self::WISHLIST_ID, $wishlistId);
    }

    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }


}
