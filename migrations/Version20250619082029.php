<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619082029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE wallets ADD author_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wallets ADD CONSTRAINT FK_967AAA6CF675F31B FOREIGN KEY (author_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_967AAA6CF675F31B ON wallets (author_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE wallets DROP FOREIGN KEY FK_967AAA6CF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_967AAA6CF675F31B ON wallets
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wallets DROP author_id
        SQL);
    }
}
