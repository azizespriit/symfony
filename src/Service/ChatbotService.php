<?php

namespace App\Service;

class ChatbotService
{
    private array $knowledgeBase = [
        'evenements' => [
            'keywords' => ['événement', 'evenement', 'évènement', 'event', 'événements', 'evenements'],
            'questions' => [
                ".*créer.*événement" => "Pour créer un événement :\n1. Allez dans la section 'Événements'\n2. Cliquez sur 'Créer un événement'\n3. Remplissez les détails (nom, date, lieu, description)\n4. Sauvegardez\nLes événements peuvent être des compétitions, tournois ou rencontres sportives.",
                ".*types.*événements" => "Nous gérons plusieurs types d'événements :\n- Compétitions officielles\n- Tournois amicaux\n- Entraînements collectifs\n- Événements spéciaux\nChaque type a ses propres règles et paramètres.",
                ".*modifier.*événement" => "Pour modifier un événement :\n1. Trouvez l'événement dans la liste\n2. Cliquez sur 'Modifier'\n3. Changez les informations nécessaires\n4. Sauvegardez\nAttention : certaines modifications peuvent affecter les réservations existantes.",
                ".*supprimer.*événement" => "Pour supprimer un événement :\n1. Sélectionnez l'événement\n2. Cliquez sur 'Supprimer'\n3. Confirmez\nAttention : Cela supprimera aussi toutes les réservations associées à cet événement."
            ],
            'advices' => [
                "Planifiez vos événements au moins 2 semaines à l'avance pour une meilleure organisation 📅",
                "Utilisez les descriptions détaillées pour informer les participants des règles spécifiques 📝",
                "Vérifiez les conflits de dates avant de créer un nouvel événement ⚠️",
                "Différenciez bien les événements publics (ouverts à tous) et privés (sur invitation) 🔒",
                "Pensez à archiver les anciens événements pour garder une trace tout en nettoyant l'interface 🗄️"
            ]
        ],
        'competitions' => [
            'keywords' => ['compétition', 'competition', 'tournoi', 'championnat', 'match', 'concours'],
            'questions' => [
                ".*différence.*compétition.*événement" => "Une compétition est un type spécifique d'événement avec :\n- Un système de classement\n- Des règles strictes\n- Des prix/récompenses\n- Souvent plusieurs matches/épreuves\nToutes les compétitions sont des événements, mais pas l'inverse.",
                ".*créer.*compétition" => "Pour créer une compétition :\n1. Allez dans 'Compétitions'\n2. Cliquez sur 'Nouvelle compétition'\n3. Remplissez les détails (type, format, règles)\n4. Ajoutez les équipes/participants\n5. Planifiez les matches\n6. Publiez la compétition",
                ".*types.*compétitions" => "Types de compétitions disponibles :\n- Tournois à élimination directe\n- Championnats par poules\n- Compétitions individuelles\n- Mixte (équipes et individus)\n- Par catégories (âge, niveau)",
                ".*inscrire.*compétition" => "Pour s'inscrire à une compétition :\n1. Trouvez la compétition dans la liste\n2. Vérifiez les critères d'éligibilité\n3. Cliquez sur 'S'inscrire'\n4. Payez si nécessaire\n5. Recevez confirmation\nLes inscriptions peuvent être individuelles ou par équipe."
            ],
            'advices' => [
                "Prévoyez toujours des dates de report en cas d'annulation pour les compétitions en extérieur ☔",
                "Limitez le nombre de participants selon la capacité de vos installations 🚧",
                "Communiquez clairement le format et les règles avant le début de la compétition 📢",
                "Prévoyez un système de réserves ou remplaçants pour les compétitions par équipe 🔄",
                "Enregistrez tous les résultats en temps réel pour maintenir le classement à jour ⏱️"
            ]
        ],
        'reservations' => [
            'keywords' => ['réservation', 'reservation', 'inscription', 'booking', 'réserver', 'reserver', 'participer'],
            'questions' => [
                ".*faire.*réservation" => "Pour faire une réservation :\n1. Trouvez l'événement/compétition\n2. Vérifiez les places disponibles\n3. Sélectionnez vos options (dates, créneaux)\n4. Confirmez les participants\n5. Payez si nécessaire\n6. Recevez votre confirmation par email",
                ".*annuler.*réservation" => "Pour annuler une réservation :\n1. Allez dans 'Mes réservations'\n2. Trouvez la réservation concernée\n3. Cliquez sur 'Annuler'\n4. Confirmez\nAttention : les annulations peuvent être soumises à des frais selon le délai.",
                ".*modifier.*réservation" => "Pour modifier une réservation :\n1. Trouvez la réservation\n2. Cliquez sur 'Modifier'\n3. Changez les détails nécessaires\n4. Sauvegardez\nLes modifications peuvent entraîner des ajustements de prix ou de disponibilité.",
                ".*liste.*réservations" => "Pour voir vos réservations :\n1. Allez dans 'Mes réservations'\n2. Filtrez par date/statut si besoin\n3. Cliquez sur une réservation pour les détails\nVous pouvez aussi exporter la liste en PDF ou Excel."
            ],
            'advices' => [
                "Réservez tôt pour les événements populaires afin de garantir votre place 🎟️",
                "Vérifiez toujours les emails de confirmation et les spams 📧",
                "Notez les numéros de confirmation pour référence rapide 🔢",
                "Annulez au moins 48h à l'avance pour éviter les frais d'annulation ⏳",
                "Utilisez la fonction 'Partager' pour informer les autres participants de vos réservations 📲"
            ]
        ]
    ];

    private array $genericResponses = [
        "Je peux vous aider avec les événements, compétitions et réservations. Sur quel point précis souhaitez-vous des informations ? 📅",
        "Pour une réponse optimale, précisez votre demande (ex: 'comment créer une compétition ?' ou 'annuler une réservation'). 🎯",
        "Votre question concerne-t-elle les événements, les compétitions ou les réservations ? Dites-m'en plus pour un conseil personnalisé. ✨",
        "Je dispose d'informations sur la gestion des événements sportifs, compétitions et système de réservation. Quelle thématique vous intéresse ? 🏆",
        "Pour vous guider au mieux, pourriez-vous formuler votre demande plus précisément ? Par exemple : 'modifier un événement' ou 's'inscrire à une compétition'. 📝"
    ];

    private array $greetings = [
        "Bonjour ! Je suis votre assistant pour la gestion des événements sportifs. Posez-moi vos questions sur les événements, compétitions ou réservations. 🏅",
        "Salut ! Prêt(e) à organiser votre prochain événement sportif ? Je peux vous conseiller sur les compétitions et réservations. ⚽",
        "Bienvenue ! Assistant événementiel sportif à votre service. Demandez-moi des informations sur les compétitions, réservations ou gestion d'événements. 🏀"
    ];

    private array $thanksResponses = [
        "Ravi d'avoir pu vous aider ! N'hésitez pas si vous avez d'autres questions sur la gestion des événements sportifs. 😊",
        "Merci à vous ! Pour aller plus loin, je peux vous fournir des guides détaillés sur l'organisation d'événements. Intéressé(e) ? 📚",
        "Avec plaisir ! Si vous souhaitez approfondir un sujet particulier, je suis à votre disposition. Bonne organisation ! ✨"
    ];

    public function getResponse(string $message): string
    {
        $message = strtolower(trim($message));

        // Réponses aux questions générales sur le système
        if (preg_match('/t\'?es qui/i', $message)) {
            return "Je suis votre assistant pour la gestion des événements sportifs. Mon rôle est de vous fournir des informations sur les compétitions, réservations et la gestion d'événements sportifs. Posez-moi toutes vos questions ! 🏆";
        }

        if (preg_match('/fonctionnalités|que peux-tu faire/i', $message)) {
            return "Je peux vous aider avec :\n- La création et gestion d'événements sportifs\n- L'organisation de compétitions et tournois\n- Le système de réservation et inscriptions\n- Les questions générales sur l'utilisation de la plateforme\nDites-moi ce qui vous intéresse ! 💡";
        }

        if ($this->isGreeting($message)) {
            return $this->greetings[array_rand($this->greetings)];
        }

        if ($this->isThanks($message)) {
            return $this->thanksResponses[array_rand($this->thanksResponses)];
        }

        $response = $this->answerPreciseQuestion($message);
        if ($response !== null) {
            return $response;
        }

        $detectedThemes = $this->detectThemes($message);

        if (!empty($detectedThemes)) {
            return $this->generateThemeResponse($detectedThemes, $message);
        }

        return $this->genericResponses[array_rand($this->genericResponses)];
    }

    private function isGreeting(string $message): bool
    {
        return preg_match('/\b(bonjour|salut|coucou|hello|hey|bonsoir|hi)\b/', $message);
    }

    private function isThanks(string $message): bool
    {
        return preg_match('/\b(merci|merci beaucoup|super|génial|parfait|thanks|thank you)\b/', $message);
    }

    private function answerPreciseQuestion(string $question): ?string
    {
        $bestAnswer = null;
        $bestScore = 0;

        foreach ($this->knowledgeBase as $theme => $data) {
            foreach ($data['questions'] as $pattern => $answer) {
                if (preg_match("/" . $pattern . "/i", $question)) {
                    $similarity = 0;
                    similar_text($question, $pattern, $similarity);
                   
                    if ($similarity > $bestScore) {
                        $bestScore = $similarity;
                        $bestAnswer = $answer;
                    }
                }
            }
        }

        if ($bestScore > 40) {
            return $bestAnswer;
        }

        return null;
    }

    private function detectThemes(string $message): array
    {
        $themes = [];

        foreach ($this->knowledgeBase as $theme => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (preg_match("/\b" . preg_quote($keyword, '/') . "\b/i", $message)) {
                    $themes[] = $theme;
                    break;
                }
            }
        }

        return array_unique($themes);
    }

    private function generateThemeResponse(array $themes, string $message): string
    {
        if (count($themes) > 1) {
            $response = "Votre message touche à plusieurs thèmes : " . implode(", ", $themes) . ". Voici des informations clés pour chaque :\n\n";
           
            foreach ($themes as $theme) {
                $response .= "**" . ucfirst($theme) . "**\n";
                $randomAdvices = array_rand($this->knowledgeBase[$theme]['advices'], min(2, count($this->knowledgeBase[$theme]['advices'])));
                foreach ((array)$randomAdvices as $index) {
                    $response .= "• " . $this->knowledgeBase[$theme]['advices'][$index] . "\n";
                }
                $response .= "\n";
            }
           
            return $response . "Pour des informations plus précises, formulez votre demande sur un thème à la fois. 🔍";
        }

        $theme = $themes[0];
        $advices = $this->knowledgeBase[$theme]['advices'];
        shuffle($advices);
        $selectedAdvices = array_slice($advices, 0, 3);

        $response = "Sur le thème **" . $theme . "**, voici des informations importantes :\n\n";
        foreach ($selectedAdvices as $advice) {
            $response .= "• " . $advice . "\n";
        }

        $sampleQuestions = [];
        foreach ($this->knowledgeBase[$theme]['questions'] as $q => $a) {
            $cleanQ = preg_replace('/\(.*?\)/', '', $q);
            $sampleQuestions[] = str_replace(['.*', '.+'], ['...', '...'], $cleanQ);
        }
        shuffle($sampleQuestions);
        $sampleQuestions = array_slice($sampleQuestions, 0, 2);

        $response .= "\nExemples de questions que vous pourriez poser :\n";
        foreach ($sampleQuestions as $sample) {
            $response .= "- \"" . ucfirst(trim($sample)) . "\"\n";
        }

        return $response;
    }
}