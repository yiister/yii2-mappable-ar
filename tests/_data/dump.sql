BEGIN TRANSACTION;
CREATE TABLE "config" (
  `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `key` VARCHAR(100) NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL
);

INSERT INTO `config` (`id`, `key`, `value`, `description`) VALUES
(1,	'email.username',	'test@example.com',	NULL),
(2,	'email.password',	'qwerty123',	NULL),
(3,	'email.smtp_port',	'465',	NULL),
(4,	'email.smtp_address',	'smtp.example.com',	NULL),
(5,	'email.smtp_encryption',	'ssl',	NULL);
COMMIT;