# Laravel Starter

## Content table

- [starting up](#starting-up)
    - [production server profile](#production-server-profile)
    - [dev server profile](#dev-server-profile)
    - [develop profile](#develop-profile)
- [adding a new entity](#adding-a-new-entity)
    - [models and migrations](#models-and-migrations)
    - [policies](#policies)
    - [exceptions](#exceptions)
    - [requests](#requests)
    - [repositories](#repositories)
    - [services](#services)
    - [controllers](#controllers)
    - [resources](#resources)
- [dto](#dto)
- [helpers](#helpers)

## starting up

there's several predefined profile for using is the different situation.

##### _Production SERVER_ profile

use on production server.

instructions:

- `docker-compose up -d --build` : starts the services (_--build_ is just for the first time).
- `docker-compose exec app bash` : open a terminal inside of `app` service.
- `chown -R www-data:www-data bootstrap/cache storage lang vault` : change the owner of folders and files to the
  docker's default user.
- `cp .env.server .env` : create a copy from `.env.server` file and named the new file `.env`
- `composer update --no-dev --optimize-autoloader` : install necessary composer dependencies and optimize autoloader
- `php artisan key:generate` : generate a new unique key for laravel that used for encode and decode seasons, cookies
  etc.
- `php artisan migrate` : create tables and seeds the database
- `php artisan storage:link` : create a symbolic link between the `public` and `storage/app/public` folder.
- `exit` : exit from the container bash.

##### _Dev SERVER_ profile

use on dev server.

instructions:

- `docker-compose up -d --build` : starts the services (_--build_ is just for the first time).
- `docker-compose exec app bash` : open a terminal inside of `app` service.
- `chown -R www-data:www-data bootstrap/cache storage lang vault` : change the owner of folders and files to the
  docker's default user.
- `cp .env.server .env` : create a copy from `.env.server` file and named the new file `.env`
- `composer update --no-dev --optimize-autoloader` : install necessary composer dependencies and optimize autoloader
- `php artisan key:generate` : generate a new unique key for laravel that used for encode and decode seasons, cookies
  etc.
- `php artisan migrate:fresh --seed --force` : create tables and seeds the database
- `php artisan storage:link` : create a symbolic link between the `public` and `storage/app/public` folder.
- `exit` : exit from the container bash.

##### _DEVELOP_ profile

this profile recommended for developing.

instructions:

- `docker-compose up -d --build` : starts the services (_--build_ is just for the first time).
- `docker-compose exec app bash` : open a terminal inside of `app` service.
- `chown -R www-data:www-data bootstrap/cache storage lang vault` : change the owner of folders and files to the
  docker's default user.
- `cp .env.develop .env` : create a copy from `.env.develop` file and named the new file `.env`
- `composer update` : install all composer dependencies
- `php artisan key:generate` : generate a new unique key for laravel that used for encode and decode seasons, cookies
  etc.
- `php artisan migrate:fresh --seed ` : create tables and seeds the database
- `php artisan storage:link` : create a symbolic link between the `public` and `storage/app/public` folder.
- `exit` : exit from the container bash.
- open [localhost:8080](http://localhost:8080/)

## adding a new entity

all the entities separate using a namespace like `core`, `ideas` or `index`. namespaces group a set of entities which
are relevant to each other.

### models and migrations

#### models

you can create a model in a namespace that you chose for your entity. models should implement these interfaces:

```php
  use App\Models\BaseModel;
  use App\Models\Contracts\Filtering\Filterable;
  use App\Models\Contracts\Filtering\Loadable;
  use App\Models\Contracts\ResourceCollectionable;
  use App\Models\Traits\Paginatable;
  
  class NewModel extends BaseModel implements Filterable, Loadable, ResourceCollectionable{
        use Paginatable;
        // ...
  }
```

> seeders and factories also should create in a same namespace as the entity

#### migrations

migrations can be created
by `php artisan make:migration create_sample_migartion_table --fullpath --path database/migrations/namespace
` command.
> also, you should prefix the table name with namespace.

we already know that laravel doesn't register migration files in `migrations` sub folders. this issue solved
using `nscreed/laravel-migration-paths` package.

### policies

you can create the policies as usual, but don't forget to specify the namespace.

in policies, you can use `App\Policies\Traits\PolicyShortHandTrait` trait to make the needed ability for authorizing
users actions.

instead of

```php
  class GroupPolicy {
    // ...
  
    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create( User $user ): bool {
        return $user->can( 'group-create' );
    }
    
    // ...
  }
```

just do

```php
  use App\Policies\Traits\PolicyShortHandTrait;

  class GroupPolicy {
    use PolicyShortHandTrait;
    
    // ...
  
    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return bool
     */
    public function create( User $user ): bool {
        return $user->can( $this->makeAbility() );
    }
    
    // ...
  }
```

> after making the policy, don't forget to register the policy for related model in the `AuthServiceProvider`

### exceptions

first, create a class in the related namespace and extend it from `App\Exceptions\BaseException` class. you should
define a method for every single unique error that could happen.

there is sn example:

```php
  public static function failedToCreate(): BaseException {
      return self::make( "error message", BaseErrorCode::FAILED_TO_CREATE_MODEL,
          Response::HTTP_INTERNAL_SERVER_ERROR );
  }
```

every method should call static `make()` method and pass these three parameter:

- `message`: error message that describe the problem
- `errorCode`: a constant variable from `App\Exceptions\BaseErrorCode` that must return a unique value. every error
  should have one.

> errorCode represent in response body like { "code": "unique value" }. front dev should resolve each unique value
> to a string and show to the end user. this feature helps to have a multi languages' website easily

- `responseCode`: determines the http status code of response

### requests

requests have two tasks. first validating store and update requests and second one is validating relations requests
body.

requests classification is defined as: `{namespace}/{entity}`

in the target folder, requests class name should be like: `EntityActionRequest`

> action can be store,update or relationship name

there is an example of a relation request:

```php
  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
      return [
          'related'               => [ 'array' ],
          'related.*.id'          => [
              'required',
              'numeric',
              Rule::exists( ( new Model )->getTable(), 'id' )
          ],
          'related.*.pivot'       => [ 'array:info,order' ],
          'related.*.pivot.info'  => [ 'string', 'max:254' ],
          'related.*.pivot.order' => [ new Order( required: false ) ],
      ];
  }
```

and a valid body for this request should be like:

```json
{
  "related": [
    {
      "id": 1,
      "pivot": {
        "info": "info for id one",
        "order": 10
      }
    },
    {
      "id": 2
    }
  ]
}
```

### repositories

a repository should handle CRUD actions and working with relationships. for each entity, we should create a repository
contract and a repository class.

`contract`: repository contract should place in `Repositories/Contracts/{Namespace}` and extend the `Repository`
abstract class.

`repository`: repository class should place in `Repositories/{Namespace}` directory and implement the abstract methods.

> after creating contract and repository, we should register these classes in `App\Providers\RepositoryServiceProvider`.

### services

services should handle the business logic and control the app flow. services should place
in `Services/{Namespace}/{EntityName}`. for better DX(Developer Experience), CRUD and Relations logics, moved into
separate classes named `EntityService` and `EntityRelationService`.

#### filtering

filtering logic contains several filter and a base logic to apply filters on eloquent builder object.

`logic`: filtering logic placed in `Services/Filtering/FilteringService` class.
`filters`: a filter is a class that implement `Services/Contracts/Filters/Filter` abstract.

> notice: don't forget to register filters on `FilteringService`

### controllers

we have versioning for controllers, so we should create the controllers in `V{Number}` directory. then, as
usual, `{Namespace}/{EntityName}`. we have two controllers for each entity:

`EntityConroller`: handles CRUD and custom actions.
`EntityRelationsConroller`: handles Relations related actions.

### resources

resources handle the responses to client. every resource should override `with` method and set the `type` attribute to
entity's name. there is an example of it:

```php
 /**
  * Get any additional data that should be returned with the resource array.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return array
  */
  public function with( $request ) {
      return [
          'type' => 'pluralEntityName'
      ];
  }
```

next, resources should use `InteractsWithRelations` and `InteractsWithPivots` traits.

`InteractsWithRelations`: if a relation loaded or eager loaded using filters, we could show the related data
using `loadedRelations` method.
`InteractsWithPivots`: if an entity or a collection of entities load using a relationship, we could show the pivot data
using `loadedPivots` method.

## dto

dto is a class that accepts data and convert it into a standard format. you can use a dto for everything everywhere. in
this repository, for example, we used a dto for converting a validated data from user's request to a compatible format
for eloquent.

to create a dto, just make a class and postfix it with `Dto`. then, extend the `App\DTOs\Contracts\Dto` and implement
the `parse` method.

## helpers

basically general functions and enum classes will create there.

`arrays`: functions that always return an array. for example an array of allowed models or keywords.
`constants`: global const variables will place here.
`functions`: general functions will define here.
`Helpers/Enums`: we will create Enum classes there. for better DX and more functionality in enum classes, you can
use `App\Helpers\Traits\EnumHelper` trait.