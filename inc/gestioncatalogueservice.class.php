<?php

/*
 * @version $Id: plugin_room.class.php 40 2009-03-04 07:00:56Z remi $
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

class PluginGestioncatalogueserviceGestioncatalogueservice extends CommonDBTM {

      static function getTypeName($nb=0) {
            return 'PluginGestioncatalogueserviceGestioncatalogueservice';
      }

      /**
       * Get CatalogueService Name
       *
       * @param $value catalogueservice ID
       */
      static function getCatalogueServiceName($value) {
            global $DB;
            $query = "SELECT * FROM glpi_plugin_gestioncatalogueservice_gestioncatalogueservices WHERE id=" . $value;
            $result = $DB->query($query);
            if ($DB->numrows($result) > 0) {
                  $data = $DB->fetch_array($result);
                  $value = $data["name"];
            }
            return $value;
      }
      /**
       * hookUpdateTicket : function call when a ticket is modifed to affect a catalogueservice if a rule match
       * @global type $DB
       * @param Ticket $ticket
       * @return type 
       */
      static function hookUpdateTicket(Ticket $ticket) {
            global $DB;
            
            $entities_condition = "";
            if($ticket->getField('entities_id')){
                  $entities_condition ="AND glpi_rules.entities_id =0 OR glpi_rules.entities_id = " . $ticket->getField('entities_id');
            }

            $query = "SELECT * FROM glpi_rules
                    LEFT JOIN glpi_rulecriterias ON glpi_rules.id=glpi_rulecriterias.rules_id
                    LEFT JOIN glpi_ruleactions ON glpi_rules.id=glpi_ruleactions.rules_id
                    WHERE sub_type='PluginGestioncatalogueserviceRule' AND glpi_rules.is_active=1 AND (
                    (criteria='itilcategories_id' AND pattern=" . $ticket->getField('itilcategories_id') . ") 
                          OR (criteria='groups_id' AND pattern='" . $ticket->getField('groups_id') . "') 
                          OR (criteria='priority' AND pattern='" . $ticket->getField('priority') . "') 
                          OR (criteria='type' AND pattern=" . $ticket->getField('type') . ") 
                          OR (criteria='_users_id_requester' AND pattern=" . $ticket->getField('users_id_recipient') . ")
                          AND glpi_ruleactions.field='catalogueservice'
                           " .$entities_condition."      
                                                    
                     )";            
            $result = $DB->query($query);
            $dont_touch_forced = 0;
            $forced_date_query = "SELECT is_forced FROM glpi_plugin_gestioncatalogueservice_2tickets WHERE id_ticket=" . $ticket->getField('id');
            $result_forced = $DB->query($forced_date_query);
            
            if ($DB->numrows($result_forced)) {
				$row_forced = mysqli_fetch_object($result_forced);
				$dont_touch_forced = $row_forced->is_forced;  
			}
            if ($result) {
                  if ($DB->numrows($result)) { // one rule exist and match
                        $row = mysqli_fetch_object($result);
                        $donnees = array(
                        "id" => $ticket->getField('id'),
                        "catalogueservice_id" => $row->value,
                        "date_ticket" => $ticket->getField('date'),
                        "dont_touch_forced" => $dont_touch_forced
                        );
                  } else { //no rule exist and match
                        $donnees = array(
                        "id" => $ticket->getField('id'),
                        "catalogueservice_id" => 0,
                        "date_ticket" => '',
                        "dont_touch_forced" => $dont_touch_forced
                        );
                  }
                  $catalogueservice = new self();
                  $catalogueservice->addorupdateCatalogueService2Tickets($donnees);
            }
			return true;
      }

      /**
       * Dropdown of ticket catalogueservice
       *
       * @param $name select name
       * @param $value default value
       * @param $complete see also at least selection (major included)
       *
       * @return string id of the select
       */
      static function dropdownCatalogueService($name, $value=0, $complete=false) {
            global $LANG, $CFG_GLPI;
            $plugingestioncatalogueservice = new self();
            $search = $plugingestioncatalogueservice->find();
            sort($search);


            $id = "select_$name" . mt_rand();
            echo "<select id='$id' name='$name'>";

            foreach ($search as $catalogueservice) {
                  echo "<option value=" . $catalogueservice['id'] . ">" . $catalogueservice['name'] . "</option>";
            }

            echo "</select>";

            return $id;
      }

      static function getIndexName() {
            return "name";
      }

      function isEntityAssign() {
            return true;
      }

      function getSearchOptions() {
            global $LANG;

            $tab = array();
            $tab['common'] = $LANG['plugin_gestioncatalogueservice'][15];

            $tab[0]['table'] = $this->getTable();
            $tab[0]['field'] = 'id';
            $tab[0]['linkfield'] = 'id';
            $tab[0]['name'] = 'id_ticket';

            $tab[1]['table'] = $this->getTable();
            $tab[1]['field'] = 'name';
            $tab[1]['linkfield'] = 'name';
            $tab[1]['name'] = $LANG['plugin_gestioncatalogueservice'][4];
            $tab[1]['datatype'] = 'itemlink';
            //$tab[1]['usehaving'] = true;

            $tab[2]['table'] = $this->getTable();
            $tab[2]['field'] = 'delais_theorique_resolution';
            $tab[2]['linkfield'] = 'delais_theorique_resolution';
            $tab[2]['name'] = $LANG['plugin_gestioncatalogueservice'][7];
            $tab[2]['datatype'] = 'itemlink';

            return $tab;
      }

      function addCatalogueService($POST) {
            global $DB;
            $entities_id = "0";
            if (array_key_exists("glpiactive_entity", $_SESSION)) {
                  $entities_id = $_SESSION["glpiactive_entity"];
            }
            $name = $POST['gestioncatalogueservice_name'];
            $short_description = $POST['gestioncatalogueservice_short_description'];
            $long_description = $POST['gestioncatalogueservice_long_description'];
            $delais_theorique_resolution = $POST['gestioncatalogueservice_delais_theorique'];
            $pourcentage_avant_envoi = $POST['gestioncatalogueservice_pourcentage_avant_envoi'];
            $clients_eligibles = $POST['gestioncatalogueservice_clients_eligibles'];
            $demandeur = $POST['gestioncatalogueservice_demandeur'];
            $valideur = $POST['gestioncatalogueservice_valideur'];
            $perimetre_service = $POST['gestioncatalogueservice_perimetre_service'];
            $query = "INSERT INTO " . $this->getTable() . "(entities_id,name,short_description,long_description,delais_theorique_resolution,pourcentage_avant_envoi,clients_eligibles,demandeur,valideur,perimetre_service) VALUES ( $entities_id,'$name','$short_description','$long_description',$delais_theorique_resolution,  $pourcentage_avant_envoi, '$clients_eligibles','$demandeur','$valideur','$perimetre_service')";
            $result = $DB->query($query);
            $newid = $DB->insert_id();
            return($newid);
      }

      public function updateCatalogueService($POST) {
            global $DB;
            $id = $POST['id'];
            $name = $POST['gestioncatalogueservice_name'];
            $short_description = $POST['gestioncatalogueservice_short_description'];
            $long_description = $POST['gestioncatalogueservice_long_description'];
            $delais_theorique_resolution = $POST['gestioncatalogueservice_delais_theorique'];
            $pourcentage_avant_envoi = $POST['gestioncatalogueservice_pourcentage_avant_envoi'];
            $clients_eligibles = $POST['gestioncatalogueservice_clients_eligibles'];
            $demandeur = $POST['gestioncatalogueservice_demandeur'];
            $valideur = $POST['gestioncatalogueservice_valideur'];
            $perimetre_service = $POST['gestioncatalogueservice_perimetre_service'];
            $query = "UPDATE " . $this->getTable() . " SET name='$name', short_description='$short_description', long_description='$long_description', delais_theorique_resolution='$delais_theorique_resolution', pourcentage_avant_envoi='$pourcentage_avant_envoi', clients_eligibles='$clients_eligibles',demandeur='$demandeur',valideur='$valideur',perimetre_service='$perimetre_service' WHERE id=" . $id;
            $result = $DB->query($query);
            return($id);
      }

      public function deleteCatalogueService($POST) {
            global $DB;
            $id = $POST['id'];
            $name = $POST['gestioncatalogueservice_name'];
            $result = $DB->query("DELETE FROM glpi_plugin_gestioncatalogueservice_gestioncatalogueservices WHERE id=" . $id);
            $result = $DB->query("DELETE FROM glpi_plugin_gestioncatalogueservice_2tickets WHERE id_catalogue_service=" . $id);
            $result = $DB->query("DELETE FROM glpi_ruleactions WHERE field='catalogueservice' AND value=" . $id);
            return($name);
      }

      public function updateCatalogueServiceConfiguration($POST) {
            global $DB;
            $heure_debut_service = $POST['gestioncatalogueservice_heure_debut_service'];
            $heure_debut_pause_midi = $POST['gestioncatalogueservice_heure_debut_pause_midi'];
            $heure_fin_pause_midi = $POST['gestioncatalogueservice_heure_fin_pause_midi'];
            $heure_fin_service = $POST['gestioncatalogueservice_heure_fin_service'];
            $nb_heure_max_list = $POST['gestioncatalogueservice_nb_heure_max_list'];
            $nb_heure_avant_bascule_heure_jour = $POST['gestioncatalogueservice_nb_heure_avant_bascule_heure_jour'];
            $_SESSION['pbcolors'] = array(	0 => $POST['pbcolor0'],
											1 => $POST['pbcolor1'],
											2 => $POST['pbcolor2'],
											3 => $POST['pbcolor3'],
											4 => $POST['pbcolor4'],
											5 => $POST['pbcolor5']);
			if (!isset($_SESSION['pbcolors'])) {
				$_SESSION['pbcolors'] = array(	0 => "0011FF",
												1 => "00F3FF",
												2 => "05FF00",
												3 => "FFFF00",
												4 => "FF9700",
												5 => "D00000");
			}
            //$color = $_SESSION['pbcolors'];
            $pbcolors = serialize($_SESSION['pbcolors']);
            $query = "UPDATE glpi_plugin_gestioncatalogueservice_configuration SET
                             heure_debut_service='$heure_debut_service',
                             heure_debut_pause_midi='$heure_debut_pause_midi',
                             heure_fin_pause_midi='$heure_fin_pause_midi',
                             heure_fin_service='$heure_fin_service',
                             nb_heure_max_list='$nb_heure_max_list',
                             nb_heure_avant_bascule_heure_jour='$nb_heure_avant_bascule_heure_jour',
                             progressbar_colors='$pbcolors'";
            $result = $DB->query($query);
            return($result);
      }

      public function addorupdateCatalogueService2Tickets($POST) {
            global $DB;
            $id_ticket = $POST['id'];
            $id_catalogue = $POST['catalogueservice_id'];
			if (array_key_exists("dont_touch_forced", $POST)) {
                  $dtf = $POST['dont_touch_forced'];
            } else {
                  $dtf = 0;
            }
            if (array_key_exists("date_ticket", $POST)) {
                  $date_ticket = $POST['date_ticket'];
            } else {
                  $date_ticket = $this->getDateForTicket($id_ticket);
            }
            if ($id_catalogue != 0) {
            $delais_theorique_resolution = $this->getDelaisTheoriqueResolutionForCatalogueService($id_catalogue);
			} else {
				$delais_theorique_resolution = 0;
			}
            $date_limite = $this->getDateLimite($date_ticket, $delais_theorique_resolution);
            $query = "SELECT * FROM glpi_plugin_gestioncatalogueservice_2tickets WHERE id_ticket=" . $id_ticket;
            $result = $DB->query($query);
            $num_rows = mysqli_num_rows($result);
            if ($num_rows) {
                  if ($id_catalogue == 0) { //suppression
                        $query = "DELETE FROM glpi_plugin_gestioncatalogueservice_2tickets WHERE id_ticket=$id_ticket";
                  } else { // mise a jour
                        $array_result = mysqli_fetch_array($result);
                        $is_forced = $array_result['is_forced'];
                        if(/*!$is_forced OR */$id_catalogue){
							if ($dtf) {
							  $query = "UPDATE glpi_plugin_gestioncatalogueservice_2tickets SET id_catalogue_service=$id_catalogue WHERE id_ticket=$id_ticket";
							}
							else {
								if (!$is_forced) {
	                              $query = "UPDATE glpi_plugin_gestioncatalogueservice_2tickets SET id_catalogue_service=$id_catalogue, date_limite='$date_limite'  WHERE id_ticket=$id_ticket";
	                              
	                            } else {
								$query = "UPDATE glpi_plugin_gestioncatalogueservice_2tickets SET id_catalogue_service=$id_catalogue, date_limite='$date_limite', is_forced='0' WHERE id_ticket=$id_ticket";
								}
						     } 
                        }
                  }
            } elseif ($id_catalogue != 0) { // insertion
                  $query = "INSERT INTO glpi_plugin_gestioncatalogueservice_2tickets VALUES ($id_ticket,$id_catalogue,'$date_limite','0')";
            }
            
            $DB->query($query) or die("error updating or adding glpi_plugin_gestioncatalogueservice_2tickets table " . $DB->error());
            return ($id_ticket);
      }

      public function forceDateLimiteCatalogueService2Tickets($POST) {
            global $DB;
            $id_ticket = $POST['id'];
            $date_limite = $POST['date_limite'];
            $query = "UPDATE glpi_plugin_gestioncatalogueservice_2tickets SET date_limite='$date_limite', is_forced=1 WHERE id_ticket=$id_ticket";
            $DB->query($query) or die("error forcing date limit in glpi_plugin_gestioncatalogueservice_2tickets table " . $DB->error());
            return ($id_ticket);
      }

      private function getDateLimite($date_ticket, $delais_theorique_resolution) {
            global $DB;
            /* initialisation */
            $params = $this->getParams();
            $heure_debut_service = $params['heure_debut_service'];
            $heure_debut_pause_midi = $params['heure_debut_pause_midi'];
            $heure_fin_pause_midi = $params['heure_fin_pause_midi'];
            $heure_fin_service = $params['heure_fin_service'];
            $temps_max_journee = $params['temps_max_journee'];
            $time_debut = strtotime($date_ticket);
            // verification pour eviter le cas particulier lorsqu'un ticket est entré après la fermeture du service
            // si c'est le cas, on calcul le délais de résolution à partir du lendemain à l'ouverture du service
            $time_fin_journee = mktime($heure_fin_service, 0, 0, intval(date('m', $time_debut)), intval(date('d', $time_debut)), intval(date('Y', $time_debut)));
            if ($time_debut > $time_fin_journee) {
                  $date_ticket = date('Y-m-d H:i:s', mktime($heure_debut_service, 0, 0, intval(date('m', $time_debut)), intval(date('d', $time_debut) + 1), intval(date('Y', $time_debut))));
            }
            $time_debut = strtotime($date_ticket);
            $time_finale = $time_debut;
            // 1ere etape, regarder si la durée théorique est supérieur a une journée
            if ($delais_theorique_resolution / $temps_max_journee <= 1) {
                  $time_finale = $this->getNextDayForTicket($date_ticket, $delais_theorique_resolution, $heure_debut_service, $heure_debut_pause_midi, $heure_fin_pause_midi, $heure_fin_service, $temps_max_journee);
            } else {
                  $nb_jour = intval(abs($delais_theorique_resolution / $temps_max_journee));
                  $reste = (abs($delais_theorique_resolution / $temps_max_journee) - $nb_jour) * $temps_max_journee;
                  for ($i = 0; $i < $nb_jour; $i++) {
                        $time_finale = $time_finale + 86400;
                        while ($this->dateCheckFree($time_finale)) {
                              $time_finale = $time_finale + 86400; // on decale d'une journée
                        }
                  }
                  $time_finale = date('Y-m-d H:i:s', $time_finale);
                  $time_finale = $this->getNextDayForTicket($time_finale, $reste, $heure_debut_service, $heure_debut_pause_midi, $heure_fin_pause_midi, $heure_fin_service, $temps_max_journee);
            }
            return date('Y-m-d H:i:s', $time_finale);
      }

      private function getNextDayForTicket($date_ticket, $delais_theorique_resolution, $heure_debut_service, $heure_debut_pause_midi, $heure_fin_pause_midi, $heure_fin_service, $temps_max_journee) {
            $pause_midi = $heure_fin_pause_midi - $heure_debut_pause_midi;
            $time_midi = $pause_midi * 3600;
            $date_ticket = strtotime($date_ticket);
            $time_debut_pause = mktime($heure_debut_pause_midi, 0, 0, intval(date('m', $date_ticket)), intval(date('d', $date_ticket)), intval(date('Y', $date_ticket)));
            $time_fin_journee = mktime($heure_fin_service, 0, 0, intval(date('m', $date_ticket)), intval(date('d', $date_ticket)), intval(date('Y', $date_ticket)));
            $delais_theorique_resolution_seconde = $delais_theorique_resolution * 3600;
            if ($date_ticket <= $time_debut_pause) {// on est le matin
                  if (($time_debut_pause - $date_ticket) >= $delais_theorique_resolution_seconde) {//la durée du ticket ne dépasse pas la pause
                        $time_finale = $date_ticket + $delais_theorique_resolution_seconde;
                  } else { // sinon, on ajoute le temps de pause au délais de résolution
                        $delais_theorique_resolution_seconde = $delais_theorique_resolution_seconde + $time_midi;
                        if (($date_ticket + $delais_theorique_resolution_seconde) <= $time_fin_journee) {// on reste encore dans la même journée
                              $time_finale = $date_ticket + $delais_theorique_resolution_seconde;
                        } else {
                              // on passe a la journée suivante
                              $diff_time = ($date_ticket + $delais_theorique_resolution_seconde - $time_fin_journee);
                              $time_finale = $diff_time + ($heure_debut_service * 3600) + mktime(0, 0, 0, intval(date('m', $date_ticket)), intval(date('d', $date_ticket)) + 1, intval(date('Y', $date_ticket)));
                              while ($this->dateCheckFree($time_finale)) {
                                    $time_finale = $time_finale + 86400; // on decale d'une journée
                              }
                        }
                  }
            } else {
                  // on est dans l'apres midi
                  if (($time_fin_journee - $date_ticket) > $delais_theorique_resolution_seconde) {// la durée du ticket ne dépasse pas la fin de la journée
                        $time_finale = $date_ticket + $delais_theorique_resolution_seconde;
                  } else {
                        $reste = ($delais_theorique_resolution_seconde - ($time_fin_journee - $date_ticket)) / 3600;
                        $jour_suivant = mktime($heure_debut_service, 0, 0, intval(date('m', $date_ticket)), intval(date('d', $date_ticket) + 1), intval(date('Y', $date_ticket)));
                        while ($this->dateCheckFree($jour_suivant)) {
                              $jour_suivant = $jour_suivant + 86400; // on decale d'une journée
                        }
                        $date_ticket = date('Y-m-d H:i:s', $jour_suivant);
                        $time_finale = $this->getNextDayForTicket($date_ticket, $reste, $heure_debut_service, $heure_debut_pause_midi, $heure_fin_pause_midi, $heure_fin_service, $temps_max_journee);
                  }
            }
            return($time_finale);
      }

      private function getDateForTicket($id) {
            global $DB;
            $query = "SELECT date FROM glpi_tickets WHERE id=" . $id;
            $result = $DB->query($query);
            if ($result) {
                  $row = $DB->fetch_assoc($result);
                  $date_ticket = $row['date'];
            } else {
                  die("error during query " . $query . " " . $DB->error());
            }
            return($date_ticket);
      }

      private function getDelaisTheoriqueResolutionForCatalogueService($id) {
            global $DB;
            $query = "SELECT delais_theorique_resolution FROM " . $this->getTable() . " WHERE id=" . $id;
            $result = $DB->query($query);
            if ($result) {
                  $row = $DB->fetch_assoc($result);
                  $delais_theorique_resolution = $row['delais_theorique_resolution'];
            } else {
                  die("error during query " . $query . " " . $DB->error());
            }
            return($delais_theorique_resolution);
      }

      private function dateCheckFree($date) {
            // Dimanche(0) ou Samedi(6)
            if (date('w', $date) == 0 || date('w', $date) == 6) {
                  return 1;
            }
            $jour = date('d', $date);
            $mois = date('m', $date);
            $annee = date('Y', $date);

            if ($jour == 1 && $mois == 1)
                  return 1; // 1er janvier
            if ($jour == 1 && $mois == 5)
                  return 1; // 1er mai
            if ($jour == 8 && $mois == 5)
                  return 1; // 5 mai
            if ($jour == 14 && $mois == 7)
                  return 1; // 14 juillet
            if ($jour == 15 && $mois == 8)
                  return 1; // 15 aout
            if ($jour == 1 && $mois == 11)
                  return 1; // 1er novembre
            if ($jour == 11 && $mois == 11)
                  return 1; // 11 novembre
            if ($jour == 25 && $mois == 12)
                  return 1; // 25 décembre        
                  
			// Pâques
            $date_paques = @easter_date($annee);
            $jour_paques = date('d', $date_paques);
            $mois_paques = date('m', $date_paques);
            if ($jour_paques == $jour && $mois_paques == $mois) {
                  return 1;
            }
            // Ascension
            $date_ascension = $this->dateAddDay($date_paques, 39);
            if (date('d', $date_ascension) == $jour && date('m', $date_ascension) == $mois) {
                  return 1;
            }
            // Pentecote
            $date_pentecote = $this->dateAddDay($date_paques, 50);
            if (date('d', $date_pentecote) == $jour && date('m', $date_pentecote) == $mois) {
                  return 1;
            }
            return 0;
      }

      private function dateAddDay($date, $nb_jour) {
            $date_finale = $date + ($nb_jour * 86400);
            return($date_finale);
      }

      private function getParams() {
            global $DB;
            $params = array();
            /* initialisation */
            $query = "SELECT * from glpi_plugin_gestioncatalogueservice_configuration";
            $result = $DB->query($query);
            if ($result) {
                  $row = $DB->fetch_assoc($result);
                  $params['heure_debut_service'] = $row['heure_debut_service'];
                  $params['heure_debut_pause_midi'] = $row['heure_debut_pause_midi'];
                  $params['heure_fin_pause_midi'] = $row['heure_fin_pause_midi'];
                  $params['heure_fin_service'] = $row['heure_fin_service'];
                  $params['temps_max_journee'] = $row['heure_fin_service'] - $row['heure_debut_service'] - ($row['heure_fin_pause_midi'] - $row['heure_debut_pause_midi']);
                  $params['nb_heure_avant_bascule_heure_jour'] = $row['nb_heure_avant_bascule_heure_jour'];
                  $params['pbcolors'] = $row['progressbar_colors'];
                  
            } else {
                  die("Aucun paramètres spécifiés pour le plugin de gestion de catalogues de service");
            }
            return $params;
      }

	// affichage des onglets 0.84
	function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
	      global $LANG;
	
	      if (plugin_gestioncatalogueservice_haveRight("show_catalogue_service_onglet", "1") && (get_class($item) == 'Ticket') && $item->fields["id"] != null) { 
	            return $LANG['plugin_gestioncatalogueservice'][15]; 
	      }   
	      return false;
	}
	
	static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

		global $CFG_GLPI, $DB;
		global $LANG;

		echo "<div align='center'>";
        // Initialise les libellés :
        $title = $LANG['plugin_gestioncatalogueservice'][18];
        $but_label = $LANG['plugin_gestioncatalogueservice'][18];
        $but_name = "modify_relation";
        $id = $item->getField('id');

        $query = "SELECT * FROM glpi_plugin_gestioncatalogueservice_2tickets WHERE id_ticket=" . $id;
        $result = $DB->query($query);
        if ($result) {
              $row = $DB->fetch_assoc($result);
              $is_forced = $row['is_forced'];
              $id_catalogue_service = $row['id_catalogue_service'];
              // Don't display date as forced if resolution date is not forced
              $date_limite = $row['date_limite'];
              $date_limite_forced = $date_limite;
              if (!$is_forced) {
                        $date_limite_forced = null;
              }
        } else {
              die("error during query " . $query . " " . $DB->error());
        }

        // Récupération de la liste des catalogue de service :
        $query = "SELECT * FROM glpi_plugin_gestioncatalogueservice_gestioncatalogueservices";
        $result = $DB->query($query);
        if ($result) {
             if ($DB->numrows($result) > 0) {
                 // affichage de la liste des catalogue de service
                 echo "<form action=\"" . $CFG_GLPI["root_doc"] . "/plugins/gestioncatalogueservice/front/gestioncatalogueservice.form.php?id=" . $id . "\" method='post'>\n";
                 echo "<input type='hidden' name='id' value='$id'>";
                 echo "<table class='tab_cadre' style='margin: 5; margin-top: 5px;'>\n";
                 echo " <tr class='tab_bg_1'>\n";
                 echo " <td align='center'> <select name='catalogueservice_id'>\n";
                 echo " <option value='0'> --------------------- </option>\n";
                 while ($row = $DB->fetch_assoc($result)) {
                       $selected = '';
                       if ($row['id'] == $id_catalogue_service) {
                             $selected = 'selected';
                       }
                       echo "<option " . $selected . " value=" . $row['id'] . ">" . $row['name'] . " ( " . $row['delais_theorique_resolution'] . " )</option>\n";
                 }
                 echo " </select></td>\n";
                 echo " </tr>\n";
                 echo " <tr class='tab_bg_1'>\n";
                 echo " <td align='center'>" . $LANG['plugin_gestioncatalogueservice'][26] . " : " . Html::convDateTime($date_limite);
                 echo "</td>\n";
				 echo " </tr>\n";
                 echo " <tr class='tab_bg_2'>\n";
                 echo " <td align='center'>\n";
                 echo "<input type='submit' name='$but_name' class='submit' value='$but_label'></td>\n";
                 echo " </tr>\n";
                 echo "</table>\n";
                 Html::closeForm();

                 $but_name = "force_date";
                 $but_label = $LANG['plugin_gestioncatalogueservice'][28];
                 echo " <form action=\"" . $CFG_GLPI["root_doc"] . "/plugins/gestioncatalogueservice/front/gestioncatalogueservice.form.php?id=" . $id . "\" method='post'>\n";
                 echo " <input type='hidden' name='id' value='$id'>";
                 echo " <table class='tab_cadre' style='margin: 5; margin-top: 5px;'>\n";
                 echo " <tr class='tab_bg_1'>\n";
                 echo " <td align='center'>" . $LANG['plugin_gestioncatalogueservice'][27] . " : </td>\n";
                 echo " </tr>\n";
                 echo " <tr class='tab_bg_1'>\n";
                 echo " <td align='center' style=\"width:80px;\">";
                 Html::showDateTimeFormItem("date_limite", $date_limite_forced, 1, true, true);
                 echo " </td>\n";
                 echo " </tr>\n";
                 echo " <tr class='tab_bg_2'>\n";
                 echo " <td align='center'>\n";
                 echo " <input type='submit' name='$but_name' class='submit' value='$but_label'></td>\n";
                 echo " </tr>\n";
                 echo " </table>\n";
                 Html::closeForm();

			} else {
            	echo $LANG['plugin_gestioncatalogueservice'][19];
            }
        }

        echo "</div>\n";
	}

}
