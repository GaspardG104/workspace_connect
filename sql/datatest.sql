-- 1. Insertion des Rôles
INSERT INTO roles (nom) VALUES ('admin'), ('manager'), ('collaborateur');

-- 2. Insertion d'un premier utilisateur Admin (mot de passe fictif pour l'instant)
INSERT INTO users (id_role, nom, prenom, email, immatriculation, password_hash) 
VALUES (4, 'Test', 'Imonial' ,'imonial_test@test.com', 'CC-456-dd', 'password_en_clair_test'),
VALUES (1, 'Admin', 'Global', 'admin@workspace.com', 'AA-123-BB', '$2y$10$rHWM2q3Y6qFlxMqpLcVX2uuntbnVW3LQ./Wo.LOApk6jO.V9VFhpu');


-- 3. Insertion des 2 places de parking (US 1.1)
INSERT INTO resources (type, nom, localisation, equipements) VALUES 
('parking', 'Place P1', 'Niveau -1, Zone A', '{"borne_electrique": true}'),
('parking', 'Place P2', 'Niveau -1, Zone B', '{"borne_electrique": false}');

-- 4. Insertion des 4 salles de réunion (US 3.1)
INSERT INTO resources (type, nom, localisation, capacite, equipements) VALUES 
('salle', 'Salle Galaxie', 'Étage 1', 12, '{"wifi": true, "ecran": true, "visio": true}'),
('salle', 'Salle Orion', 'Étage 1', 6, '{"wifi": true, "ecran": true}'),
('salle', 'Salle Comète', 'Étage 2', 4, '{"wifi": true}'),
('salle', 'Bulle Silence', 'Étage 2', 2, '{"wifi": false, "prise": true}');

-- 5. Insertion de quelques bureaux en Open Space (pour le fun)
INSERT INTO resources (type, nom, localisation) VALUES 
('bureau', 'Bureau 101', 'Open Space Nord'),
('bureau', 'Bureau 102', 'Open Space Nord');