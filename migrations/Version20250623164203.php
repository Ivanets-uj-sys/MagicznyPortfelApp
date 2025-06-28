<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623164203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE operations CHANGE operation_description operation_description LONGTEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wallets DROP FOREIGN KEY FK_967AAA6CF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wallets ADD CONSTRAINT FK_967AAA6CF675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE wallets DROP FOREIGN KEY FK_967AAA6CF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE wallets ADD CONSTRAINT FK_967AAA6CF675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE operations CHANGE operation_description operation_description VARCHAR(200) NOT NULL
        SQL);
    }
}
