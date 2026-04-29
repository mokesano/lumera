<?php
declare(strict_types=1);

/**
 * @defgroup pages_policies
 */
 
/**
 * @file pages/policies/index.php
 * 
 * Copyright (c) 2025 Wizdam Team
 * Copyright (c) 2025 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @ingroup pages_policies
 * @brief Handle requests for journal policies.
 */

switch ($op) {
    case 'index': 
    case 'privacy-statement':
    case 'peer-review':
    case 'ethics':
    case 'open-access':
    case 'archiving':
    case 'copyright':
    case 'publication-frequency':
    case 'section-policies':
    case 'view':
        define('HANDLER_CLASS', 'PoliciesHandler');
        import('pages.policies.PoliciesHandler');
        break;
    
    // [PENTING] Menangkap semua custom slug (retraction, publication-ethics, dll)
    default:
        define('HANDLER_CLASS', 'PoliciesHandler');
        import('pages.policies.PoliciesHandler');
        break;
}
?>