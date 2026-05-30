# eventsauce-pdo-message-repository-swoole
Swoole coroutine-aware connection pool for [eventsauce-pdo-message-repository](https://github.com/imefisto/eventsauce-pdo-message-repository).

## Installation

```bash
composer require imefisto/eventsauce-pdo-message-repository-swoole
```

## Usage

`SwooleConnectionManager` is a drop-in replacement for `DefaultConnectionManager`:

```php
use Imefisto\EventSaucePDOMessageRepository\Swoole\SwooleConnectionManager;

$connection = new SwooleConnectionManager(
    dsn: 'mysql:host=127.0.0.1;dbname=mydb',
    user: 'user',
    password: 'password',
    maxSize: 10
);

$repo = new PDOMessageRepository($connection, $tableName, $serializer);
```

## How it works

- Connections are created lazily up to `$maxSize`
- When all connections are checked out, `get()` suspends the calling coroutine until one is returned — no busy-waiting, no connection leak
- Coroutine context makes the pool safe: Swoole only yields at I/O / channel operations, so the connection count check and increment are atomic
