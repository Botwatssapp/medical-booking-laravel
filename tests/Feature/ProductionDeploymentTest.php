<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionDeploymentTest extends TestCase
{
    public function test_env_example_documents_required_keys_without_secrets(): void
    {
        $example = file_get_contents(base_path('.env.example'));

        foreach ([
            'APP_NAME=',
            'APP_ENV=',
            'APP_KEY=',
            'APP_DEBUG=',
            'APP_URL=',
            'DB_CONNECTION=',
            'DB_HOST=',
            'DB_DATABASE=',
            'SESSION_DRIVER=',
            'CACHE_STORE=',
            'QUEUE_CONNECTION=',
            'MAIL_MAILER=',
            'SESSION_SECURE_COOKIE=',
        ] as $key) {
            $this->assertStringContainsString($key, $example);
        }

        $this->assertStringContainsString('APP_DEBUG=false', $example);
        $this->assertStringContainsString('medical_booking', $example);
        $this->assertStringNotContainsString('gmail.com', $example);
        $this->assertMatchesRegularExpression('/^MAIL_PASSWORD=(null)?$/m', $example);
    }

    public function test_gitignore_excludes_environment_and_dependency_trees(): void
    {
        $active = collect(file(base_path('.gitignore'), FILE_IGNORE_NEW_LINES))
            ->map(fn (string $line) => trim($line))
            ->filter(fn (string $line) => $line !== '' && ! str_starts_with($line, '#'));

        foreach (['.env', '/vendor', '/node_modules', '/public/hot', '/public/storage', '*.log'] as $pattern) {
            $this->assertTrue($active->contains($pattern), "Missing gitignore pattern: {$pattern}");
        }
    }

    public function test_deployment_guide_exists(): void
    {
        $this->assertFileExists(base_path('docs/DEPLOYMENT.md'));
        $contents = file_get_contents(base_path('docs/DEPLOYMENT.md'));
        $this->assertStringContainsString('php artisan migrate --force', $contents);
        $this->assertStringContainsString('migrate:fresh', $contents);
        $this->assertStringContainsString('appointments:expire', $contents);
    }
}
