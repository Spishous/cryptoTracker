<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220427000841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE balance_coin (id INT AUTO_INCREMENT NOT NULL, crypto_id_id INT NOT NULL, quantity DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL, action_closed TINYINT(1) NOT NULL, quote DOUBLE PRECISION NOT NULL, latest_quote DOUBLE PRECISION NOT NULL, INDEX IDX_474D665A69F28E2C (crypto_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE balance_coin ADD CONSTRAINT FK_474D665A69F28E2C FOREIGN KEY (crypto_id_id) REFERENCES crypto_list (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE balance_coin');
    }
}
