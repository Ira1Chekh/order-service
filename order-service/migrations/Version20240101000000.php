<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create local_products and orders tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE local_products (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            price DOUBLE PRECISION NOT NULL,
            quantity INT NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE orders (
            order_id VARCHAR(36) NOT NULL,
            product_id VARCHAR(36) NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            quantity_ordered INT NOT NULL,
            order_status VARCHAR(20) NOT NULL,
            PRIMARY KEY(order_id),
            CONSTRAINT FK_order_product FOREIGN KEY (product_id) REFERENCES local_products (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE local_products');
    }
}
