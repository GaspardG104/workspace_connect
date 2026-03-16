-- 0. Activation de l'extension pour les contraintes de réservation
CREATE EXTENSION IF NOT EXISTS btree_gist;

-- 1. Gestion des Rôles
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(50) UNIQUE NOT NULL 
);

-- 2. Table des Utilisateurs
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    id_role INTEGER REFERENCES roles(id),
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    immatriculation VARCHAR(20),
    password_hash TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Table des Ressources
CREATE TYPE resource_type AS ENUM ('parking', 'bureau', 'salle', 'box');

CREATE TABLE resources (
    id SERIAL PRIMARY KEY,
    type resource_type NOT NULL,
    nom VARCHAR(100) NOT NULL,
    localisation TEXT,
    capacite INTEGER DEFAULT 1,
    equipements JSONB, 
    is_active BOOLEAN DEFAULT TRUE 
);

-- 4. Table des Réservations
CREATE TABLE bookings (
    id SERIAL PRIMARY KEY,
    id_user INTEGER REFERENCES users(id) ON DELETE CASCADE,
    id_resource INTEGER REFERENCES resources(id) ON DELETE CASCADE,
    date_debut TIMESTAMP NOT NULL,
    date_fin TIMESTAMP NOT NULL,
    statut VARCHAR(20) DEFAULT 'confirme',
    qr_code_token TEXT UNIQUE,
    
    -- Empêche le chevauchement (Vérifie que début < fin et pas de doublon sur la ressource)
    CONSTRAINT no_overlap EXCLUDE USING gist (
        id_resource WITH =,
        tsrange(date_debut, date_fin) WITH &&
    ),
    CONSTRAINT check_dates CHECK (date_debut < date_fin),

    CONSTRAINT check_date_future CHECK (date_debut >= CURRENT_TIMESTAMP)
);

-- 5. Table des Participants
CREATE TABLE attendees (
    id SERIAL PRIMARY KEY,
    id_booking INTEGER REFERENCES bookings(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    nom_invite VARCHAR(100)
);

CREATE TABLE booking_invites (
    id SERIAL PRIMARY KEY,
    id_booking INTEGER REFERENCES bookings(id) ON DELETE CASCADE,
    id_user INTEGER REFERENCES users(id) ON DELETE CASCADE
);