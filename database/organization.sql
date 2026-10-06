-- =========================================================
-- ORGANIZATION table
-- Matches: resources/views/backend/organization/organization.blade.php
--          OrganizationController / OrganizationModel
-- Fields: org_name, slug, address, phone_number, thumb_image, status
-- status: 1 = Active, 0 = Inactive, 9 = Deleted
-- =========================================================

-- ---------------------------------------------------------
-- MySQL / MariaDB (XAMPP)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `organization` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NULL,
  `address` TEXT NOT NULL,
  `phone_number` VARCHAR(50) NULL,
  `thumb_image` VARCHAR(500) NULL COMMENT 'Banner image path',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive, 9=Deleted',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_organization_status` (`status`),
  KEY `idx_organization_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional sample row (edit as needed)
-- INSERT INTO `organization` (`org_name`, `slug`, `address`, `phone_number`, `thumb_image`, `status`)
-- VALUES ('Padma Bank Limited', 'padma-bank-limited', 'Dhaka, Bangladesh', '16236', NULL, 1);


-- ---------------------------------------------------------
-- Oracle
-- ---------------------------------------------------------
/*
CREATE TABLE ORGANIZATION (
  ID            NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  ORG_NAME      VARCHAR2(255) NOT NULL,
  SLUG          VARCHAR2(255) NULL,
  ADDRESS       CLOB NOT NULL,
  PHONE_NUMBER  VARCHAR2(50) NULL,
  THUMB_IMAGE   VARCHAR2(500) NULL,
  STATUS        NUMBER(3) DEFAULT 1 NOT NULL,
  CREATED_AT    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UPDATED_AT    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IDX_ORGANIZATION_STATUS ON ORGANIZATION (STATUS);
CREATE INDEX IDX_ORGANIZATION_SLUG ON ORGANIZATION (SLUG);

-- Optional sample row
-- INSERT INTO ORGANIZATION (ORG_NAME, SLUG, ADDRESS, PHONE_NUMBER, THUMB_IMAGE, STATUS)
-- VALUES ('Padma Bank Limited', 'padma-bank-limited', 'Dhaka, Bangladesh', '16236', NULL, 1);
-- COMMIT;
*/
