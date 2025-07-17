<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250714111812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE candidate (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, position VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE member (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, dues_paid TINYINT(1) NOT NULL, voting_code VARCHAR(6) NOT NULL, city VARCHAR(255) DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, promotion_year INT DEFAULT NULL, annual_payments JSON DEFAULT NULL COMMENT '(DC2Type:json)', UNIQUE INDEX UNIQ_70E4FA78E7927C74 (email), UNIQUE INDEX UNIQ_70E4FA781D7DE02C (voting_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE polling_station (id INT AUTO_INCREMENT NOT NULL, position VARCHAR(255) NOT NULL, is_open TINYINT(1) NOT NULL, is_result_published TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, candidate_id INT DEFAULT NULL, INDEX IDX_5A1085647597D3FE (member_id), INDEX IDX_5A10856491BD8781 (candidate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote ADD CONSTRAINT FK_5A1085647597D3FE FOREIGN KEY (member_id) REFERENCES member (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote ADD CONSTRAINT FK_5A10856491BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE vote DROP FOREIGN KEY FK_5A1085647597D3FE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE vote DROP FOREIGN KEY FK_5A10856491BD8781
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE candidate
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE member
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE polling_station
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vote
        SQL);
    }
}
