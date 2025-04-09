<?php

namespace Egits\WishlistGroups\Api\Data;
/**
 * Interface AttachmentInterface
 */
interface WishlistItemInterface
{

    CONST WISHLIST_ITEM_ID = 'egits_wishlist_item_id';
    CONST WISHLIST_ID = 'wishlist_id';
    CONST PRODUCT_ID = 'product_id';
    CONST QTY = 'qty';


    /**
     * @return mixed
     */
    public function getEgitsWishlistItemId();

    /**
     * @return mixed
     */
    public function getWishlistId();

    /**
     * @return mixed
     */
    public function getProductId();

    /**
     * @return mixed
     */
    public function getQty();

    /**
     * @param $wishlistId
     * @return mixed
     */
    public function setEgitsWishlistItemId($wishlistItemId);

    /**
     * @param $wishlistId
     * @return mixed
     */
    public function setWishlistId($wishlistId);

    /**
     * @param $productId
     * @return mixed
     */
    public function setProductId($productId);

    /**
     * @param $qty
     * @return mixed
     */
    public function setQty($qty);

}
