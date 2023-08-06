ALTER TABLE `artist` 
ADD `time_create` TIMESTAMP NULL DEFAULT NULL AFTER `birth_day`, 
ADD `time_edit` TIMESTAMP NULL DEFAULT NULL AFTER `time_create`, 
ADD `admin_create` VARCHAR(40) NULL DEFAULT NULL AFTER `time_edit`, 
ADD `admin_edit` VARCHAR(40) NULL DEFAULT NULL AFTER `admin_create`, 
ADD `ip_create` VARCHAR(50) NULL DEFAULT NULL AFTER `admin_edit`, 
ADD `ip_edit` VARCHAR(50) NULL DEFAULT NULL AFTER `ip_create`; 

