<?php

namespace Egits\WishlistGroups\Model\ResourceModel\Wishlist;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Egits\WishlistGroups\Model\ResourceModel\Wishlist
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Egits\WishlistGroups\Model\Wishlist', 'Egits\WishlistGroups\Model\ResourceModel\Wishlist');
    }
}
