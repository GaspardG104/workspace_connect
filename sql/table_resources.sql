-- 1. Ajout des Salles de réunion
INSERT INTO resources (nom, type, localisation, capacite, equipements) VALUES 
('S1', 'salle', 'Zone Nord', 10, '{}'::jsonb),
('S2', 'salle', 'Zone Sud', 8, '{}'::jsonb);

-- 2. Ajout des Boxes
INSERT INTO resources (nom, type, localisation, capacite, equipements) VALUES 
('B1', 'box', 'Zone Centrale', 2, '{}'::jsonb),
('B2', 'box', 'Zone Centrale', 2, '{}'::jsonb);

-- 3. Ajout des 36 Bureaux (Ilots I1 à I6, Bureaux B1 à B6)
INSERT INTO resources (nom, type, localisation, capacite, equipements)
SELECT 
    'I' || ilot || 'B' || bureau AS nom,
    'bureau'::resource_type AS type, -- On cast explicitement vers ton type personnalisé
    'Espace Open Space' AS localisation,
    1 AS capacite,
    '{}'::jsonb AS equipements
FROM 
    generate_series(1, 6) AS ilot,
    generate_series(1, 6) AS bureau;