-- AccessForm Prototype Database
-- MySQL 8+

CREATE DATABASE IF NOT EXISTS accessform_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE accessform_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','creator','viewer') NOT NULL DEFAULT 'creator',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_audit (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  email VARCHAR(190) NOT NULL,
  status ENUM('success','failed') NOT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_audit_email (email),
  KEY idx_login_audit_user (user_id),
  CONSTRAINT fk_login_audit_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forms (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_forms_user (user_id),
  KEY idx_forms_active (is_active),
  CONSTRAINT fk_forms_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS form_fields (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  form_id INT UNSIGNED NOT NULL,
  field_key VARCHAR(120) NOT NULL,
  field_type ENUM('text','email','number','checkbox','radio','dropdown') NOT NULL,
  label VARCHAR(255) NOT NULL,
  is_required TINYINT(1) NOT NULL DEFAULT 0,
  options_json JSON NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_form_fields_form (form_id),
  CONSTRAINT fk_form_fields_form
    FOREIGN KEY (form_id) REFERENCES forms(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS form_responses (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  form_id INT UNSIGNED NOT NULL,
  submitted_by INT UNSIGNED NULL,
  response_json JSON NOT NULL,
  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_form_responses_form (form_id),
  KEY idx_form_responses_user (submitted_by),
  CONSTRAINT fk_form_responses_form
    FOREIGN KEY (form_id) REFERENCES forms(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_form_responses_user
    FOREIGN KEY (submitted_by) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;
