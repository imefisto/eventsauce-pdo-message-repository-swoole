<?php
namespace Imefisto\EventSaucePDOMessageRepository\Swoole;

use Imefisto\EventSaucePDOMessageRepository\ConnectionManager;
use Swoole\Coroutine\Channel;

class SwooleConnectionManager implements ConnectionManager
{
    private Channel $pool;
    private int $currentSize = 0;
    private array $options;

    public function __construct(
        private string $dsn,
        private string $user,
        private string $password,
        private int $maxSize = 10,
        ?array $options = []
    ) {
        $this->pool = new Channel($maxSize);
        $this->options = !empty($options)
            ? $options
            : [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
    }

    public function get(): \PDO
    {
        if (!$this->pool->isEmpty()) {
            return $this->pool->pop();
        }

        if ($this->currentSize < $this->maxSize) {
            $this->currentSize++;
            return $this->connect();
        }

        return $this->pool->pop();
    }

    public function put(object $conn): void
    {
        $this->pool->push($conn);
    }

    private function connect(): \PDO
    {
        return new \PDO($this->dsn, $this->user, $this->password, $this->options);
    }
}
