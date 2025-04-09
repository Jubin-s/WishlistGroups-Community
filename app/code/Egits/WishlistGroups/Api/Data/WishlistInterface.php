<?php

namespace Egits\WishlistGroups\Api\Data;
/**
 * Interface AttachmentInterface
 */
interface WishlistInterface
{

    CONST WISHLIST_ID = 'egits_wishlist_id';
    CONST NAME = 'name';
    CONST CUSTOMER_ID = 'customer_id';


    /**
     * @return mixed
     */
    public function getEgitsWishlistId();

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getCustomerId();

    /**
     * @param $wishlistId
     * @return mixed
     */
    public function setEgitsWishlistId($wishlistId);

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name);

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

}
