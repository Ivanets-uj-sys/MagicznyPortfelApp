<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250612200331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', slug VARCHAR(64) DEFAULT NULL, UNIQUE INDEX uq_categories_title (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE operations (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, wallet_id INT NOT NULL, title VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', amount DOUBLE PRECISION NOT NULL, balance DOUBLE PRECISION NOT NULL, operation_description VARCHAR(200) NOT NULL, slug VARCHAR(64) DEFAULT NULL, INDEX IDX_2814534812469DE2 (category_id), INDEX IDX_28145348712520F3 (wallet_id), UNIQUE INDEX uq_operation_title (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE operation_tags (operation_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_40CCC9B744AC3583 (operation_id), INDEX IDX_40CCC9B7BAD26311 (tag_id), PRIMARY KEY(operation_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', title VARCHAR(64) NOT NULL, slug VARCHAR(64) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE wallets (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', title VARCHAR(64) NOT NULL, slug VARCHAR(64) DEFAULT NULL, UNIQUE INDEX uq_wallet_title (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE operations ADD CONSTRAINT FK_2814534812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE operations ADD CONSTRAINT FK_28145348712520F3 FOREIGN KEY (wallet_id) REFERENCES wallets (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE operation_tags ADD CONSTRAINT FK_40CCC9B744AC3583 FOREIGN KEY (operation_id) REFERENCES operations (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE operation_tags ADD CONSTRAINT FK_40CCC9B7BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE operations DROP FOREIGN KEY FK_2814534812469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE operations DROP FOREIGN KEY FK_28145348712520F3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE operation_tags DROP FOREIGN KEY FK_40CCC9B744AC3583
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE operation_tags DROP FOREIGN KEY FK_40CCC9B7BAD26311
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categories
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE operations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE operation_tags
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tags
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE wallets
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
