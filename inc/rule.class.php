<?php
/*
 * @version $Id: ruletest.class.php 143 2010-10-05 13:34:02Z walid $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

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
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


/**
* Rule class store all informations about a GLPI rule :
*   - description
*   - criterias
*   - actions
*
**/
class PluginGestioncatalogueserviceRule extends Rule {

   // From Rule
   static public $right	= 'rule_import';
   public $can_sort		= true;

   function getTitle() {
      global $LANG;

      return $LANG['plugin_gestioncatalogueservice'][32];
   }

   function maxActionsCount() {
      return 1;
   }

   function getCriterias() {
      $criterias = array();
      
      $criterias['itilcategories_id']['table']     = 'glpi_itilcategories';
      $criterias['itilcategories_id']['field']     = 'name';
      $criterias['itilcategories_id']['name']      = __("Category");
      $criterias['itilcategories_id']['linkfield'] = 'itilcategories_id';
      $criterias['itilcategories_id']['type']      = 'dropdown';
      
      $criterias['_groups_id_assign']['table']     = 'glpi_groups';
      $criterias['_groups_id_assign']['field']     = 'name';
      $criterias['_groups_id_assign']['name']      = __("Assigned to")." - ".__("Group");
      $criterias['_groups_id_assign']['linkfield'] = '_groups_id_assign';
      $criterias['_groups_id_assign']['type']      = 'dropdown';
      
      $criterias['priority']['name'] = __("Priority");
      $criterias['priority']['type'] = 'dropdown_priority';
      
      $criterias['type']['table']     = 'glpi_tickets';
      $criterias['type']['field']     = 'type';
      $criterias['type']['name']      = __("Type");
      $criterias['type']['linkfield'] = 'type';
      $criterias['type']['type']      = 'dropdown_tickettype';
      
      $criterias['_users_id_requester']['table']     = 'glpi_users';
      $criterias['_users_id_requester']['field']     = 'name';
      $criterias['_users_id_requester']['name']      = __('Requester')." - ".__('User');
      $criterias['_users_id_requester']['linkfield'] = '_users_id_requester';
      $criterias['_users_id_requester']['type']      = 'dropdown_users';
      
      $criterias['_groups_id_requester']['table']     = 'glpi_groups';
      $criterias['_groups_id_requester']['field']     = 'name';
      $criterias['_groups_id_requester']['name']      = __('Requester')." - ".__('Group');
      $criterias['_groups_id_requester']['linkfield'] = '_groups_id_requester';
      $criterias['_groups_id_requester']['type']      = 'dropdown';
      
      $criterias['slas_id']['table'] = 'glpi_slas';
      $criterias['slas_id']['field'] = 'name';
      $criterias['slas_id']['name']  = __('SLA');
      $criterias['slas_id']['linkfield'] = 'slas_id';
      $criterias['slas_id']['type']  = 'dropdown';

      return $criterias;
   }

   function getActions() {
      global $LANG;
      $actions = array();
      $actions['catalogueservice']['name']  =$LANG['plugin_gestioncatalogueservice'][30];
      $actions['catalogueservice']['type']  = 'dropdown';
      $actions['catalogueservice']['table'] = 'glpi_plugin_gestioncatalogueservice_gestioncatalogueservices';
      
      return $actions;
   }
   
   function isEntityAssign() {
      return true;
   }
}

?>
