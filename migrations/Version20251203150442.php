<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251203150442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create action_log table for SQL-based application logs.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('action_log');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('type', 'string', ['length' => 50]);
        $table->addColumn('route', 'string', ['notnull' => false]);
        $table->addColumn('user', 'json', ['notnull' => false]);
        $table->addColumn('payload', 'json', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime_immutable');
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('action_log');
    }
}
