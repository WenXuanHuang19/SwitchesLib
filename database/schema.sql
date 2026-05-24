-- Switches Lib — database schema
--
-- Conventions:
--   * Numeric spec fields (forces, travels, spring length, pin count) are
--     nullable; NULL means "Unknown" and is rendered as such in the UI. This
--     keeps numeric sorting (e.g. by bottom-out force) clean.
--   * Text/enum spec fields store the literal string "Unknown" when no official
--     data exists.
--   * Hard delete only — there is no deleted_at column (ADR: hard delete).
--   * google_id / email_verified are intentionally omitted (ADR-0003 Google
--     login deferred, ADR-0005 email verification deferred).

CREATE DATABASE IF NOT EXISTS switches_lib
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE switches_lib;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS switches;
DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS designers;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100)  NOT NULL UNIQUE,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- designers  (the entity that designs a switch — replaces the old "brands")
-- ---------------------------------------------------------------------------
CREATE TABLE designers (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL UNIQUE,
    website    VARCHAR(255) NULL,
    country    VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- tags  (fixed enum definitions, seeded once — not editable via UI in MVP)
-- ---------------------------------------------------------------------------
CREATE TABLE tags (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    type        ENUM('switch_type','sound_profile','feel_profile','recommended_use') NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tag (type, name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- switches  (the core public entity)
-- ---------------------------------------------------------------------------
CREATE TABLE switches (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    slug                  VARCHAR(255) NOT NULL UNIQUE,
    name                  VARCHAR(255) NOT NULL,
    designer_id           INT NULL,
    series                VARCHAR(255) NULL,
    variant               VARCHAR(255) NULL,
    manufacturer          VARCHAR(255) NULL,
    switch_category       VARCHAR(50)  NOT NULL DEFAULT 'Mechanical MX',
    switch_type           VARCHAR(50)  NOT NULL,
    description           TEXT NULL,
    release_date          DATE NULL,

    -- Force & travel (numeric; NULL = Unknown)
    initial_force         DECIMAL(5,1) NULL,
    actuation_force       DECIMAL(5,1) NULL,
    bottom_out_force      DECIMAL(5,1) NULL,
    tactile_force         DECIMAL(5,1) NULL,
    actuation_travel      DECIMAL(4,2) NULL,
    total_travel          DECIMAL(4,2) NULL,
    spring_length         DECIMAL(5,2) NULL,
    spring_type           VARCHAR(100) NULL,

    -- Materials & structure
    top_housing_material    VARCHAR(100) NULL,
    bottom_housing_material VARCHAR(100) NULL,
    stem_material           VARCHAR(100) NULL,
    stem_type               VARCHAR(100) NULL,
    contact_material        VARCHAR(100) NULL,
    pin_count               INT NULL,
    led_diffuser            ENUM('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
    rgb_support             ENUM('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
    factory_lubed           ENUM('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
    is_silent               ENUM('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
    silent_structure        VARCHAR(255) NULL,

    -- Tags (one value per tag type; may be 'Unknown')
    sound_profile         VARCHAR(50) NOT NULL DEFAULT 'Unknown',
    feel_profile          VARCHAR(50) NOT NULL DEFAULT 'Unknown',
    recommended_use       VARCHAR(50) NOT NULL DEFAULT 'Unknown',

    -- Media & status
    image_url             VARCHAR(255) NULL,
    status                ENUM('approved','draft') NOT NULL DEFAULT 'approved',
    views_count           INT NOT NULL DEFAULT 0,
    submitted_by          INT NULL,
    approved_by           INT NULL,
    created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_switch_designer  FOREIGN KEY (designer_id)  REFERENCES designers(id) ON DELETE SET NULL,
    CONSTRAINT fk_switch_submitter FOREIGN KEY (submitted_by) REFERENCES users(id)     ON DELETE SET NULL,
    CONSTRAINT fk_switch_approver  FOREIGN KEY (approved_by)  REFERENCES users(id)     ON DELETE SET NULL,
    KEY idx_switch_type  (switch_type),
    KEY idx_sound        (sound_profile),
    KEY idx_designer     (designer_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- submissions  (mirror the switch spec fields — no payload_json, ADR-0004)
-- ---------------------------------------------------------------------------
CREATE TABLE submissions (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    user_id               INT NOT NULL,

    -- Mirrored switch fields
    name                  VARCHAR(255) NOT NULL,
    designer_id           INT NULL,
    series                VARCHAR(255) NULL,
    variant               VARCHAR(255) NULL,
    manufacturer          VARCHAR(255) NULL,
    switch_category       VARCHAR(50)  NOT NULL DEFAULT 'Mechanical MX',
    switch_type           VARCHAR(50)  NOT NULL,
    description           TEXT NULL,
    release_date          DATE NULL,

    initial_force         DECIMAL(5,1) NULL,
    actuation_force       DECIMAL(5,1) NULL,
    bottom_out_force      DECIMAL(5,1) NULL,
    tactile_force         DECIMAL(5,1) NULL,
    actuation_travel      DECIMAL(4,2) NULL,
    total_travel          DECIMAL(4,2) NULL,
    spring_length         DECIMAL(5,2) NULL,
    spring_type           VARCHAR(100) NULL,

    top_housing_material    VARCHAR(100) NULL,
    bottom_housing_material VARCHAR(100) NULL,
    stem_material           VARCHAR(100) NULL,
    stem_type               VARCHAR(100) NULL,
    contact_material        VARCHAR(100) NULL,
    pin_count               INT NULL,
    led_diffuser            ENUM('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
    rgb_support             ENUM('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
    factory_lubed           ENUM('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
    is_silent               ENUM('Yes','No','Unknown') NOT NULL DEFAULT 'Unknown',
    silent_structure        VARCHAR(255) NULL,

    sound_profile         VARCHAR(50) NOT NULL DEFAULT 'Unknown',
    feel_profile          VARCHAR(50) NOT NULL DEFAULT 'Unknown',
    recommended_use       VARCHAR(50) NOT NULL DEFAULT 'Unknown',

    image_url             VARCHAR(255) NULL,

    -- Submission lifecycle
    status                ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    reviewed_by           INT NULL,
    reviewed_at           TIMESTAMP NULL,
    created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_submission_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
    CONSTRAINT fk_submission_designer FOREIGN KEY (designer_id) REFERENCES designers(id) ON DELETE SET NULL,
    CONSTRAINT fk_submission_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id)     ON DELETE SET NULL,
    KEY idx_submission_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- blog_posts
-- ---------------------------------------------------------------------------
CREATE TABLE blog_posts (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    slug            VARCHAR(255) NOT NULL UNIQUE,
    title           VARCHAR(255) NOT NULL,
    category        VARCHAR(100) NULL,
    tags            VARCHAR(255) NULL,
    excerpt         TEXT NULL,
    content         MEDIUMTEXT NULL,
    cover_image_url VARCHAR(255) NULL,
    status          ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at    TIMESTAMP NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
