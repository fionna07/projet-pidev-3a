<?php

namespace App\Service;

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;
use Phpml\Math\Distance\Euclidean;

class MatchingService
{
    public function calculateSimilarity(string $skill1, string $skill2): float
    {
        // Normaliser les compétences
        $skill1 = $this->normalizeSkill($skill1);
        $skill2 = $this->normalizeSkill($skill2);

        // Vérifier si un mot clé est commun (ex: "récolte des olives" == "récolte olive")
        if ($this->hasCommonWord($skill1, $skill2)) {
            return 1.0; // Si un mot commun est trouvé, on considère qu'ils sont identiques
        }

        // Tokenizer + Vectorizer
        $tokenizer = new WhitespaceTokenizer();
        $vectorizer = new TokenCountVectorizer($tokenizer);
        $skills = [$skill1, $skill2]; 
    
        $vectorizer->fit($skills);
        $vectorizer->transform($skills);
    
        if (!is_array($skills[0]) || !is_array($skills[1])) {
            return 0.0;
        }
    
        $vector1 = $skills[0];
        $vector2 = $skills[1];
    
        if (count($vector1) !== count($vector2)) {
            return 0.0;
        }

        $euclidean = new Euclidean();
        $distance = $euclidean->distance($vector1, $vector2);

        return 1 / (1 + $distance);
    }

    public function calculateMatchPercentage(string $requiredSkillsStr, string $candidateSkillsStr): float
    {
        $requiredSkills = array_filter(array_map([$this, 'normalizeSkill'], explode(',', $requiredSkillsStr)));
        $candidateSkills = array_filter(array_map([$this, 'normalizeSkill'], explode(',', $candidateSkillsStr)));

        if (empty($requiredSkills)) {
            return 0.0;
        }

        $matchingSkills = 0;

        foreach ($requiredSkills as $requiredSkill) {
            foreach ($candidateSkills as $candidateSkill) {
                $similarity = $this->calculateSimilarity($requiredSkill, $candidateSkill);
                if ($similarity >= 0.7) {
                    $matchingSkills++;
                    break;
                }
            }
        }

        return ($matchingSkills / count($requiredSkills)) * 100;
    }

    private function normalizeSkill(string $skill): string
    {
        // Convertir en minuscules et supprimer espaces
        $skill = mb_strtolower(trim($skill)); 

        // Supprimer les petits mots inutiles
        $stopWords = ['des', 'de', 'du', 'la', 'le', 'les', 'un', 'une', 'et', 'avec'];
        $skill = preg_replace('/\b(' . implode('|', $stopWords) . ')\b/', '', $skill);

        // Supprimer le "s" final
        return preg_replace('/s$/', '', trim($skill)); 
    }

    private function hasCommonWord(string $skill1, string $skill2): bool
    {
        $words1 = explode(' ', $skill1);
        $words2 = explode(' ', $skill2);

        return (bool) array_intersect($words1, $words2);
    }
}
