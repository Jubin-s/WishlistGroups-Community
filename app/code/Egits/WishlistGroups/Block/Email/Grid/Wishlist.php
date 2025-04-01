<?php

namespace Egits\WishlistGroups\Block\Email\Grid;

use Magento\Framework\View\Element\Template;

/**
 * Class Wishlist
 */
class Wishlist extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Egits_WishlistGroups::email/grid/wishlist.phtml';

    private $formData = [];


    /**
     * function to set form data.
     *
     * @param array $formData
     */
    public function setEmailFormData($formData)
    {    
        if(empty($formData)){
            $this->formData = [];
        }
        else
        {
            $this->formData = $formData;
        };
    }

    /**
     * function to get form data.
     *
     * @return array
     */
    public function getEmailFormData()
    {
        return  $this->formData;
    }
}
