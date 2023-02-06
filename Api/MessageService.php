<?php
namespace Omega\Cyberkonsultant\Api;

use Omega\Cyberkonsultant\Api\Traits\NormalizeTrait;

class MessageService extends AbstractApiService
{
    use NormalizeTrait;

    private const DEFAULT_LIMIT = 1;

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function getEvents()
    {
        $this->getMessages('cyberkonsultant.track.events');
    }

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function getPageEvents()
    {
        $this->getMessages('cyberkonsultant.track.page_events');
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

        $connection = $this->resourceConnection->getConnection();
        $qmTable = $connection->getTableName('queue_message');
        $qmsTable = $connection->getTableName('queue_message_status');

        $query = "Select * FROM ".$qmTable." qm LEFT JOIN ".$qmsTable." qms ON qms.message_id = qm.id  WHERE qms.status = 2 AND qm.topic_name='".$topic."'  ORDER BY qm.id ASC LIMIT ".$limit;
        $result = $connection->fetchAll($query);
        $events = [];
        $ids = [];
        foreach ($result as $event) {
            $e = json_decode($event['body']);
            if (isset($e->type) && isset($e->uuid)) {
                $events[] = [
                    'user_id' => $e->uuid,
                    'event_time' => isset($e->eventTime) ? $e->eventTime : (new \DateTime())->format(\DateTime::ATOM),
                    'event_type' => $e->type,
                    'product_id' => isset($e->productId) ? $e->productId : 0,
                    'category_id' => isset($e->categoryId) ? $e->categoryId : 0,
                    'cart_id' => isset($e->cartId) ? $e->cartId : null,
                    'frame_id' => isset($e->frameId) ? $e->frameId : null,
                    'price' => isset($e->price) ? $e->price : 0,
                ];
                $ids[] = $event['id'];
            }
        }

        if (count($events)) {
            $connection->query("UPDATE " . $qmsTable . " SET status=4 WHERE id IN (".join(',', $ids).")")->execute();
        }

        return $this->jsonResponse([
            'limit' => (int) $limit,
            'data' => $events,
        ]);
    }
}
