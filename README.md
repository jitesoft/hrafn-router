# Router

Request enters the `handle` method, where at the router matches the request URI to a specific action which will receive the given `ServerRequestInterface`.  
Parameters will be passed to the function depending on their names, that is:

```php
public function handle (int $id, string $name) {...}

// URI => /path/to/method?name=abc&id=5
// [id] => 5 will be passed as the `id` argument (case insensitive).
// [name] => abc will be passed as the `name` argument (case insensitive).
```

Reg-ex is used to parse the URI of the actions. To set a value as an argument, use the `{name}` syntax and it will be passed
as any other get parameter.

```php
public function handle (int $id, string $name) {...}

// URI pattern => /path/to/method/{name}
// URI => /path/to/method/abc?id=5
// [id] => 5 will be passed as the `id` argument (case insensitive).
// The last query path part will be passed as [name] => abc, i.e., via the `name` argument.
```

The first value passed to the method used for handling the request will always be the full Request as a `psr\ServerRequestInterface`.

Any middlewares used have to implement the `psr\MiddlewareInterface`. Middlewares will always be called before the `Action` invokes the controller.
There order they are called depends on the order they are added when defining the route. Global middlewares added to the router right away will always be
called before the route middlewares.
