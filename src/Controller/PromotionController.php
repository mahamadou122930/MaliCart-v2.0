<?php

namespace App\Controller;

use App\Entity\PromotionCode;
use App\Form\CodePromoType;
use App\Utils\PromotionCal;
use App\Utils\VerifPromoCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PromotionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/promotion', name: 'app_promotion')]
    public function index(VerifPromoCode $verifPromoCode, Request $request, PromotionCal $calculpromo): Response
    {
        $form = $this->createForm(CodePromoType::class);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $code = $form->getData()->getCode();

                $verificationResult = $verifPromoCode->validatePromotionCode($code);

                if ($verificationResult === VerifPromoCode::SUCCESS_PROMOTION_VALID) {
                    // Le code promo est valide
                    $totalAmount = 100; // Exemple : à remplacer par le montant réel de la commande
                    $promoCodeEntity = $this->entityManager->getRepository(PromotionCode::class)->findOneby(['code'=>$code]);
                    
                    if ($promoCodeEntity) {
                        $reduction = $promoCodeEntity->getReduction();

                        // Calcul de la réduction
                        $reductionAmount = $calculpromo->calculateReductionAmount($totalAmount, $reduction);

                        // Affichage du montant de réduction à l'utilisateur
                        $this->addFlash('success', 'Le code promotionnel est valide. Réduction : '.$reductionAmount.'€');

                        return $this->redirectToRoute('app_success');
                    } else {
                        $this->addFlash('error', 'Le code de promotion est introuvable.');
                    }
                } else {
                    $errorMessage = $this->getErrorMessage($verificationResult);
                    $this->addFlash('error', $errorMessage);
                }
            }  
        
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la validation du code promotionnel.');
        }

        return $this->render('promotion/index.html.twig', [
            'form' => $form->createView()
        ]);

    }  
    
    private function getErrorMessage(int $verificationResult): string
    {
        $errorMessage = '';

        switch ($verificationResult) {
            case VerifPromoCode::ERROR_INVALID_CODE:
                $errorMessage = 'Le code de promotion est invalide.';
                break;
            case VerifPromoCode::ERROR_PROMOTION_NOT_ACTIVE:
                $errorMessage = 'Le code de promotion saisi est inactif.';
                break;
            case VerifPromoCode::ERROR_PROMOTION_EXPIRED:
                $errorMessage = 'Le code de promotion a expiré.';
                break;
            default:
                $errorMessage = 'Une erreur s\'est produite lors de la validation du code promotionnel.';
        }

        return $errorMessage;
    }
}
