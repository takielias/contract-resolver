<?php

namespace TakiElias\ContractResolver\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;
use TakiElias\ContractResolver\ContractResolverServiceProvider;

class ContractResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create the necessary directories
        $this->createTestDirectories();

        // Create stub files for testing
        $this->createStubFiles();
    }

    protected function tearDown(): void
    {
        // Clean up generated files
        $this->cleanupGeneratedFiles();
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            ContractResolverServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup test environment
        $app['config']->set('app.key', 'base64:'.base64_encode('contract-resolver-test-key'));
    }

    /** @test */
    public function it_can_create_repository_interface()
    {
        // Debug: Check directory permissions
        $contractsDir = app_path('Contracts/Repositories');
        $this->assertTrue(is_dir($contractsDir), "Directory $contractsDir does not exist");
        $this->assertTrue(is_writable($contractsDir), "Directory $contractsDir is not writable");

        $result = Artisan::call('cr:make-repo-interface', ['name' => 'Test']);

        // Debug: Show command output if failed
        if ($result !== 0) {
            $output = Artisan::output();
            $this->fail("Command failed with exit code: $result. Output: $output");
        }

        $filePath = app_path('Contracts/Repositories/TestRepositoryInterface.php');
        $this->assertTrue(file_exists($filePath), "File $filePath was not created");

        $content = file_get_contents($filePath);
        $this->assertStringContainsString('interface TestRepositoryInterface', $content);
        $this->assertStringContainsString('namespace App\Contracts\Repositories;', $content);
    }

    /** @test */
    public function it_can_create_repository_implementation()
    {
        Artisan::call('cr:make-repo', ['name' => 'Test']);

        $this->assertTrue(file_exists(app_path('Repositories/TestRepository.php')));

        $content = file_get_contents(app_path('Repositories/TestRepository.php'));
        $this->assertStringContainsString('class TestRepository implements TestRepositoryInterface', $content);
        $this->assertStringContainsString('namespace App\Repositories;', $content);
        $this->assertStringContainsString('use App\Contracts\Repositories\TestRepositoryInterface;', $content);
    }

    /** @test */
    public function it_can_create_service_interface()
    {
        Artisan::call('cr:make-service-interface', ['name' => 'Test']);

        $this->assertTrue(file_exists(app_path('Contracts/Services/TestServiceInterface.php')));

        $content = file_get_contents(app_path('Contracts/Services/TestServiceInterface.php'));
        $this->assertStringContainsString('interface TestServiceInterface', $content);
        $this->assertStringContainsString('namespace App\Contracts\Services;', $content);
    }

    /** @test */
    public function it_can_create_service_implementation()
    {
        Artisan::call('cr:make-service', ['name' => 'Test']);

        $this->assertTrue(file_exists(app_path('Services/TestService.php')));

        $content = file_get_contents(app_path('Services/TestService.php'));
        $this->assertStringContainsString('final class TestService implements TestServiceInterface', $content);
        $this->assertStringContainsString('namespace App\Services;', $content);
        $this->assertStringContainsString('use App\Contracts\Services\TestServiceInterface;', $content);
    }

    /** @test */
    public function it_can_create_repository_with_namespace()
    {
        Artisan::call('cr:make-repo-interface', ['name' => 'Admin\User']);
        Artisan::call('cr:make-repo', ['name' => 'Admin\User']);

        $this->assertTrue(file_exists(app_path('Contracts/Repositories/Admin/UserRepositoryInterface.php')));
        $this->assertTrue(file_exists(app_path('Repositories/Admin/UserRepository.php')));

        $interfaceContent = file_get_contents(app_path('Contracts/Repositories/Admin/UserRepositoryInterface.php'));
        $this->assertStringContainsString('namespace App\Contracts\Repositories\Admin;', $interfaceContent);

        $repoContent = file_get_contents(app_path('Repositories/Admin/UserRepository.php'));
        $this->assertStringContainsString('namespace App\Repositories\Admin;', $repoContent);
        $this->assertStringContainsString('use App\Contracts\Repositories\Admin\UserRepositoryInterface;', $repoContent);
    }

    /** @test */
    public function it_can_create_service_with_namespace()
    {
        Artisan::call('cr:make-service-interface', ['name' => 'Admin\User']);
        Artisan::call('cr:make-service', ['name' => 'Admin\User']);

        $this->assertTrue(file_exists(app_path('Contracts/Services/Admin/UserServiceInterface.php')));
        $this->assertTrue(file_exists(app_path('Services/Admin/UserService.php')));

        $interfaceContent = file_get_contents(app_path('Contracts/Services/Admin/UserServiceInterface.php'));
        $this->assertStringContainsString('namespace App\Contracts\Services\Admin;', $interfaceContent);

        $serviceContent = file_get_contents(app_path('Services/Admin/UserService.php'));
        $this->assertStringContainsString('namespace App\Services\Admin;', $serviceContent);
        $this->assertStringContainsString('use App\Contracts\Services\Admin\UserServiceInterface;', $serviceContent);
    }

    /** @test */
    public function debug_command_execution()
    {
        // Test basic file creation first
        $testFile = app_path('test.txt');
        file_put_contents($testFile, 'test');
        $this->assertTrue(file_exists($testFile));
        unlink($testFile);

        // Test Artisan command execution
        $result = Artisan::call('list');
        $this->assertEquals(0, $result);

        // Check our commands are registered
        $output = Artisan::output();
        $this->assertStringContainsString('cr:make-repo-interface', $output);
    }

    /** @test */
    public function it_handles_invalid_interface_gracefully()
    {
        // Create a file that's not a valid interface
        $this->createInvalidInterface();

        // This should not throw an exception
        $this->app->register(ContractResolverServiceProvider::class);

        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    /** @test */
    public function it_appends_correct_suffixes_to_class_names()
    {
        // Test without a suffix
        Artisan::call('cr:make-repo-interface', ['name' => 'Product']);
        $this->assertTrue(file_exists(app_path('Contracts/Repositories/ProductRepositoryInterface.php')));

        // Test with suffix already present
        Artisan::call('cr:make-service-interface', ['name' => 'ProductServiceInterface']);
        $this->assertTrue(file_exists(app_path('Contracts/Services/ProductServiceInterface.php')));

        // Verify only one suffix is added
        $content = file_get_contents(app_path('Contracts/Services/ProductServiceInterface.php'));
        $this->assertStringContainsString('interface ProductServiceInterface', $content);
        $this->assertStringNotContainsString('ProductServiceInterfaceInterface', $content);
    }

    /** @test */
    public function main_command_can_create_repository_only()
    {
        // Mock the command interaction
        $this->artisan('cr:make')
            ->expectsChoice('What do you want to create ?', ['Repository'], [
                'Service', 'Repository', 'All'
            ])
            ->expectsQuestion('What is the name ?', 'TestProduct')
            ->assertExitCode(0);

        $this->assertTrue(file_exists(app_path('Contracts/Repositories/TestProductRepositoryInterface.php')));
        $this->assertTrue(file_exists(app_path('Repositories/TestProductRepository.php')));
    }

    /** @test */
    public function main_command_can_create_service_only()
    {
        $this->artisan('cr:make')
            ->expectsChoice('What do you want to create ?', ['Service'], [
                'Service', 'Repository', 'All'
            ])
            ->expectsQuestion('What is the name ?', 'TestProduct')
            ->assertExitCode(0);

        $this->assertTrue(file_exists(app_path('Contracts/Services/TestProductServiceInterface.php')));
        $this->assertTrue(file_exists(app_path('Services/TestProductService.php')));
    }

    /** @test */
    public function main_command_can_create_all()
    {
        $this->artisan('cr:make')
            ->expectsChoice('What do you want to create ?', ['All'], [
                'Service', 'Repository', 'All'
            ])
            ->expectsQuestion('What is the name ?', 'TestProduct')
            ->assertExitCode(0);

        // Check repository files
        $this->assertTrue(file_exists(app_path('Contracts/Repositories/TestProductRepositoryInterface.php')));
        $this->assertTrue(file_exists(app_path('Repositories/TestProductRepository.php')));

        // Check service files
        $this->assertTrue(file_exists(app_path('Contracts/Services/TestProductServiceInterface.php')));
        $this->assertTrue(file_exists(app_path('Services/TestProductService.php')));
    }

    private function createTestDirectories()
    {
        $dirs = [
            app_path('Contracts/Repositories'),
            app_path('Contracts/Services'),
            app_path('Repositories'),
            app_path('Services'),
        ];

        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
                // Ensure writable permissions for DDEV
                chmod($dir, 0775);
            }
        }

        // Make parent directories writable too
        chmod(app_path('Contracts'), 0775);
        chmod(app_path('Repositories'), 0775);
        chmod(app_path('Services'), 0775);
    }

    private function createStubFiles()
    {
        // The commands look for stubs relative to their own location: __DIR__. $stub
        // Since we're testing, we need to place stubs where the actual commands would find them
        $packageRoot = dirname(dirname(__DIR__)); // Go up from tests/ to package root
        $stubsPath = $packageRoot . '/src/Console/Commands/stubs';

        if (!file_exists($stubsPath)) {
            mkdir($stubsPath, 0755, true);
        }

        // Repository interface stub
        file_put_contents($stubsPath . '/repository.interface.stub',
            '<?php declare(strict_types=1);

namespace {{ namespace }};

interface {{ class }}
{

}');

        // Repository stub
        file_put_contents($stubsPath . '/repository.stub',
            '<?php declare(strict_types=1);

namespace {{ namespace }};

use Illuminate\Database\Eloquent\Builder;
use {{ repoContractsNamespace }}\{{ class }}Interface;

class {{ class }}  implements {{ class }}Interface
{

}');

        // Service interface stub
        file_put_contents($stubsPath . '/service.interface.stub',
            '<?php  declare(strict_types=1);

namespace {{ namespace }};

interface {{ class }}
{

}
');

        // Service stub
        file_put_contents($stubsPath . '/service.stub',
            '<?php declare(strict_types=1);

namespace {{ namespace }};

use {{ serviceContractsNamespace }}\{{ class }}Interface;

final class {{ class }} implements {{ class }}Interface
{

}');
    }

    private function createTestInterface()
    {
        $interfaceCode = '<?php
namespace App\Contracts\Services;

interface TestAutoBindInterface
{
    public function test();
}';

        file_put_contents(app_path('Contracts/Services/TestAutoBindInterface.php'), $interfaceCode);
    }

    private function createTestImplementation()
    {
        $implementationCode = '<?php
namespace App\Services;

use App\Contracts\Services\TestAutoBindInterface;

class TestAutoBind implements TestAutoBindInterface
{
    public function test()
    {
        return "test";
    }
}';

        file_put_contents(app_path('Services/TestAutoBind.php'), $implementationCode);
    }

    private function createInvalidInterface()
    {
        $invalidCode = '<?php
namespace App\Contracts\Services;

// This is not a valid interface
class InvalidInterface
{
    public function test() {}
}';

        file_put_contents(app_path('Contracts/Services/InvalidInterface.php'), $invalidCode);
    }

    private function cleanupGeneratedFiles()
    {
        $filesystem = new Filesystem();

        $paths = [
            app_path('Contracts'),
            app_path('Repositories'),
            app_path('Services'),
            dirname(__DIR__, 2) . '/src/Console/Commands/stubs',
        ];

        foreach ($paths as $path) {
            if ($filesystem->exists($path)) {
                $filesystem->deleteDirectory($path);
            }
        }
    }
}
