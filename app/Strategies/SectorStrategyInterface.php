<?php

namespace App\Strategies;

/**
 * SectorStrategyInterface — Contract defining terms, categories and rules for each industry sector (LSP & OCP).
 */
interface SectorStrategyInterface
{
    /** Return localized vocabulary mapping for SaaS interface customization. */
    public function getTerminology(): array;

    /** Return sector specific priority weight modification rules. */
    public function getPriorityRules(): array;

    /** Return default resource category definitions to seed or initialize catalog lists. */
    public function getDefaultCategories(): array;
}
