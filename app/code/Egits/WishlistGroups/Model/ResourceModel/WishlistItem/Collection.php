<?php

namespace Egits\WishlistGroups\Model\ResourceModel\WishlistItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Egits\WishlistGroups\Model\ResourceModel\WishlistItem
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Egits\WishlistGroups\Model\WishlistItem', 'Egits\WishlistGroups\Model\ResourceModel\WishlistItem');
    }
}
