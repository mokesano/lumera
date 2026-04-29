<?php
declare(strict_types=1);

/**
 * @file tools/mergeUsers.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class mergeUsers
 * @ingroup tools
 *
 * @brief CLI tool for merging two OJS 2 user accounts.
 * [WIZDAM EDITION] Modernized CLI User Merge Tool.
 */

require(__DIR__ . '/bootstrap.inc.php');

class mergeUsers extends CommandLineTool {

    /** @var string The username to keep (all roles/content transferred to this user). */
    protected string $username1 = '';

    /** @var string The username to merge from (this user will be deleted). */
    protected string $username2 = '';

    /**
     * Constructor.
     * @param array $argv command-line arguments
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);

        if (!isset($this->argv[0]) || !isset($this->argv[1]) ) {
            $this->usage();
            exit(1);
        }

        $this->username1 = $this->argv[0];
        $this->username2 = $this->argv[1];
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function mergeUsers($argv = []) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Print command usage information.
     */
    public function usage(): void {
        echo "Wizdam User Merge Tool\n"
            . "Use this tool to merge two user accounts.\n\n"
            . "Usage: {$this->scriptName} [username1] [username2]\n"
            . "username1      The target user to keep (NEW USER).\n"
            . "username2      The source user to delete (OLD USER).\n"
            . "All roles and content associated with username2 will be transferred to username1.\n";
    }

    /**
     * Execute the merge users command.
     */
    public function execute(): void {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        /** @var User|null $oldUser */
        $oldUser = $userDao->getUserbyUsername($this->username2);

        /** @var User|null $newUser */
        $newUser = $userDao->getUserbyUsername($this->username1);

        $oldUserId = $oldUser?->getId();
        $newUserId = $newUser?->getId();

        if (empty($oldUserId)) {
            printf("Error: Source username '%s' is not a valid user.\n", $this->username2);
            exit(1);
        }

        if (empty($newUserId)) {
            printf("Error: Target username '%s' is not a valid user.\n", $this->username1);
            exit(1);
        }
        
        if ($oldUserId === $newUserId) {
            printf("Error: Source and target usernames must be different.\n");
            exit(1);
        }

        // Both user IDs are valid. Merge the accounts.
        import('classes.user.UserAction');
        // [WIZDAM] Casting IDs to int for strict safety, although DAO should handle it.
        UserAction::mergeUsers((int)$oldUserId, (int)$newUserId);

        printf("Merge completed: '%s' (ID %d) merged into '%s' (ID %d).\n",
            $this->username2,
            $oldUserId,
            $this->username1,
            $newUserId
        );
    }
}

// [WIZDAM] Safe instantiation
$tool = new mergeUsers($argv ?? []);
$tool->execute();