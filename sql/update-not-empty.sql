ALTER TABLE `glpi_plugin_gestion_catalogue_service` RENAME `glpi_plugin_gestioncatalogueservice_gestioncatalogueservices`;
ALTER TABLE `glpi_plugin_gestion_catalogue_service2tickets` RENAME `glpi_plugin_gestioncatalogueservice_2tickets`;
ALTER TABLE `glpi_plugin_gestion_catalogue_service_configuration` RENAME `glpi_plugin_gestioncatalogueservice_configuration`;

ALTER TABLE `glpi_plugin_gestioncatalogueservice_gestioncatalogueservices` CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ;
ALTER TABLE `glpi_plugin_gestioncatalogueservice_2tickets` CHANGE `ID_catalogue_service` `id_ticket` INT( 11 ) NOT NULL
ALTER TABLE `glpi_plugin_gestioncatalogueservice_2tickets` CHANGE `ID_catalogue_service` `id_catalogue_service` INT( 11 ) NOT NULL;
ALTER TABLE `glpi_plugin_gestioncatalogueservice_2tickets` CHANGE `Date_limite` `date_limite` DATETIME NULL DEFAULT NULL ;
ALTER TABLE `glpi_plugin_gestioncatalogueservice_gestioncatalogueservices` CHANGE `FK_entities` `entities_id` INT( 11 ) NULL DEFAULT NULL ;
ALTER TABLE `glpi_plugin_gestioncatalogueservice_2tickets` ADD INDEX ( `date_limite` ) ;
ALTER TABLE `glpi_plugin_gestioncatalogueservice_2tickets` ADD `is_forced` TINYINT NULL DEFAULT '0';

 UPDATE `glpi_ruleactions` SET field = 'catalogueservice' WHERE field = 'sla' ;