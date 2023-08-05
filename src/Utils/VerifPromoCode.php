<?php

namespace App\Utils;

use App\Entity\PromotionCode;
use Doctrine\ORM\EntityManagerInterface;

class VerifPromoCode
{

    const ERROR_INVALID_CODE = 1;
    const ERROR_PROMOTION_NOT_ACTIVE = 2;
    const ERROR_PROMOTION_EXPIRED = 3;
    const SUCCESS_PROMOTION_VALID = 0;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validatePromotionCode(string $code): string
    {
       // Vérification des caractères alphanumériques
        if (!preg_match('/^[a-zA-Z0-9]+$/', $code)) {
            return self::ERROR_INVALID_CODE;
        }

        // Recherche du code de promotion dans la base de données
        $promotionCode = $this->entityManager->getRepository(PromotionCode::class)->findOneBy(['code' => $code]);

        if (!$promotionCode) {
            return self::ERROR_INVALID_CODE;
        }

        $currentDateTime = new \DateTime();

        // Vérification de la période de validité
        if ($currentDateTime < $promotionCode->getStartDate()) {
            return self::ERROR_PROMOTION_NOT_ACTIVE;
        }

        if ($currentDateTime > $promotionCode->getEndDate()) {
            return self::ERROR_PROMOTION_EXPIRED;
        }

        // Le code est valide
        return self::SUCCESS_PROMOTION_VALID;
    }

    
}





