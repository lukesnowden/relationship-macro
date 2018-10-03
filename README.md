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

```blade
@if( $customer->orders->isEmtpy() )
    ...
@endif

You have {{ $customer->orders()->count() }} orders
```

You can also use a whole class to store your relationships and add them in one go, note that all methods must return a `Closure`.

```php
<?php

class MyRelationships {
    
    public function orders() : \Closure
    {
        return function() {
            return $this->hasMany( Order::class, 'customer_id' );
        };
    }
    
}

Customer::relationshipMacros( new MyRelationships );
```

### Credits

[spatie/macroable](https://github.com/spatie/macroable)

This trait is a altered version of the Macroable package to work with Laravel Models.

## MIT License

Copyright (c) 2018 Luke Snowden

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
