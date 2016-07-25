<?php

/*
 * @version $Id: HEADER 1 2010-03-03 21:49 Tsmr $
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
  // ----------------------------------------------------------------------
  // Original Author of file: CAILLAUD Xavier
  // Purpose of file: plugin catalogueservices v1.6.0 - GLPI 0.78
  // ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
      die("Sorry. You can't access directly to this file");
}

class PluginGestioncatalogueserviceProfile extends CommonDBTM {

    static function getTypeName($nb=0) {
          global $LANG;

          return $LANG['plugin_gestioncatalogueservice']['profile'][0];
    }

    static function canCreate() {
          return Session::haveRight('profile', 'w');
    }

    static function canView() {
          return Session::haveRight('profile', 'r');
    }

    //if profile deleted
    static function purgeProfiles(Profile $prof) {
          $plugprof = new self();
          $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
    }

    function getFromDBByProfile($profiles_id) {
          global $DB;

          $query = "SELECT * FROM " . $this->getTable() . " WHERE profiles_id = '" . $profiles_id . "' ";
          $result = $DB->query($query);
          if ($result) {
                if ($DB->numrows($result) != 1) {
                      // ajout dans la table
                      $query_insert = "INSERT INTO `" . $this->getTable() . "` ( `profiles_id`, `show_catalogue_service_onglet`) VALUES (" . $profiles_id . ", '0')";
                      $DB->query($query_insert) or die("error adding defaults values into `" . $this->getTable() . "` table " . $LANG["update"][90] . $DB->error());
                      $query = "SELECT * FROM `" . $this->getTable() . "`	WHERE `profiles_id` = '" . $profiles_id . "' ";
                      $DB->query($query);
                }
                $this->fields = $DB->fetch_assoc($result);
                if (is_array($this->fields) && count($this->fields)) {
                      return true;
                } else {
                      return false;
                }
          }
          return false;
    }

    static function createFirstAccess($ID) {

          $myProf = new self();
          if (!$myProf->getFromDBByProfile($ID)) {

                $myProf->add(array(
                    'profiles_id' => $ID,
                    'show_catalogue_service_onglet' => '1'));
          }
    }

    function createAccess($ID) {

          $this->add(array(
              'profiles_id' => $ID));
    }
    static function changeProfile() {

          $prof = new self();
          if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id']))
                $_SESSION["glpi_plugin_catalogueservice_profile"] = $prof->fields;
          else
                unset($_SESSION["glpi_plugin_catalogueservice_profile"]);
    }

    //profiles modification
    function showForm($ID, $options=array()) {
          global $LANG;

          if (!Session::haveRight("profile", "r"))
                return false;

          $prof = new Profile();
          if ($ID) {
                $this->getFromDBByProfile($ID);
                $prof->getFromDB($ID);
          }

          $this->showFormHeader($options);

          echo "<tr class='tab_bg_2'>";

          echo "<th colspan='4' class='center b'>" . $LANG['plugin_gestioncatalogueservice']['profile'][0] . " " . $prof->fields["name"] . "</th>";

          echo "</tr>";
          echo "<tr class='tab_bg_2'>";

          echo "<td>" . $LANG['plugin_gestioncatalogueservice']['profile'][1] . ":</td><td>";
          if ($prof->fields['interface'] != 'helpdesk') {
                Dropdown::showYesNo("show_catalogue_service_onglet", $this->fields["show_catalogue_service_onglet"]);
          } else {
                echo Dropdown::getYesNo(0);
          }
          echo "</td>";
          echo "</tr>";

          echo "<input type='hidden' name='id' value=" . $this->fields["id"] . ">";

          $options['candel'] = false;
          $this->showFormButtons($options);
    }

	function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
	  	global $LANG;
			if ($item->getType() == 'Profile') {
				return $LANG['plugin_gestioncatalogueservice'][15];
			}
	  	return '';
	}

	static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

		global $CFG_GLPI, $DB;
    	global $LANG;

		$PluginGestioncatalogueserviceProfile = new self();

		if (get_class($item) == 'Profile') {
            $PluginGestioncatalogueserviceProfile->showForm($item->getField('id'), array('target' => $CFG_GLPI["root_doc"] . "/plugins/gestioncatalogueservice/front/profile.form.php"));
	    }
	}

}

?>
