<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SLA (Service Level Agreement) - Délais par priorité
    |--------------------------------------------------------------------------
    |
    | Définit les délais maximaux de résolution pour chaque niveau de priorité.
    | Les délais sont exprimés en heures.
    |
    */

    'deadlines' => [
        'critical' => 4,      // 4 heures
        'high' => 24,         // 24 heures (1 jour)
        'normal' => 48,       // 48 heures (2 jours)
        'low' => 168,         // 168 heures (7 jours)
    ],

    /*
    |--------------------------------------------------------------------------
    | Seuils d'alerte SLA
    |--------------------------------------------------------------------------
    |
    | Définit à quel pourcentage du temps restant déclencher les alertes.
    |
    */

    'alert_thresholds' => [
        'warning' => 80,      // Alerte à 80% du temps écoulé (badge orange)
        'critical' => 100,    // Critique à 100% du temps écoulé (badge rouge)
    ],

    /*
    |--------------------------------------------------------------------------
    | Emails d'alerte SLA
    |--------------------------------------------------------------------------
    |
    | Activer/désactiver les emails automatiques d'alerte.
    |
    */

    'email_alerts' => [
        'enabled' => env('SLA_EMAIL_ALERTS_ENABLED', true),
        'send_at_80_percent' => true,  // Envoyer email à 80%
        'send_at_100_percent' => true, // Envoyer email à 100% (dépassement)
    ],

    /*
    |--------------------------------------------------------------------------
    | Calcul du temps de travail
    |--------------------------------------------------------------------------
    |
    | Définit si le SLA se calcule en heures ouvrables ou en heures calendaires.
    |
    */

    'business_hours_only' => false, // false = 24/7, true = heures ouvrables seulement

    'business_hours' => [
        'start' => '08:00',
        'end' => '17:00',
    ],

    'working_days' => [1, 2, 3, 4, 5], // Lundi à Vendredi (1 = Lundi, 7 = Dimanche)

];
