<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * "My Wish List" link
 */

namespace Egits\WishlistGroups\Block\Wishlist;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Helper\Data;

/**
 * Class Link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Link extends \Magento\Framework\View\Element\Html\Link implements SortLinkInterface
{
    /**
     * Template name
     *
     * @var string
     */
    protected $_template = 'Egits_WishlistGroups::wishlist/link.phtml';

    /**
     * @var Data
     */
    protected $_wishlistHelper;

    /**
     * @param Context $context
     * @param Data $wishlistHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $wishlistHelper,
        array $data = []
    )
    {
        $this->_wishlistHelper = $wishlistHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_wishlistHelper->isAllow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('wishlist');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('My Wish List');
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
