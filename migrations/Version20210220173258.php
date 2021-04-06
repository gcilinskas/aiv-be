<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210220173258 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, track_id INT DEFAULT NULL, transcription_track_id INT DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, uri VARCHAR(255) DEFAULT NULL, sample_rate_hertz INT DEFAULT NULL, encoding_type INT DEFAULT NULL, aws_key VARCHAR(255) DEFAULT NULL, extension VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8C9F36105ED23C43 (track_id), UNIQUE INDEX UNIQ_8C9F3610B6EEACC4 (transcription_track_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE track (id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, transcription_file_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, artist VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D6E3F8A693CB796C (file_id), UNIQUE INDEX UNIQ_D6E3F8A6178FCD2B (transcription_file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE word (id INT AUTO_INCREMENT NOT NULL, track_id INT DEFAULT NULL, word VARCHAR(255) DEFAULT NULL, start_time DOUBLE PRECISION DEFAULT NULL, end_time DOUBLE PRECISION DEFAULT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_C3F175115ED23C43 (track_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36105ED23C43 FOREIGN KEY (track_id) REFERENCES track (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610B6EEACC4 FOREIGN KEY (transcription_track_id) REFERENCES track (id)');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A693CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A6178FCD2B FOREIGN KEY (transcription_file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE word ADD CONSTRAINT FK_C3F175115ED23C43 FOREIGN KEY (track_id) REFERENCES track (id)');
        $this->addSql('ALTER TABLE user ADD updated_at DATETIME NOT NULL, ADD created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A693CB796C');
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A6178FCD2B');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36105ED23C43');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610B6EEACC4');
        $this->addSql('ALTER TABLE word DROP FOREIGN KEY FK_C3F175115ED23C43');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE track');
        $this->addSql('DROP TABLE word');
        $this->addSql('ALTER TABLE user DROP updated_at, DROP created_at');
    }
}
