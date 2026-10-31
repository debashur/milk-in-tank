<?php

declare(strict_types=1);

namespace App\Tests\Unit\Api;

use App\Api\Milk\CreatePourAction;
use App\Api\Milk\CreatePourInput;
use App\Api\Milk\ListPoursAction;
use App\Api\Milk\ListTanksAction;
use App\Api\Shared\ResponseFactory;
use App\Migration\M261031181800CreateMilkTables;
use App\Milk\MilkRepository;
use App\Milk\TankAllocator;
use Codeception\Test\Unit;
use HttpSoft\Message\ResponseFactory as PsrResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\ResponseFactory\DataResponseFactory;
use Yiisoft\Db\Migration\Informer\NullMigrationInformer;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;
use Yiisoft\Db\Sqlite\Dsn;
use Yiisoft\Http\Status;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;

final class MilkActionsTest extends Unit
{
    private Connection $db;
    private MilkRepository $repository;
    private ResponseFactory $responseFactory;

    protected function _before(): void
    {
        $this->db = new Connection(new Driver(new Dsn(databaseName: 'memory')));
        $this->db->createCommand('PRAGMA foreign_keys = ON')->execute();
        (new M261031181800CreateMilkTables())->up(new MigrationBuilder($this->db, new NullMigrationInformer()));
        $this->repository = new MilkRepository($this->db, new TankAllocator());
        $this->responseFactory = new ResponseFactory(new DataResponseFactory(new PsrResponseFactory()));
    }

    public function testListsFiveEmptyTanks(): void
    {
        $response = (new ListTanksAction($this->repository, $this->responseFactory))();
        $data = $this->data($response);

        assertSame(Status::OK, $response->getStatusCode());
        assertSame('success', $data['status']);
        assertSame(5, count($data['data']));
        assertSame(300, $data['data'][0]['capacity']);
        assertSame(0, $data['data'][0]['currentLiters']);
        assertSame(300, $data['data'][0]['freeLiters']);
    }

    public function testCreatesPourAndShowsItInTheJournal(): void
    {
        $create = (new CreatePourAction($this->repository, $this->responseFactory))($this->input('Иван', 100));
        $created = $this->data($create);

        assertSame(Status::CREATED, $create->getStatusCode());
        assertSame(1, $created['data']['pour']['tankId']);
        assertSame(100, $created['data']['pour']['liters']);
        assertSame(100, $created['data']['tanks'][0]['currentLiters']);
        assertSame(200, $created['data']['tanks'][0]['freeLiters']);

        $journal = $this->data((new ListPoursAction($this->repository, $this->responseFactory))());
        assertSame('Иван', $journal['data'][0]['personName']);
        assertSame(100, $journal['data'][0]['tankCurrentLiters']);
    }

    public function testRejectsPourThatFitsNowhere(): void
    {
        $response = (new CreatePourAction($this->repository, $this->responseFactory))($this->input('Иван', 301));
        $data = $this->data($response);

        assertSame(Status::UNPROCESSABLE_ENTITY, $response->getStatusCode());
        assertSame('failed', $data['status']);
        assertStringContainsString('301', $data['error_message']);
        assertSame([], $this->data((new ListPoursAction($this->repository, $this->responseFactory))())['data']);
    }

    public function testPourOf250SkipsTankWhoseCapacityIs200(): void
    {
        $this->db->createCommand()->update('tank', ['capacity' => 200], ['id' => 1])->execute();

        $created = $this->data(
            (new CreatePourAction($this->repository, $this->responseFactory))($this->input('Иван', 250)),
        );

        assertSame(2, $created['data']['pour']['tankId']);
        assertSame(0, $created['data']['tanks'][0]['currentLiters']);
        assertSame(250, $created['data']['tanks'][1]['currentLiters']);
    }

    private function input(string $personName, int $liters): CreatePourInput
    {
        $input = new CreatePourInput();
        $input->personName = $personName;
        $input->liters = $liters;

        return $input;
    }

    private function data(ResponseInterface $response): array
    {
        $body = $response->getBody();
        assertInstanceOf(DataStream::class, $body);

        return $body->getData();
    }
}
