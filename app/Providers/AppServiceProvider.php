<?php

namespace App\Providers;

use App\Models\Contract;
use App\Models\Document;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Policies\ContractPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\OwnerPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\TenantPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(Owner::class, OwnerPolicy::class);
        Gate::policy(Contract::class, ContractPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
    }
}
