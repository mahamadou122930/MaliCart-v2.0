<?php

namespace App\Manager;

use App\Entity\Order;
use App\Entity\ShopProduct;
use App\Factory\OrderFactory;
use App\Storage\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CartManager
{
    /**
     * @var CarSessionStorage
     */
    private $cartSessionStorage;

    /**
     * @var OrderFactory
     */
    private $cartFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CartManager constructor.
     */
    public function __construct(
        CartSessionStorage $cartStorage,
        OrderFactory $orderFactory,
        EntityManagerInterface $entityManager
    ){
     
        $this->cartSessionStorage = $cartStorage;
        $this->cartFactory = $orderFactory;
        $this->entityManager = $entityManager;
    }

     /**
     * Add a product to the cart.
     */
    public function addProductToCart(ShopProduct $shopProduct, Request $request): void
    {
        $cart = $this->cartSessionStorage->getCart();
        $cart = $this->cartFactory->createItem($cart, $shopProduct, $request);
        $this->cartSessionStorage->setCart($cart);
    }

    /**
     * Persists the cart in session
     */
    public function save(array $cart): void
    {
        $this->cartSessionStorage->setCart($cart);
    }

    
}   