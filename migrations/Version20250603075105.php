<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603075105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD metal VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD size_product INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP other_attribute
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD other_attribute TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP metal
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP size_product
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN product.other_attribute IS '(DC2Type:array)'
        SQL);
    }
}
