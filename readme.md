# Contract Resolver

[![Latest Version on Packagist](https://img.shields.io/packagist/v/takielias/contract-resolver.svg?style=flat-square)](https://packagist.org/packages/takielias/contract-resolver)
[![Total Downloads](https://img.shields.io/packagist/dt/takielias/contract-resolver.svg?style=flat-square)](https://packagist.org/packages/takielias/contract-resolver)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/takielias/contract-resolver/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/takielias/contract-resolver/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/takielias/contract-resolver/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/takielias/contract-resolver/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![License](https://img.shields.io/packagist/l/takielias/contract-resolver.svg?style=flat-square)](https://packagist.org/packages/takielias/contract-resolver)

A powerful Laravel package that automatically resolves and binds contracts (interfaces) to their implementations, with comprehensive code generation commands for repositories and services following the Repository and Service pattern.

<a href="https://www.buymeacoffee.com/takielias" target="_blank"> <img align="left" src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" height="50" width="210" alt="takielias" /></a>

<br/>
<br/>

## ✨ Features

- **🔄 Auto-binding**: Automatically binds interfaces to their implementations
- **🎨 Code Generation**: Generate repositories, services, and their interfaces with artisan commands
- **📁 Convention-based**: Follows Laravel naming conventions and directory structure
- **🔧 Highly Configurable**: Customize paths, namespaces, and binding rules
- **⚡ Performance Optimized**: Efficient file scanning and namespace resolution
- **🧪 Well Tested**: Comprehensive test suite with high code coverage
- **📦 Zero Dependencies**: Only requires core Laravel components

## 🚀 Installation

You can install the package via composer:

```bash
composer require takielias/contract-resolver
```

The package will automatically register itself via Laravel's package discovery.

### Publishing Configuration

Optionally, you can publish the config file:

```bash
php artisan vendor:publish --provider="TakiElias\ContractResolver\ContractResolverServiceProvider" --tag="contract-resolver.config"
```

## 📖 Usage

## Available Commands

| Command | Description |
|---------|-------------|
| `cr:make` | Interactive command to create repositories, services, or both |
| `cr:make-repo-interface` | Generate repository interface |
| `cr:make-repo` | Generate repository implementation |
| `cr:make-service-interface` | Generate service interface |
| `cr:make-service` | Generate service implementation |

### Auto-binding Contracts

The package automatically scans and binds interfaces to their implementations based on Laravel conventions:

**Directory Structure:**
```
app/
├── Contracts/
│   ├── Repositories/
│   │   ├── UserRepositoryInterface.php
│   │   └── PostRepositoryInterface.php
│   └── Services/
│       ├── UserServiceInterface.php
│       └── PostServiceInterface.php
├── Repositories/
│   ├── UserRepository.php
│   └── PostRepository.php
└── Services/
    ├── UserService.php
    └── PostService.php
```

**Example Interface:**
```php
<?php

namespace App\Contracts\Repositories;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function create(array $data): User;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
```

**Example Implementation:**
```php
<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return User::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return User::destroy($id);
    }
}
```

**Using in Controllers:**
```php
<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function show(int $id)
    {
        $user = $this->userRepository->findById($id);
        
        return response()->json($user);
    }
}
```

### Code Generation Commands

The package provides powerful artisan commands to generate boilerplate code:

#### Generate Everything

```bash
php artisan cr:make
```

This interactive command will prompt you to:
1. Choose what to create (Service, Repository, or All)
2. Enter the name (e.g., "Product")

#### Generate Repository with Interface

```bash
php artisan cr:make-repo-interface Product
php artisan cr:make-repo Product
```

**Generated Interface** (`app/Contracts/Repositories/ProductRepositoryInterface.php`):
```php
<?php

namespace App\Contracts\Repositories;

interface ProductRepositoryInterface
{
    //
}
```

**Generated Repository** (`app/Repositories/ProductRepository.php`):
```php
<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    //
}
```

#### Generate Service with Interface

```bash
php artisan cr:make-service-interface Product
php artisan cr:make-service Product
```

**Generated Service Interface** (`app/Contracts/Services/ProductServiceInterface.php`):
```php
<?php

namespace App\Contracts\Services;

interface ProductServiceInterface
{
    //
}
```

**Generated Service** (`app/Services/ProductService.php`):
```php
<?php

namespace App\Services;

use App\Contracts\Services\ProductServiceInterface;

class ProductService implements ProductServiceInterface
{
    //
}
```

### Advanced Usage

#### Nested Namespaces

The package supports nested namespaces:

```bash
php artisan cr:make-repo Admin\\User
```

This creates:
- `app/Contracts/Repositories/Admin/UserRepositoryInterface.php`
- `app/Repositories/Admin/UserRepository.php`

#### Force Overwrite

Use the `--force` flag to overwrite existing files:

```bash
php artisan cr:make-repo Product --force
```

### Real-world Example

Here's a complete example of a User management system:

**1. Generate the files:**
```bash
php artisan cr:make
# Choose "All" and enter "User"
```

**2. Implement the Repository Interface:**
```php
<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function create(array $data): User;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function search(string $query): \Illuminate\Database\Eloquent\Collection;
}
```

**3. Implement the Repository:**
```php
<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return User::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return User::destroy($id);
    }

    public function search(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();
    }
}
```

**4. Implement the Service Interface:**
```php
<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    public function getAllUsers(int $perPage = 15): LengthAwarePaginator;
    public function getUserById(int $id): ?User;
    public function createUser(array $data): User;
    public function updateUser(int $id, array $data): bool;
    public function deleteUser(int $id): bool;
    public function searchUsers(string $query): \Illuminate\Database\Eloquent\Collection;
}
```

**5. Implement the Service:**
```php
<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        
        return $this->userRepository->create($data);
    }

    public function updateUser(int $id, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function searchUsers(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return $this->userRepository->search($query);
    }
}
```

**6. Use in Controller:**
```php
<?php

namespace App\Http\Controllers;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserServiceInterface $userService
    ) {}

    public function index(Request $request)
    {
        $users = $this->userService->getAllUsers($request->get('per_page', 15));
        
        return response()->json($users);
    }

    public function show(int $id)
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());
        
        return response()->json($user, 201);
    }

    public function update(UpdateUserRequest $request, int $id)
    {
        $updated = $this->userService->updateUser($id, $request->validated());
        
        if (!$updated) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['message' => 'User updated successfully']);
    }

    public function destroy(int $id)
    {
        $deleted = $this->userService->deleteUser($id);
        
        if (!$deleted) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $users = $this->userService->searchUsers($query);
        
        return response()->json($users);
    }
}
```

## ⚙️ Configuration

The package works out of the box with sensible defaults. You can customize the behavior by publishing and editing the configuration file:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto-binding Paths
    |--------------------------------------------------------------------------
    |
    | Define the paths where the package should scan for interfaces and
    | their implementations. The package will automatically bind
    | interfaces to their corresponding implementations.
    |
    */
    'paths' => [
        'contracts' => [
            'repositories' => app_path('Contracts/Repositories'),
            'services' => app_path('Contracts/Services'),
        ],
        'implementations' => [
            'repositories' => app_path('Repositories'),
            'services' => app_path('Services'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Naming Conventions
    |--------------------------------------------------------------------------
    |
    | Define the naming conventions used for automatic binding.
    | The package will use these patterns to match interfaces
    | with their implementations.
    |
    */
    'naming' => [
        'interface_suffix' => 'Interface',
        'remove_segments' => ['Contracts'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-binding
    |--------------------------------------------------------------------------
    |
    | Enable or disable automatic binding of interfaces to implementations.
    | When enabled, the package will automatically scan and bind interfaces
    | during the service provider registration.
    |
    */
    'auto_binding' => [
        'enabled' => true,
        'cache_bindings' => true,
    ],
];
```

## 🧪 Testing

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## 📈 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒 Security Vulnerabilities

If you discover a security vulnerability within this package, please send an e-mail to Taki Elias via [taki.elias@gmail.com](mailto:taki.elias@gmail.com). All security vulnerabilities will be promptly addressed.

## 🏆 Credits

- [Taki Elias](https://github.com/takielias)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🌟 Show Your Support

If this package helped you, please consider giving it a ⭐ on GitHub!

## 🔗 Links

- [GitHub Repository](https://github.com/takielias/contract-resolver)
- [Packagist Package](https://packagist.org/packages/takielias/contract-resolver)
- [Author's Website](https://ebuz.xyz)
- [Laravel Documentation](https://laravel.com/docs)

---

**Made with ❤️ by [Taki Elias](https://ebuz.xyz)**
