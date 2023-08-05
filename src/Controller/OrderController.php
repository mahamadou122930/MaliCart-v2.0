<?php

namespace App\Controller;

use App\Entity\Carrier;
use App\Entity\Order;
use App\Form\OrderCarrierType;
use App\Form\OrderType;
use App\Manager\CartManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande', name: 'order')]
    public function index(CartManager $cartManager, Request $request): Response
    {
        $cart = $cartManager->getCurrentCart();
        $form = $this->createForm(OrderType::class, null, [
            'user'=> $this->getUser()
        ]);

        $form->handleRequest($request);
        

       if (!$this->getUser()->getAddresses()->getValues())
       {
            return $this->redirectToRoute('account_address_add');
       }

        if ($form->isSubmitted() && $form->isValid()) {
                $delivery = $form->get('addresses')->getData();
                $delivery_content = $delivery->getFirstName().' '.$delivery->getLastname();
                $delivery_content .='<br/>'.$delivery->getPhone();
                
                if (!$delivery->getCompany())                {
                    $delivery_content .= '<br/>'.$delivery->getCompany();
                }
                $delivery_content .='<br/>'.$delivery->getAddress();
                $delivery_content .='<br/>'.$delivery->getPostal().'_'.$delivery->getCity();
                $delivery_content .='<br/>'.$delivery->getCountry();
                $cart->setDelivery($delivery_content);
                $cart->setUser($this->getUser());
                $cart->setState(0);
            
            return $this->redirectToRoute('order_shipping');               

        }
        return $this->render('order/index.html.twig', [
            'form'=> $form->createView(),
            'cart'=> $cart
        ]);
    }

    #[Route('/commande/checkout-shipping', name: 'order_shipping')]
    public function shipping(CartManager $cartManager, Request $request): Response
    {
        
        $cart = $cartManager->getCurrentCart();

        // Crée le formulaire en utilisant le OrderCarrierType
        $form = $this->createForm(OrderCarrierType::class);
        $form->handleRequest($request);

        $carriers = $this->entityManager->getRepository(Carrier::class)->findAll();
        
    
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedCarrier = $form->get('carrier')->getData();
            
            $cart = $cartManager->getCurrentCart();
            $carrierName = $selectedCarrier->getName();
            $carrierPrice = $selectedCarrier->getPrice();
            $cart->setCarrierName($carrierName);
            $cart->setCarrierPrice($carrierPrice);
                           
        }
        
        $this->entityManager->flush();   
    
        
        return $this->render('order/order_shipping.html.twig', [
            'form' => $form->createView(),
            'carriers' => $carriers,
            'cart' => $cart

        ]);
    }
}
