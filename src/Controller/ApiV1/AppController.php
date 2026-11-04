<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Controller\AppController as BaseController;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Table;

class AppController extends BaseController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    public function beforeRender(EventInterface $event): void
    {
        if ($this->viewBuilder()->getOption('serialize') === null) {
            $this->viewBuilder()->setOption('serialize', true);
        }
    }

    /**
     * @param array<string> $fields
     */
    protected function patchFromRequest(Table $table, EntityInterface $entity, array $fields): EntityInterface
    {
        return $table->patchEntity($entity, $this->request->getData(), [
            'fields' => $fields,
        ]);
    }

    protected function respondSaved(string $key, EntityInterface $entity, bool $created): void
    {
        $this->response = $this->response->withStatus($created ? 201 : 200);
        $this->set([
            'success' => true,
            $key => $entity,
        ]);
    }

    protected function respondValidationError(EntityInterface $entity): void
    {
        $this->response = $this->response->withStatus(422);
        $this->set([
            'success' => false,
            'message' => 'A mentés sikertelen.',
            'errors' => $entity->getErrors(),
        ]);
    }

    protected function respondDeleted(): void
    {
        $this->set([
            'success' => true,
        ]);
    }

    protected function respondConflict(string $message): void
    {
        $this->response = $this->response->withStatus(409);
        $this->set([
            'success' => false,
            'message' => $message,
        ]);
    }

    /**
     * @param \Cake\Datasource\Paging\PaginatedInterface<mixed, mixed> $results
     * @return array<string, int|null>
     */
    protected function pagingFor(PaginatedInterface $results): array
    {
        return [
            'page' => $results->currentPage(),
            'limit' => $results->perPage(),
            'count' => $results->totalCount(),
            'pageCount' => $results->pageCount(),
        ];
    }
}
