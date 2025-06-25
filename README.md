# 🚀 NodiShell

**Advanced Laravel Interactive Shell with Autocomplete and Script Repository**

NodiShell is a powerful, extensible interactive shell for Laravel applications that provides organized script execution, variable management, system monitoring, and development tools in a beautiful terminal interface.

## 📚 Table of Contents

- [🚀 NodiShell](#-nodishell)
  - [📚 Table of Contents](#-table-of-contents)
  - [✨ Features](#-features)
    - [🎯 Core Features](#-core-features)
    - [🔧 Development Tools](#-development-tools)
    - [🎨 User Experience](#-user-experience)
  - [📦 Installation](#-installation)
    - [1. Install the Package](#1-install-the-package)
    - [2. Publish Configuration](#2-publish-configuration)
    - [3. Set Up Directory Structure](#3-set-up-directory-structure)
    - [4. Configure Your Environment](#4-configure-your-environment)
  - [🚀 Usage](#-usage)
    - [Basic Usage](#basic-usage)
    - [Command Line Options](#command-line-options)
    - [Navigation](#navigation)
  - [⚡ Code Generation](#-code-generation)
    - [Generator Commands](#generator-commands)
    - [Creating Categories with Generators](#creating-categories-with-generators)
    - [Creating Scripts with Generators](#creating-scripts-with-generators)
    - [Creating System Checks with Generators](#creating-system-checks-with-generators)
  - [🛠️ Development Guide](#️-development-guide)
    - [Creating a Script](#creating-a-script)
      - [1. Create Script Class](#1-create-script-class)
      - [2. Script Interface Methods](#2-script-interface-methods)
    - [Creating a Category](#creating-a-category)
      - [1. Create Category Class](#1-create-category-class)
      - [2. Category Interface Methods](#2-category-interface-methods)
    - [Creating System Checks](#creating-system-checks)
      - [1. Create System Check Class](#1-create-system-check-class)
      - [2. Register System Checks](#2-register-system-checks)
      - [3. System Check Interface Methods](#3-system-check-interface-methods)
  - [🎛️ Configuration](#️-configuration)
    - [Configuration File Structure](#configuration-file-structure)
    - [Environment Variables](#environment-variables)
  - [🔧 Advanced Usage](#-advanced-usage)
    - [Variable Management](#variable-management)
    - [Production Safety](#production-safety)
    - [Integration with Laravel Tinker](#integration-with-laravel-tinker)
  - [📋 Built-in Categories](#-built-in-categories)
    - [Database Category](#database-category)
    - [Model Category](#model-category)
    - [Testing Category](#testing-category)
    - [Maintenance Category](#maintenance-category)
  - [🤝 Contributing](#-contributing)
    - [Adding Features](#adding-features)
    - [Coding Standards](#coding-standards)
  - [📝 Examples](#-examples)
    - [Example: User Management Script](#example-user-management-script)
  - [🐛 Troubleshooting](#-troubleshooting)
    - [Common Issues](#common-issues)
    - [Debug Mode](#debug-mode)
  - [📄 License](#-license)
  - [🙋‍♂️ Support](#️-support)

---

## ✨ Features

### 🎯 Core Features
- **Category-based Script Organization** - Organize scripts into logical categories
- **Interactive Menu System** - Beautiful, intuitive navigation with arrow keys
- **Variable Management** - Store and reuse variables across script executions
- **Search Functionality** - Quickly find scripts across all categories
- **Command History** - Track and review executed commands
- **Production Safety** - Built-in safety checks for production environments

### 🔧 Development Tools
- **Raw PHP Execution** - Execute PHP code directly with Laravel context
- **Laravel Tinker Integration** - Enhanced Tinker with NodiShell variables
- **System Status Monitoring** - Real-time system health checks
- **Custom System Checks** - Extensible health check system
- **Session Persistence** - Variables persist throughout your shell session

### 🎨 User Experience
- **Beautiful Interface** - Colorful, organized display with emojis and borders
- **Autocomplete Support** - Smart search and filtering
- **Error Handling** - Graceful error handling with helpful messages
- **Multi-environment Support** - Safe operation across development and production

## 📦 Installation

### 1. Install the Package

Add NodiShell to your Laravel project:

```bash
composer require nodilabs/nodishell
```

### 2. Publish Configuration

Publish the configuration file to customize NodiShell:

```bash
php artisan vendor:publish --provider="NodiLabs\NodiShell\NodiShellServiceProvider"
```

### 3. Set Up Directory Structure

Create the required directories in your Laravel application (You can update these directories in the configuration file `config/nodishell.php`):

```bash
mkdir -p app/Console/NodiShell/Categories
mkdir -p app/Console/NodiShell/Scripts
mkdir -p app/Console/NodiShell/Checks
```

### 4. Configure Your Environment

Edit `config/nodishell.php` to customize settings:

```php
<?php

return [   
    'features' => [
        'search' => true,
        'raw_php' => true,
        'variable_manager' => true,
        'system_status' => true
    ],
    
    'production_safety' => [
        'safe_mode' => true
    ],
    
    'discovery' => [
        'categories_path' => app_path('Console/NodiShell/Categories'),
        'scripts_path' => app_path('Console/NodiShell/Scripts')
    ],
];
```

## 🚀 Usage

### Basic Usage

Launch NodiShell in interactive mode:

```bash
php artisan nodishell
```

### Command Line Options

```bash
# Interactive mode (default)
php artisan nodishell

# Execute a specific script directly
php artisan nodishell --script=script-name

# Start in a specific category
php artisan nodishell --category=database

# Enable production safety mode
php artisan nodishell --safe-mode
```

### Navigation

- **Arrow Keys**: Navigate through menus
- **Enter**: Select an option
- **Type**: Search and filter options
- **Ctrl+C**: Exit at any time

## ⚡ Code Generation

NodiShell provides powerful generator commands to quickly create categories, scripts, and system checks with proper structure and boilerplate code.

### Generator Commands

All generator commands follow Laravel's convention and include helpful options:

```bash
# Available generator commands
php artisan nodishell:category    # Create a new category
php artisan nodishell:script      # Create a new script
php artisan nodishell:check       # Create a new system check
```

**Common Options:**
- `--force` - Overwrite existing files
- `--help` - Show detailed command help

### Creating Categories with Generators

Generate a new category with all the required structure:

```bash
# Basic category creation
php artisan nodishell:category UserManagement

# With options
php artisan nodishell:category UserManagement \
    --description="User management and administration" \
    --icon="👥" \
    --color="blue" \
    --sort-order=50
```

**Available Options:**
- `--description=` - Category description
- `--icon=` - Emoji icon for the category
- `--color=` - Color theme (blue, green, red, yellow, purple, etc.)
- `--sort-order=` - Display order (default: 100)

**Generated Structure:**
```php
<?php



namespace App\Console\NodiShell\Categories;

use App\Console\NodiShell\Categories\BaseCategory;

final class UserManagementCategory extends BaseCategory
{
    protected int $sortOrder = 50;

    public function getName(): string
    {
        return 'User management and administration';
    }

    public function getIcon(): string
    {
        return '👥';
    }

    public function getColor(): string
    {
        return 'blue';
    }

    protected function loadScripts(): void
    {
        $this->scripts = [
            // Add your scripts here
        ];
    }
}
```

### Creating Scripts with Generators

Generate a new script with proper structure and error handling:

```bash
# Basic script creation
php artisan nodishell:script ResetUserPassword

# With options
php artisan nodishell:script ResetUserPassword \
    --category="users" \
    --description="Reset a user's password and send notification" \
    --tags="users,password,security,notification" \
    --production-safe
```

**Available Options:**
- `--category=` - Script category
- `--description=` - Script description
- `--tags=` - Comma-separated tags
- `--production-safe` - Mark as safe for production

**Generated Structure:**
```php
<?php



namespace App\Console\NodiShell\Scripts;

use App\Console\NodiShell\Scripts\BaseScript;

final class ResetUserPasswordScript extends BaseScript
{
    protected string $name = 'Reset User Password';
    protected string $description = 'Reset a user\'s password and send notification';
    protected string $category = 'users';
    protected bool $productionSafe = true;
    protected array $tags = ['users', 'password', 'security', 'notification'];

    protected array $parameters = [
        // Add your parameters here
    ];

    public function execute(array $parameters = []): mixed
    {
        try {
            $session = $parameters['_session'] ?? null;
            $variables = $parameters['_variables'] ?? [];

            // Your script logic here

            return [
                'success' => true,
                'data' => null,
                'message' => 'Script executed successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
```

### Creating System Checks with Generators

Generate a new system check for monitoring:

```bash
# Basic check creation
php artisan nodishell:check CacheConnection

# With options
php artisan nodishell:check CacheConnection \
    --label="Cache Connection Status" \
    --description="Verify all cache connections are working properly"
```

**Available Options:**
- `--label=` - Display label for the check
- `--description=` - Detailed description

**Generated Structure:**
```php
<?php



namespace App\Console\NodiShell\Checks;

use NodiLabs\NodiShell\Contracts\SystemCheckInterface;
use NodiLabs\NodiShell\Data\CheckResultData;

final class CacheConnectionCheck implements SystemCheckInterface
{
    public function getLabel(): string
    {
        return 'Cache Connection Status';
    }

    public function getDescription(): string
    {
        return 'Verify all cache connections are working properly';
    }

    public function run(): array
    {
        $results = [];

        try {
            // Your check logic here

            $results[] = new CheckResultData(
                successful: true,
                message: 'Cache Connection Status: OK'
            );

        } catch (\Exception $e) {
            $results[] = new CheckResultData(
                successful: false,
                message: 'Cache Connection Status: FAILED - ' . $e->getMessage()
            );
        }

        return $results;
    }
}
```

**Key Benefits of Generators:**

✅ **Consistent Structure** - All files follow project conventions  
✅ **Smart Naming** - Automatically appends appropriate suffixes  
✅ **Interactive Prompts** - Asks for missing information  
✅ **Type Safety** - Includes proper type hints and strict typing  
✅ **Error Handling** - Built-in exception handling  
✅ **Documentation** - Helpful comments and next steps  

## 🛠️ Development Guide

### Creating a Script

Scripts are the core executable units in NodiShell. Here's how to create one:

#### 1. Create Script Class

Create a new script in `app/Console/NodiShell/Scripts/`:

```php
<?php

namespace App\Console\NodiShell\Scripts;

use NodiLabs\NodiShell\Contracts\ScriptInterface;

class MyCustomScript implements ScriptInterface
{
    public function getName(): string
    {
        return 'my-custom-script';
    }

    public function getDescription(): string
    {
        return 'This script demonstrates custom functionality';
    }

    public function getCategory(): string
    {
        return 'custom';
    }

    public function isProductionSafe(): bool
    {
        return false; // Set to true if safe for production
    }

    public function getParameters(): array
    {
        return [
            [
                'name' => 'user_id',
                'label' => 'Enter User ID',
                'required' => true,
            ],
            // As many params as you need
        ];
    }

    public function execute(array $parameters = []): mixed
    {
        // Here your script's logic
    }
}
```

#### 2. Script Interface Methods

| Method | Description | Required |
|--------|-------------|----------|
| `getName()` | Unique script identifier | ✅ |
| `getDescription()` | Human-readable description | ✅ |
| `getCategory()` | Category this script belongs to | ✅ |
| `isProductionSafe()` | Whether safe to run in production | ✅ |
| `getParameters()` | Array of required parameters | ✅ |
| `execute(array $parameters)` | Main execution logic | ✅ |

### Creating a Category

Categories organize related scripts together. Here's how to create one:

#### 1. Create Category Class

Create a new category in `app/Console/NodiShell/Categories/`:

```php
<?php

namespace App\Console\NodiShell\Categories;

use NodiLabs\NodiShell\Contracts\CategoryInterface;
use NodiLabs\NodiShell\Contracts\ScriptInterface;
use Illuminate\Support\Collection;

class CustomCategory implements CategoryInterface
{
    public function getName(): string
    {
        return 'Custom Operations';
    }

    public function getDescription(): string
    {
        return 'Custom business logic and operations';
    }

    public function getIcon(): string
    {
        return '⚡'; // Choose an appropriate emoji
    }

    public function getSortOrder(): int
    {
        return 100; // Higher numbers appear later in the list
    }

    public function isEnabled(): bool
    {
        return true; // Set to false to disable this category
    }

    public function getScripts(): array
    {
        // Return array of script instances
        return [
            new \App\Console\NodiShell\Scripts\MyCustomScript(),
            new \App\Console\NodiShell\Scripts\AnotherCustomScript(),
            // Add more scripts as needed
        ];
    }
}
```

#### 2. Category Interface Methods

| Method | Description | Required |
|--------|-------------|----------|
| `getName()` | Display name for the category | ✅ |
| `getDescription()` | Category description | ✅ |
| `getIcon()` | Emoji icon for visual identification | ✅ |
| `getSortOrder()` | Numeric sort order (0-999) | ✅ |
| `isEnabled()` | Whether category is active | ✅ |
| `getScripts()` | Array of ScriptInterface instances | ✅ |

### Creating System Checks

System checks provide health monitoring capabilities:

#### 1. Create System Check Class

Create a new check in `app/Console/NodiShell/Checks/`:

```php
<?php

namespace App\Console\NodiShell\Checks;

use NodiLabs\NodiShell\Contracts\SystemCheckInterface;
use NodiLabs\NodiShell\Data\CheckResultData;

class DatabaseConnectionCheck implements SystemCheckInterface
{
    public function getLabel(): string
    {
        return 'Database Connection';
    }

    public function getDescription(): string
    {
        return 'Verifies that database connections are working properly';
    }

    public function run(): array
    {
        $results = [];
        
        try {
            // Test default connection
            \DB::connection()->getPdo();
            $results[] = new CheckResultData(
                successful: true,
                message: 'Default database connection: OK'
            );
            
            // Test additional connections if configured
            $connections = config('database.connections');
            foreach ($connections as $name => $config) {
                if ($name === config('database.default')) {
                    continue; // Skip default, already tested
                }
                
                try {
                    \DB::connection($name)->getPdo();
                    $results[] = new CheckResultData(
                        successful: true,
                        message: "Connection '{$name}': OK"
                    );
                } catch (\Exception $e) {
                    $results[] = new CheckResultData(
                        successful: false,
                        message: "Connection '{$name}': FAILED - {$e->getMessage()}"
                    );
                }
            }
            
        } catch (\Exception $e) {
            $results[] = new CheckResultData(
                successful: false,
                message: 'Default database connection: FAILED - ' . $e->getMessage()
            );
        }
        
        return $results;
    }
}
```

#### 2. Register System Checks

System checks are **automatically discovered** from the `app/Console/NodiShell/Checks/` directory. Just create your check class and NodiShell will find it automatically.

**Alternative: Manual Registration**

If you prefer manual registration or need to register checks from other locations, add them to your config file:

```php
// config/nodishell.php
'system_checks' => [
    \App\Console\NodiShell\Checks\DatabaseConnectionCheck::class,
    \App\Console\NodiShell\Checks\CacheConnectionCheck::class,
    \App\Console\NodiShell\Checks\QueueConnectionCheck::class,
],
```

#### 3. System Check Interface Methods

| Method | Description | Required |
|--------|-------------|----------|
| `getLabel()` | Short name for the check | ✅ |
| `getDescription()` | Detailed description | ✅ |
| `run()` | Execute check and return CheckResultData[] | ✅ |

## 🎛️ Configuration

### Configuration File Structure

```php
<?php

return [    
    // Feature toggles
    'features' => [
        'search' => true,                    // Global script search
        'raw_php' => true,                   // PHP execution mode
        'variable_manager' => true,          // Session variables
        'system_status' => true,             // System monitoring
        'model_explorer' => true,            // Model inspection
    ],
    
    // Production safety
    'production_safety' => [
        'safe_mode' => true,                 // Enable safety checks
    ],
    
    // Auto-discovery paths
    'discovery' => [
        'categories_path' => app_path('Console/NodiShell/Categories'),
        'scripts_path' => app_path('Console/NodiShell/Scripts'),
        'checks_path' => app_path('Console/NodiShell/Checks'),
    ],
    
    // Manual system checks registration (optional)
    'system_checks' => [
        // \App\Console\NodiShell\Checks\DatabaseConnectionCheck::class,
    ],
];
```

### Environment Variables

You can also configure NodiShell using environment variables, for example:

```env
NODISHELL_SAFE_MODE=true
NODISHELL_ENABLE_SEARCH=true
NODISHELL_ENABLE_RAW_PHP=false
```

## 🔧 Advanced Usage

### Variable Management

NodiShell provides persistent variable storage throughout your session:

```php
// In a script's execute method
public function execute(array $parameters = []): mixed
{
    $session = $parameters['_session'];
    
    // Store a variable
    $session->setVariable('my_data', ['key' => 'value']);
    
    // Retrieve a variable
    $data = $session->getVariable('my_data');
    
    // Get all variables
    $allVars = $session->getAllVariables();
    
    return $result;
}
```

### Production Safety

NodiShell includes built-in production safety features:
- **Safe Mode**: Warns about potentially dangerous operations
- **Production Checks**: Scripts marked as `isProductionSafe() = false` require confirmation
- **Environment Detection**: Automatic environment-aware behavior

### Integration with Laravel Tinker

NodiShell enhances Laravel Tinker by:
- **Injecting session variables** into each new Tinker session
- **Providing helper functions** like `nodishell_vars()` to list available variables
- **Auto-loading variables** with type information when Tinker starts

**Note:** Variables you create inside Tinker are not automatically saved back to NodiShell when you exit Tinker. Each new Tinker session starts fresh with the current NodiShell variables injected.

## 📋 Built-in Categories

NodiShell comes with several built-in categories:

### Database Category
- **Database migrations status**
- **Table inspection tools**
- **Query execution utilities**

### Model Category  
- **Model inspection**
- **Relationship exploration**
- **Data manipulation tools**

### Testing Category
- **Test execution helpers**
- **Test data generation**
- **Coverage reporting**

### Maintenance Category
- **Cache management**
- **Log rotation**
- **Cleanup operations**

## 🤝 Contributing

### Adding Features

1. Fork the repository
2. Create a feature branch
3. Add your functionality
4. Include tests
5. Submit a pull request

### Coding Standards

- Follow PSR-12 coding standards
- Add type hints for all methods
- Include comprehensive docblocks
- Write tests for new functionality

## 📝 Examples

### Example: User Management Script

```php
<?php

namespace App\Console\NodiShell\Scripts\Users;

use NodiLabs\NodiShell\Contracts\ScriptInterface;
use App\Models\User;

class ResetUserPasswordScript implements ScriptInterface
{
    public function getName(): string
    {
        return 'reset-user-password';
    }

    public function getDescription(): string
    {
        return 'Reset a user password and send notification';
    }

    public function getCategory(): string
    {
        return 'users';
    }

    public function isProductionSafe(): bool
    {
        return false; // Requires careful handling in production
    }

    public function getParameters(): array
    {
        return [
            [
                'name' => 'email',
                'label' => 'User email address',
                'required' => true,
            ],
            [
                'name' => 'send_notification',
                'label' => 'Send email notification? (y/n)',
                'required' => false,
            ],
        ];
    }

    public function execute(array $parameters = []): mixed
    {
        $email = $parameters['email'];
        $sendNotification = strtolower($parameters['send_notification'] ?? 'y') === 'y';
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            throw new \Exception("User with email {$email} not found");
        }
        
        // Generate new password
        $newPassword = \Str::random(12);
        $user->password = \Hash::make($newPassword);
        $user->save();
        
        $result = [
            'user_id' => $user->id,
            'email' => $user->email,
            'new_password' => $newPassword,
            'notification_sent' => false,
        ];
        
        if ($sendNotification) {
            // Send notification logic here
            $user->notify(new \App\Notifications\PasswordResetNotification($newPassword));
            $result['notification_sent'] = true;
        }
        
        return $result;
    }
}
```

## 🐛 Troubleshooting

### Common Issues

**Scripts not appearing in categories**
- Ensure your script implements `ScriptInterface`
- Check that the script's `getCategory()` matches your category key
- Verify the script class is properly namespaced

**Category not showing up**
- Ensure your category implements `CategoryInterface`
- Check that `isEnabled()` returns `true`
- Verify the category file is in the correct directory

**System checks not running**
- Make sure checks are registered in your service provider
- Verify checks implement `SystemCheckInterface`
- Check that the `run()` method returns an array of `CheckResultData`

### Debug Mode

Enable debug logging by setting:

```env
LOG_LEVEL=debug
```

## 📄 License

MIT License. See LICENSE file for details.

## 🙋‍♂️ Support

- **Documentation**: This README and inline code documentation
- **Issues**: GitHub Issues for bug reports and feature requests
- **Community**: Laravel community forums and Discord

---

**Built with ❤️ (and ☕) for the Laravel community**
