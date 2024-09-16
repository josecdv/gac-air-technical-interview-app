<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240916144206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stock_historic (id INT AUTO_INCREMENT NOT NULL, user_id_id INT UNSIGNED NOT NULL, product_id_id INT NOT NULL, created_at DATETIME NOT NULL, stock INT NOT NULL, INDEX IDX_E294BB149D86650F (user_id_id), INDEX IDX_E294BB14DE18E50B (product_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stock_historic ADD CONSTRAINT FK_E294BB149D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE stock_historic ADD CONSTRAINT FK_E294BB14DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product DROP stock');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock_historic DROP FOREIGN KEY FK_E294BB149D86650F');
        $this->addSql('ALTER TABLE stock_historic DROP FOREIGN KEY FK_E294BB14DE18E50B');
        $this->addSql('DROP TABLE stock_historic');
        $this->addSql('ALTER TABLE product ADD stock INT NOT NULL');
    }
}
