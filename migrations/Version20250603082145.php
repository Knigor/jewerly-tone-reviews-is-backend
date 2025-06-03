<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603082145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change product.size_product type from int to JSON';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE product ALTER COLUMN size_product TYPE JSON USING to_json(size_product)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE product ALTER COLUMN size_product TYPE INT USING (size_product::text)::integer
        SQL);
    }
}

