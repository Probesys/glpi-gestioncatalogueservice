<?php

/*
 * @version $Id: hook.php 95 2009-09-04 17:02:19Z remi $
  -------------------------------------------------------------------------
  GLPI - Gestionnaire Libre de Parc Informatique
  Copyright (C) 2003-2009 by the INDEPNET Development Team.

  http://indepnet.net/   http://glpi-project.org
  -------------------------------------------------------------------------

  LICENSE

  This file is part of GLPI.

  GLPI is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  GLPI is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with GLPI; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

function plugin_gestioncatalogueservice_install() {
      global $DB, $LANG;

      $update = false;
      if (TableExists("glpi_plugin_gestion_catalogue_service") && !TableExists("glpi_plugin_gestioncatalogueservice_gestioncatalogueservices")) {
            // update de version
            $DB->runFile(GLPI_ROOT . "/plugins/gestioncatalogueservice/sql/update-not-empty.sql");
            $update = true;
      }

      if (TableExists("glpi_plugin_gestioncatalogueservices_gestioncatalogueservices") && !TableExists("glpi_plugin_gestioncatalogueservice_gestioncatalogueservices")) {
            // update de version
            $DB->runFile(GLPI_ROOT . "/plugins/gestioncatalogueservice/sql/update-not-empty_080.sql");
            $update = true;
      }

      if (!TableExists('glpi_plugin_gestioncatalogueservice_gestioncatalogueservices')) {
            $query = "CREATE TABLE  `glpi_plugin_gestioncatalogueservice_gestioncatalogueservices` (
			`id` int(11) NOT NULL auto_increment,
                        `entities_id` int(11) NOT NULL,
			`name` varchar(255) collate utf8_unicode_ci default NULL,
                        `short_description` text collate utf8_unicode_ci,
                        `long_description` text collate utf8_unicode_ci,
                        `delais_theorique_resolution` int(11) NOT NULL default '0',
                        `pourcentage_avant_envoi` int(11) NOT NULL default '0',
                        `clients_eligibles` text collate utf8_unicode_ci,
                        `demandeur` text collate utf8_unicode_ci,
                        `valideur` text collate utf8_unicode_ci,
                        `perimetre_service` text collate utf8_unicode_ci,
			PRIMARY KEY  (`id`),
                        KEY `entities_id` (`entities_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
            $DB->query($query) or die("error adding glpi_gestioncatalogueservice table " . $LANG["update"][90] . $DB->error());
      }
      if (!TableExists('glpi_plugin_gestioncatalogueservice_2tickets')) {
            $query = "CREATE TABLE  `glpi_plugin_gestioncatalogueservice_2tickets` (
                        `id_ticket` int(11) NOT NULL,
                        `id_catalogue_service` int(11) NOT NULL,
                        `date_limite` datetime default NULL,
                        `is_forced` TINYINT NULL DEFAULT '0',
                        KEY `id_ticket` (`id_ticket`),
                        KEY `id_catalogue_service` (`id_catalogue_service`),
                        KEY  `date_limite` (`date_limite`)
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
            $DB->query($query) or die("error adding glpi_plugin_gestioncatalogueservice_2tickets table " . $LANG["update"][90] . $DB->error());
      }
      // Check for is_forced field in table glpi_plugin_gestioncatalogueservice_2tickets
	  else {
			$result = mysql_query("SHOW COLUMNS FROM glpi_plugin_gestioncatalogueservice_2tickets LIKE 'is_forced'"); 
			$exists = (mysql_num_rows($result))?TRUE:FALSE;
				if (!$exists) {
					$query = "ALTER TABLE `glpi_plugin_gestioncatalogueservice_2tickets` ADD `is_forced` TINYINT NULL DEFAULT '0';";
					$DB->query($query) or die("error adding is_forced field in table glpi_plugin_gestioncatalogueservice_2tickets");
				}
	  }
	  if (!TableExists('glpi_plugin_gestioncatalogueservice_configuration')) {
            $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_gestioncatalogueservice_configuration` (
                        `heure_debut_service` float default NULL,
                        `heure_debut_pause_midi` float default NULL,
                        `heure_fin_pause_midi` float default NULL,
                        `heure_fin_service` float default NULL,
                        `nb_heure_max_list` int(11) default NULL,
                        `nb_heure_avant_bascule_heure_jour` int(11) default NULL,
                        `progressbar_colors` varchar(150) default 'a:6:{i:0;s:6:\"0011FF\";i:1;s:6:\"00F3FF\";i:2;s:6:\"05FF00\";i:3;s:6:\"FFFF00\";i:4;s:6:\"FF9700\";i:5;s:6:\"D00000\";}'
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die("error adding glpi_plugin_gestioncatalogueservice_configuration table " . $LANG["update"][90] . $DB->error());
            $query_insert = "INSERT INTO `glpi_plugin_gestioncatalogueservice_configuration` (`heure_debut_service`, `heure_debut_pause_midi`, `heure_fin_pause_midi`, `heure_fin_service`, `nb_heure_max_list`, `nb_heure_avant_bascule_heure_jour`) VALUES (7, 12, 13.5, 18, 1080, 48)";
            $DB->query($query_insert) or die("error adding defaults values into glpi_plugin_gestioncatalogueservice_configuration table " . $LANG["update"][90] . $DB->error());
      }
      // Check for progressbar_colors field in table glpi_plugin_gestioncatalogueservice_configuration
      else {
			$result = mysql_query("SHOW COLUMNS FROM glpi_plugin_gestioncatalogueservice_configuration LIKE 'progressbar_colors'"); 
			$exists = (mysql_num_rows($result))?TRUE:FALSE;
				if (!$exists) {
					$query = "ALTER TABLE `glpi_plugin_gestioncatalogueservice_configuration` ADD `progressbar_colors` varchar(150) default 'a:6:{i:0;s:6:\"0011FF\";i:1;s:6:\"00F3FF\";i:2;s:6:\"05FF00\";i:3;s:6:\"FFFF00\";i:4;s:6:\"FF9700\";i:5;s:6:\"D00000\";}';";
					$DB->query($query) or die("error adding progressbar_colors field in table glpi_plugin_gestioncatalogueservice_configuration");
				}
	  }
      if (!TableExists('glpi_plugin_gestioncatalogueservice_profiles')) {
            $query = "CREATE TABLE `glpi_plugin_gestioncatalogueservice_profiles` (
                        `id` int(11) NOT NULL auto_increment,
                        `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
                        `show_catalogue_service_onglet` char(1) collate utf8_unicode_ci default NULL,
                        PRIMARY KEY  (`id`),
                        KEY `profiles_id` (`profiles_id`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;  ";
            $DB->query($query) or die("error adding glpi_plugin_gestioncatalogueservice_profiles table " . $LANG["update"][90] . $DB->error());
            $query_insert = "INSERT INTO `glpi_plugin_gestioncatalogueservice_profiles` (`id`, `profiles_id`, `show_catalogue_service_onglet`) VALUES (1, 4, '1')";
            $DB->query($query_insert) or die("error adding defaults values into glpi_plugin_gestioncatalogueservice_profiles table " . $LANG["update"][90] . $DB->error());
      }

      return true;
}

function plugin_gestioncatalogueservice_uninstall() {
      global $DB;

      $tables = array(	"glpi_plugin_gestioncatalogueservice_gestioncatalogueservices",
						"glpi_plugin_gestioncatalogueservice_2tickets",
						"glpi_plugin_gestioncatalogueservice_configuration",
						"glpi_plugin_gestioncatalogueservice_profiles",
      );

      foreach ($tables as $table)
            $DB->query("DROP TABLE IF EXISTS `$table`;");

      plugin_init_gestioncatalogueservice();

      return true;
}

function plugin_gestioncatalogueservice_addLeftJoin($type, $ref_table, $new_table, $linkfield, &$already_link_tables) {
      switch ($new_table) {
            case "glpi_plugin_gestioncatalogueservice_2tickets" : // From Ticket
                  $out = " LEFT JOIN `glpi_plugin_gestioncatalogueservice_2tickets` ON (`$ref_table`.`id` = `glpi_plugin_gestioncatalogueservice_2tickets`.`id_ticket`) ";
                  return $out;
                  break;
      }
      return "";
}

function plugin_gestioncatalogueservice_getAddSearchOptions($itemtype) {

      global $LANG;
      $opt = array();
      if ($itemtype == "Ticket") {
            $nbitem = 610;
            $opt[$nbitem]['table'] = 'glpi_plugin_gestioncatalogueservice_2tickets';
            $opt[$nbitem]['field'] = 'date_limite';
            $opt[$nbitem]['name'] = $LANG['plugin_gestioncatalogueservice'][7];
            $opt[$nbitem]['datatype'] = 'datetime';
#            $opt[$nbitem]['forcegroupby'] = true;
            $opt[$nbitem]['itemlink_type'] = 'PluginGestionCatalogueServiceCatalogueService';
            $sopt[$nbitem]['purpose'] = 'log'; // an extra field used to clean search options
      }
      return $opt;
}

function plugin_gestioncatalogueservice_getPluginsDatabaseRelations() {
      $plugin = new Plugin();

      if ($plugin->isActivated("gestioncatalogueservice"))
            return array("glpi_plugin_gestioncatalogueservice_2tickets" => array("glpi_plugin_gestioncatalogueservice_gestioncatalogueservices" => "id_ticket"),
                "glpi_tickets" => array("glpi_plugin_gestioncatalogueservice_2tickets" => "id_ticket"),
                "glpi_profiles" => array("glpi_plugin_gestioncatalogueservice_profiles" => "profiles_id")
            );
      else
            return array();
}

//display custom fields in the search
function plugin_gestioncatalogueservice_giveItem($type, $ID, $data, $num) {
      global $CFG_GLPI, $LANG, $DB;

      $searchopt = &Search::getOptions($type);
      $table = $searchopt[$ID]["table"];
      $field = $searchopt[$ID]["field"];

      switch ($table . '.' . $field) {
            //display associated items with catalogueservice
            case "glpi_plugin_gestioncatalogueservice_2tickets.date_limite" :
                  $query = "SELECT date FROM glpi_tickets WHERE id=" . $data['id'];
                  $result = $DB->query($query);
                  if ($result) {
                        if ($DB->numrows($result)) {
                              $row = mysqli_fetch_object($result);
                              $barre = getBarreDelaisResolution($row->date, $data["ITEM_" . $num]);
                              $out = "<strong>" . Html::convDateTime($data["ITEM_" . $num]) . "</strong>" . $barre;
                              return $out;
                        }
                  } else {
                        return "";
                  }
                  break;
      }
      return "";
}

/**
 * function getBarreDelaisResolution
 * @author probesys / Philippe GODOT
 * 					  Maxime Bonillo
 * @description permet de récupérer la barre de progression relative au delais de résolution du ticket vis à vis de son état d'avancement
 * @param
 *   $date_creation_ticket la date de création du ticket
 *   $date_limite la date théorique de résolution du ticket
 * @return string contenant le code html correspondant à la barre de progression
 */
function getBarreDelaisResolution($date_creation_ticket, $date_limite) {
    $date_creation_ticket = strtotime($date_creation_ticket);
    $date_today = time();
    $date_limite = strtotime($date_limite);
    $barre = '';

    $duree_totale = $date_limite - $date_creation_ticket;
    $duree_entame = abs($date_today - $date_creation_ticket);

    if ($date_limite != '') {

      	if ($duree_totale == 0) {
      	      $pourcentage = 100;
      	} else {
      	      $pourcentage = round(($duree_entame * 100) / $duree_totale);
      	      if ($pourcentage > 100) {
	  	  		$pourcentage = 100;
	  	  	}
      	}

      	if (!isset($_SESSION['pbcolors'])) {
      	$_SESSION['pbcolors'] = array(0 => "0011FF",
	  	  						    1 => "00F3FF",
	  	  						    2 => "05FF00",
	  	  						    3 => "FFFF00",
	  	  						    4 => "FF9700",
	  	  						    5 => "D00000");
	  	}
      	$pbcolors = $_SESSION['pbcolors'];
	  	$divi = 100 / (count($pbcolors)-1);
	  	$colorkey = floor($pourcentage / $divi);
	  	if ($colorkey > count($pbcolors)-1) {
	  	    $colorkey = count($pbcolors)-1;
	  	}
	  	$bgcolor = '#' . $pbcolors[$colorkey];
	  
        $barre = '<div class="noPrint" style="border: 1px solid black; padding: 1px; height: 5px; width: 100%; text-align: left;" title="' . $pourcentage . '%">
<div style="background: ' . $bgcolor . ' none repeat scroll 0% 0%; height: 5px; width: ' . $pourcentage . '%; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;"/>
</div>';
      }
      return $barre;
}
