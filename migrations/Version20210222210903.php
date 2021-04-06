<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210222210903 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36103B5D9B');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36105ED23C43');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610B6EEACC4');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36103B5D9B FOREIGN KEY (vocabulary_track_id) REFERENCES track (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36105ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610B6EEACC4 FOREIGN KEY (transcription_track_id) REFERENCES track (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36105ED23C43');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610B6EEACC4');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36103B5D9B');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36105ED23C43 FOREIGN KEY (track_id) REFERENCES track (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610B6EEACC4 FOREIGN KEY (transcription_track_id) REFERENCES track (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36103B5D9B FOREIGN KEY (vocabulary_track_id) REFERENCES track (id)');
    }
}
