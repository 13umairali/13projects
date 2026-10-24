<?php

declare(strict_types=1);

namespace App\Tenancy;

use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper as BaseDatabaseTenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

/**
 * Drop-in replacement for Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper.
 *
 * The stock bootstrapper, when APP_ENV=local, checks that the tenant database
 * exists by querying INFORMATION_SCHEMA through the *central* connection. On
 * shared hosting (cPanel) each tenant DB is owned by its own MySQL user, so the
 * central user cannot see it and provisioning aborts with
 * "Database X does not exist" even though the tenant credentials work.
 *
 * We skip that central-side probe entirely: a missing database still fails
 * loudly (MySQL 1049) the moment the tenant connection is actually used.
 */
class DatabaseTenancyBootstrapper extends BaseDatabaseTenancyBootstrapper
{
    public function bootstrap(Tenant $tenant)
    {
        /** @var \Stancl\Tenancy\Contracts\TenantWithDatabase $tenant */

        // Still fail early with a clear exception if the DB driver has no manager.
        $tenant->database()->manager();

        $this->database->connectToTenant($tenant);
    }
}
