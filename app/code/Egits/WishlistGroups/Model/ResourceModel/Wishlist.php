<?php

namespace Egits\WishlistGroups\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Wishlist
 *
 */
class Wishlist extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('egits_wishlist_names', 'egits_wishlist_id');
    }
}
