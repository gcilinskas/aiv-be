<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210222211354 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A6178FCD2B');
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A693CB796C');
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A6A453AF14');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A6178FCD2B FOREIGN KEY (transcription_file_id) REFERENCES file (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A693CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A6A453AF14 FOREIGN KEY (vocabulary_file_id) REFERENCES file (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A693CB796C');
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A6178FCD2B');
        $this->addSql('ALTER TABLE track DROP FOREIGN KEY FK_D6E3F8A6A453AF14');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A693CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A6178FCD2B FOREIGN KEY (transcription_file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A6A453AF14 FOREIGN KEY (vocabulary_file_id) REFERENCES file (id)');
    }
}
