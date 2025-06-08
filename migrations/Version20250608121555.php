<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250608121555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE order_items (id SERIAL NOT NULL, order_id_id INT DEFAULT NULL, product_id_id INT DEFAULT NULL, quantity INT NOT NULL, price_order INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_62809DB0FCDAEAAA ON order_items (order_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_62809DB0DE18E50B ON order_items (product_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE orders (id SERIAL NOT NULL, users_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status_order VARCHAR(255) NOT NULL, amount_quantity INT NOT NULL, email_order VARCHAR(100) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E52FFDEE67B3B43D ON orders (users_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN orders.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES orders (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE67B3B43D FOREIGN KEY (users_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ALTER category_id SET NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_items DROP CONSTRAINT FK_62809DB0FCDAEAAA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_items DROP CONSTRAINT FK_62809DB0DE18E50B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEE67B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_items
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE orders
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ALTER category_id DROP NOT NULL
        SQL);
    }
}
