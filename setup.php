<?php

/*
 * @version $Id: setup.php 60 2009-09-02 15:03:49Z moyo $
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
// Original Author of file: GODOT Philippe
// Purpose of file:
// ----------------------------------------------------------------------
// Init the hooks of the plugins -Needed
function plugin_init_gestioncatalogueservice() {
      global $PLUGIN_HOOKS, $CFG_GLPI, $LINK_ID_TABLE, $LANG;

	  $PLUGIN_HOOKS['csrf_compliant']['gestioncatalogueservice'] = true; //0.83.3 

      if (isset($_SESSION["glpiID"])) {
            Plugin::registerClass('PluginGestioncatalogueserviceGestioncatalogueservice', array(
                'classname'					=> 'PluginGestioncatalogueserviceGestioncatalogueservice', //nom de la classe
                'tablename' 				=> 'glpi_plugin_gestioncatalogueservice_gestioncatalogueservices', // nom de la table
                'typename' 					=> $LANG['plugin_gestioncatalogueservice'][0], // nom du type
                'formpage' 					=> 'front/gestioncatalogueservice.form.php', // formulaire de saisie
                'searchpage' 				=> 'index.php', // formulaire de recherche
                'deleted_tables' 			=> false, // gestion des deleted
                'reservation_types' 		=> false, // gestion des reservations
                'specif_entities_tables' 	=> true, // objet definis dans une entité
                'recursive_type' 			=> false, // objet recursif
                'linkuser_types' 			=> false, // Permet à l'objet d'être visible dans le menu "Mes matériels" comme objet attaché à un utilisateur (droit "Liaison avec les matériels pour la création de tickets"
                'linkgroup_types' 			=> false, // Permet à l'objet d'êtee visible dans le menu "Mes matériels" comme objet attaché à un utilisateur (droit "Voir les matériels des groupe(s) associé(s)")
                'massiveaction_noupdate' 	=> false, //Ne propose pas pour ce type de menu "Modifier" dans les modifications massives
                'massiveaction_nodelete' 	=> false, //Ne propose pas pour ce type de menu "Supprimer" dans les modifications massives
                'doc_types' 				=> false, //A partir de 0.72.1 : liaison avec les documents
                'helpdesk_visible_types' 	=> true,        //A partir de 0.72.1 : règler la visibilité d'un objet dans le helpdesk
				'addtabon' 					=> 'Ticket'
            ));
            
			Plugin::registerClass('PluginGestioncatalogueserviceProfile', array('addtabon' => 'Profile'));

            Plugin::registerClass('PluginGestioncatalogueserviceRuleCollection',
                        array('rulecollections_types' => true));

            //load changeprofile function
            $PLUGIN_HOOKS['change_profile']['gestioncatalogueservice'] = array('PluginGestioncatalogueserviceProfile', 'Session::changeProfile');

            if (Session::haveRight('config', 'w')) {
                  $PLUGIN_HOOKS['menu_entry']['gestioncatalogueservice'] = true;
                  $PLUGIN_HOOKS['submenu_entry']['gestioncatalogueservice']['add'] = 'front/gestioncatalogueservice.form.php';
                  $PLUGIN_HOOKS['submenu_entry']['gestioncatalogueservice']['search'] = 'index.php';
                  $PLUGIN_HOOKS['config_page']['gestioncatalogueservice'] = 'front/gestioncatalogueservice.config.php';
            }

            if (plugin_gestioncatalogueservice_haveRight("show_catalogue_service_onglet", "1")) {
                  // Massive Action definition
                  $PLUGIN_HOOKS['use_massive_action']['gestioncatalogueservice'] = 1;
            }

            $PLUGIN_HOOKS['item_add']['gestioncatalogueservice'] = array('Ticket' => array('PluginGestioncatalogueserviceGestioncatalogueservice', 'hookUpdateTicket'));

            $PLUGIN_HOOKS['item_update']['gestioncatalogueservice'] = array('Ticket' => array('PluginGestioncatalogueserviceGestioncatalogueservice', 'hookUpdateTicket'));

			
      }
}

// Get the name and the version of the plugin - Needed
function plugin_version_gestioncatalogueservice() {
      return array(	'name' => 'Gestion Catalogue de Service',
					'version' => '0.84.0',
					'author' => 'Philippe Godot, Maxime Bonillo',
					'license' => 'GPLv2+',
					'homepage' => 'http://www.probesys.com/',
					'minGlpiVersion' => '0.84', // For compatibility / no install in version < 0.84
      );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_gestioncatalogueservice_check_prerequisites() {
	if (version_compare(GLPI_VERSION,'0.84','lt')) {
		  echo "This plugin requires GLPI > 0.84";
		  return false;
	}
	return true;
}

// Define rights for the plugin types
function plugin_gestioncatalogueservice_haveTypeRight($type, $right) {
      global $LANG;
      switch ($type) {
            case 'plugin_gestioncatalogueservice' :
                  return Session::haveRight("profile", $right);
                  break;
      }
      return true;
}

// Check configuration process for plugin : need to return true if succeeded
// Can display a message only if failure and $verbose is true
function plugin_gestioncatalogueservice_check_config($verbose=false) {
      global $DB, $LANG;
      $query = "SELECT * FROM glpi_plugin_gestioncatalogueservice_configuration";
      $result = $DB->query($query);
      if ($result) {
            if ($DB->numrows($result) > 0) {
                  return true;
            }
      }
      if ($verbose) {
            echo $LANG['plugins'][2];
      }
      return false;
}

function plugin_gestioncatalogueservice_haveRight($module, $right) {
      global $DB;
      $query = " SELECT * FROM glpi_plugin_gestioncatalogueservice_profiles WHERE profiles_id=" . $_SESSION['glpiactiveprofile']['id'] . " AND " . $module . "=" . $right;
      $result = $DB->query($query);
      if ($result) {
            if ($DB->numrows($result) > 0) {
                  $_SESSION["glpiactiveprofile"][$module] = 1;
                  return true;
            } else {
                  $_SESSION["glpiactiveprofile"][$module] = 0;
                  return false;
            }
      } else {
            $_SESSION["glpiactiveprofile"][$module] = 0;
            return false;
      }
}
