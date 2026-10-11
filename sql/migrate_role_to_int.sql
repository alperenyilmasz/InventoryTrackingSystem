-- Mevcut veritabanı: role sütununu ENUM'dan sayısal değere geçirir
-- 1 = yönetici (admin), 2 = normal kullanıcı (user)
-- phpMyAdmin veya mysql CLI ile bir kez çalıştırın.

SET NAMES utf8mb4;

ALTER TABLE users ADD COLUMN role_int TINYINT UNSIGNED NOT NULL DEFAULT 2 AFTER password_hash;

UPDATE users SET role_int = CASE
    WHEN role = 'admin' THEN 1
    WHEN role = '1' THEN 1
    ELSE 2
END;

ALTER TABLE users DROP COLUMN role;
ALTER TABLE users CHANGE role_int role TINYINT UNSIGNED NOT NULL DEFAULT 2 COMMENT '1=yönetici, 2=kullanıcı';
