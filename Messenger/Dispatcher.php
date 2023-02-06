<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Messenger;

use Magento\Framework\App\ResourceConnection;

class Dispatcher
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection;
    }

    public function dispatch(Queue $queue)
    {
        $tableName = $this->connection->getTableName('idea_flowbox_messenger_messages');
        $conn = $this->connection->getConnection();

        $conn->insert($tableName, [
            'body' => serialize($queue->getBody()),
            'queue_name' => $queue->getQueueName(),
            'queue_status' => 'unacked',
            'available_at' => date('Y-m-d H:i:s'),
        ]);
    }
}