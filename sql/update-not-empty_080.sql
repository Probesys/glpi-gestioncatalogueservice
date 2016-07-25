ALTER TABLE `glpi_plugin_gestioncatalogueservices_gestioncatalogueservices` RENAME `glpi_plugin_gestioncatalogueservice_gestioncatalogueservices`;
ALTER TABLE `glpi_plugin_gestioncatalogueservices_2tickets` RENAME `glpi_plugin_gestioncatalogueservice_2tickets`;
ALTER TABLE `glpi_plugin_gestioncatalogueservices_configuration` RENAME `glpi_plugin_gestioncatalogueservice_configuration`;
ALTER TABLE `glpi_plugin_gestioncatalogueservices_profiles` RENAME `glpi_plugin_gestioncatalogueservice_profiles`;
ALTER TABLE `glpi_plugin_gestioncatalogueservice_2tickets` ADD INDEX ( `date_limite` ) ;
ALTER TABLE `glpi_plugin_gestioncatalogueservice_2tickets` ADD `is_forced` TINYINT NULL DEFAULT '0';
UPDATE `glpi_rules` SET sub_type='PluginGestioncatalogueserviceRule' , `entities_id` = '0' WHERE 
sub_type = 'RuleTicket' AND id IN
(
SELECT DISTINCT glpi_ruleactions.rules_id FROM `glpi_ruleactions`
WHERE field='catalogueservice'
);
