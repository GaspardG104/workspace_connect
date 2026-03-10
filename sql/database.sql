-- 1. Gestion des Rôles (Collaborateur, Manager, Admin)
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(50) UNIQUE NOT NULL -- ex: 'admin', 'manager', 'collaborateur'
);

-- 2. Table des Utilisateurs
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    id_role INTEGER REFERENCES roles(id),
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    immatriculation VARCHAR(20), -- [cite: 31, 36]
    password_hash TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Table des Ressources (Parking, Bureaux, Salles)
CREATE TYPE resource_type AS ENUM ('parking', 'bureau', 'salle');

-- question pr Mr SANCHEZ : est ce que faire un type est plus interessant qu'une autre table
-- pck chaque type ne va pas avoir les meme parametres, une salle de réu n'a pas dde prise
-- pour recharger les voitures électrique par ex

CREATE TABLE resources (
    id SERIAL PRIMARY KEY,
    type resource_type NOT NULL, -- ex: Parking, Bureaux, Salles
    nom VARCHAR(100) NOT NULL, -- ex: 'Salle A1', 'Place P12'
    localisation TEXT, -- 
    capacite INTEGER DEFAULT 1, -- Pour les salles [cite: 22]
    equipements JSONB, --  ex: {"wifi": true, "ecran": true}
    is_active BOOLEAN DEFAULT TRUE -- Pour la maintenance 
);

-- 4. Table des Réservations
CREATE TABLE bookings (
    id SERIAL PRIMARY KEY,
    id_user INTEGER REFERENCES users(id) ON DELETE CASCADE,
    id_resource INTEGER REFERENCES resources(id) ON DELETE CASCADE,
    date_debut TIMESTAMP NOT NULL, -- 
    date_fin TIMESTAMP NOT NULL, -- 
    statut VARCHAR(20) DEFAULT 'confirme', -- confirme, annule, no_show [cite: 59, 60]
    qr_code_token TEXT UNIQUE, -- [cite: 18]
    
    -- Empêche le chevauchement de réservation pour une même ressource (Magie PostgreSQL)
    CONSTRAINT no_overlap EXCLUDE USING gist (
        id_resource WITH =,
        tsrange(date_debut, date_fin) WITH &&
    )
);

-- 5. Table des Participants (Salles de réunion)
CREATE TABLE attendees (
    id SERIAL PRIMARY KEY,
    id_booking INTEGER REFERENCES bookings(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL, -- Interne ou Externe [cite: 33, 49]
    nom_invite VARCHAR(100)
);