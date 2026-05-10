<?php

namespace App\Providers;

use App\Repositories\AccountRepository;
use App\Repositories\InstallmentRepository;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Interfaces\InstallmentRepositoryInterface;
use App\Repositories\Interfaces\LoanRepositoryInterface;
use App\Repositories\Interfaces\TranscationRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\LoanRepository;
use App\Repositories\TranscationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

        $this->app->bind(
        InstallmentRepositoryInterface::class,
        InstallmentRepository::class
        );

        $this->app->bind(
            AccountRepositoryInterface::class,
            AccountRepository::class
        );

        $this->app->bind(
            TranscationRepositoryInterface::class,
            TranscationRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            LoanRepositoryInterface::class,
            LoanRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
