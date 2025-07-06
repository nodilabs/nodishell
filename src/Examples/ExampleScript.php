<?php

namespace NodiLabs\NodiShell\Examples;

use NodiLabs\NodiShell\Abstracts\BaseScript;

/**
 * Example script demonstrating BaseScript usage.
 * This file shows how to extend BaseScript with common functionality.
 */
final class ExampleScript extends BaseScript
{
    protected string $name = 'example-script';

    protected string $description = 'An example script demonstrating BaseScript functionality';

    protected string $category = 'examples';

    protected bool $productionSafe = true;

    protected array $tags = ['example', 'demo', 'tutorial'];

    protected array $parameters = [
        [
            'name' => 'user_id',
            'label' => 'User ID',
            'type' => 'string',
            'required' => true,
            'description' => 'The ID of the user to process',
        ],
        [
            'name' => 'action',
            'label' => 'Action to perform',
            'type' => 'string',
            'required' => false,
            'description' => 'The action to perform (default: info)',
        ],
    ];

    public function execute(array $parameters): mixed
    {
        try {
            // Validate required parameters using helper method
            $this->validateParameters($parameters, ['user_id']);

            // Get parameters using helper methods
            $userId = $this->getParameter($parameters, 'user_id');
            $action = $this->getParameter($parameters, 'action', 'info');

            // Access session and variables using helper methods
            $session = $this->getSession($parameters);
            $variables = $this->getVariables($parameters);

            // Simulate some processing
            $userData = [
                'id' => $userId,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'action' => $action,
            ];

            // Store result in session if available
            if ($session) {
                $session->setVariable('last_user_processed', $userData);
            }

            // Return success using helper method
            return $this->success($userData, "Successfully processed user {$userId} with action '{$action}'");

        } catch (\InvalidArgumentException $e) {
            // Return error using helper method
            return $this->error('Validation failed: '.$e->getMessage());

        } catch (\Exception $e) {
            // Return error using helper method
            return $this->error('An error occurred: '.$e->getMessage());
        }
    }
}
