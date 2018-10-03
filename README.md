# Relationship Macro

This trait allows you to easily create relationships for foreign models.

```cli
composer require lukesnowden/relationship-macros:^0.0.1
```

To allow use on the destination model use the trait `Lukesnowden\RelationshipMacros\Traits\Macro`.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lukesnowden\RelationshipMacros\Traits\Macro;

class Customer extends Model
{

    use Macro;
    
}
```

To add a relationship;

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Customer;
use Some\Other\Models\Order;

class AppServiceProvider extends ServiceProvider
{

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{

        Customer::relationshipMacro( 'orders', function() {
            return $this->hasMany( Order::class, 'customer_id' );
        });
        
    }
    
}
```

You can now use the relationship natively using both the eloquent and query builder;

```cli
@if( $customer->orders->isEmtpy() )
    ...
@endif

{{ You have {{ $customer->orders()->count() }} orders

```
