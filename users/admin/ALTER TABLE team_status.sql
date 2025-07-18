ALTER TABLE teams
ADD COLUMN team_status ENUM('active, disbanded, onprobation, defunct') DEFAULT active NOT NULL;
