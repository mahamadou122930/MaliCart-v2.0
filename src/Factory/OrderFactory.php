<?php

namespace App\Factory;


use App\Entity\ShopProduct;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class OrderFactory
 */
class OrderFactory
{

    public function createItem(array $cart, ShopProduct $shopProduct, Request $request): array
    {
        // Loop through the cart to find a matching item
        foreach ($cart as $index => $cartItem) {
            if ($this->isCartItemMatching($cartItem, $shopProduct, $request)) {
                // If a matching item is found, simply increment its quantity
                $cart[$index]['quantity']++;
                return $cart;
            }
        }

        // If no matching item is found, add a new item to the cart
        $newItem = [
            'product_id' => $shopProduct->getId(),
            'color_id' => $request->request->get('color'),
            'size' => $request->request->get('size'),
            'quantity' => 1,
        ];

        $cart[] = $newItem;
        return $cart;
    }


    private function isCartItemMatching(array $cartItem, ShopProduct $shopProduct, Request $request): bool
    {
        return (
            $cartItem['product_id'] === $shopProduct->getId() &&
            $cartItem['color_id'] === $request->request->get('color') &&
            $cartItem['size'] === $request->request->get('size')
        );
    }

}