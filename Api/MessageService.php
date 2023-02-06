<?php
namespace Omega\Cyberkonsultant\Api;

use Omega\Cyberkonsultant\Api\Traits\NormalizeTrait;

class MessageService extends AbstractApiService
{
    use NormalizeTrait;

    private const DEFAULT_LIMIT = 1000;

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function getEvents()
    {
        $this->getMessages('events');
    }

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function getPageEvents()
    {
        $this->getMessages('page_events');
    }

    /**
     * @param string $topic
     * @return null
     * @throws \Zend_Db_Statement_Exception
     */
    private function getMessages(string $topic)
    {
        $this->checkPermission();
        $limit = $this->getRequest()->get('limit', self::DEFAULT_LIMIT);
        $limit = abs((int) $limit ?: 50);
        $limit = $limit <= 1000 ? $limit : 1000;

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('idea_flowbox_messenger_messages');

        $result = $connection->fetchAll('SELECT * FROM ' . $tableName . ' WHERE queue_name=\''.$topic.'\' ORDER BY available_at ASC LIMIT '.$limit);
        $events = [];
        $ids = [];
        foreach ($result as $event) {
            $events[] = $this->normalize(unserialize($event['body']));
            $ids[] = $event['id'];
        }

        if (count($events)) {
            $connection->query("DELETE FROM " . $tableName . " WHERE id IN (".join(',', $ids).")")->execute();
        }

        return $this->jsonResponse([
            'limit' => (int) $limit,
            'data' => $events,
        ]);
    }
}
