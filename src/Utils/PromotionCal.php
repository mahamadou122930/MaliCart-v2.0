<?php

namespace App\Utils;


class PromotionCal
{
    public function calculateReductionAmount(float $totalAmount, float $percentage): float
    {
        // Calculer le montant de réduction en fonction du montant total et du pourcentage de réduction
        $reductionAmount = $totalAmount * ($percentage / 100);

        return $reductionAmount;
    }

}