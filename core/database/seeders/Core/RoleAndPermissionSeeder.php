<?php

    namespace Database\Seeders\Core;

    use App\Models\Core\User;
    use AreasEnum;
    use Hans\Horus\Models\Role;
    use Horus;
    use Illuminate\Database\Seeder;
    use RolesEnum;
    use Throwable;

    class RoleAndPermissionSeeder extends Seeder {
        /**
         * Run the database seeds.
         *
         * @return void
         * @throws Throwable
         */
        public function run() {
            Horus::createPermissions( [
                // create basic permissions for given model plus additional permissions [ 'viewRole', 'viewAuthor' ]
                // you can Model::class => 'permission' if you have one additional permission
                User::class => [
                    'viewHorizon',
                ],
            ], AreasEnum::ADMIN // you can create permissions for a specific guard
            );
            Horus::createPermissions( [
                User::class
            ], AreasEnum::USER );

            Horus::createRoles( [
                // create roles
                RolesEnum::DEFAULT_ADMINS
            ], AreasEnum::ADMIN  // you can determine a guard for a set of roles. you can assign permissions
            // with same guard to a role
            );
            Horus::createRoles( [ RolesEnum::DEFAULT_USERS ], AreasEnum::USER );

            // assign permissions to the given roles
            Horus::assignPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_ADMINS, AreasEnum::ADMIN ), [
                // you can Model::class => '*' if you just want basic permissions to assign to the role
                // also you can Model::class => 'permission' if you have one permission for a role
                User::class => [
                    '*',

                    'viewHorizon',
                ]
            ], AreasEnum::ADMIN // determine the permissions guard for assigning to a role
            // ( *notice : role must be in same guard as permissions are )
            );
            Horus::assignPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_USERS, AreasEnum::USER ), [
                User::class => [ 'view' ]
            ], AreasEnum::USER );

            // super permissions
            Horus::createSuperPermissions( [
                '*', // create *-* permission
                User::class => '*', // create user-*
            ], AreasEnum::ADMIN );

            // assign super permissions
            Horus::assignSuperPermissionsToRole( Role::findByName( RolesEnum::DEFAULT_ADMINS, AreasEnum::ADMIN ), [
                '*',
                User::class,
            ] );

        }
    }
