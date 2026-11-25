-- Estructura de la base de datos (MySQL 8).
-- Es idempotente: se puede ejecutar más de una vez sin errores.

CREATE TABLE IF NOT EXISTS contacts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(254) NOT NULL,
    phone      CHAR(9)      NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- El listado se ordena por id descendente: la base asigna el id al insertar, en el mismo
    -- orden que created_at, y la clave primaria permite paginar sin un índice adicional.
    PRIMARY KEY (id),

    -- La collation *_ci no distingue mayúsculas: "Ana@Mail.com" y "ana@mail.com" chocan.
    UNIQUE KEY uq_contacts_email (email),

    -- MySQL admite varios NULL en un índice único: los contactos sin teléfono no chocan entre sí.
    UNIQUE KEY uq_contacts_phone (phone)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_0900_ai_ci;
