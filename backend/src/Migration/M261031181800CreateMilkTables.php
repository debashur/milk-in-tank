<?php

declare(strict_types=1);

namespace App\Migration;

use Yiisoft\Db\Constant\ReferentialAction;
use Yiisoft\Db\Constraint\ForeignKey;
use Yiisoft\Db\Migration\MigrationBuilder;
use Yiisoft\Db\Migration\RevertibleMigrationInterface;
use Yiisoft\Db\Migration\TransactionalMigrationInterface;

final class M261031181800CreateMilkTables implements RevertibleMigrationInterface, TransactionalMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $column = $b->columnBuilder();

        $b->createTable('tank', [
            'id' => $column::primaryKey(),
            'capacity' => $column::integer()->notNull()->check('capacity = 300'),
        ]);

        for ($id = 1; $id <= 5; ++$id) {
            $b->insert('tank', [
                'id' => $id,
                'capacity' => 300,
            ]);
        }

        $b->createTable('pour', [
            'id' => $column::primaryKey(),
            'person_name' => $column::string(100)->notNull()->check('length(person_name) BETWEEN 1 AND 100'),
            'liters' => $column::integer()->notNull()->check('liters >= 1 AND liters <= 300'),
            'tank_id' => $column::integer()->notNull()->reference(new ForeignKey(
                foreignTableName: 'tank',
                foreignColumnNames: ['id'],
                onDelete: ReferentialAction::RESTRICT,
                onUpdate: ReferentialAction::RESTRICT,
            )),
            'created_at' => $column::datetime()->notNull(),
        ]);

        $b->createIndex('pour', 'idx_pour_tank_id', 'tank_id');
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('pour');
        $b->dropTable('tank');
    }
}
