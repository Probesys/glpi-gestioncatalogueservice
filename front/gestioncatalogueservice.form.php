<?php
include_once ("../../../inc/includes.php");

global $CFG_GLPI, $DB;
global $LANG;

if(!isset($_GET["id"])) $_GET["id"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

//checkRight('show_catalogueserviceonglet','1');

$catalogueservice = new PluginGestioncatalogueserviceGestioncatalogueservice();

if (isset($_POST["modify_relation"])) {
        $id_tickets = $catalogueservice->addorupdateCatalogueService2Tickets($_POST);
        if ($_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
            Html::redirect($CFG_GLPI["root_doc"]."/front/helpdesk.public.php?show=user&id=".$id_tickets);
        }
        else
         {
            Html::redirect($CFG_GLPI["root_doc"]."/front/ticket.form.php?id=".$id_tickets);
         }
}
elseif (isset($_POST["force_date"])) {
        $id_tickets = $catalogueservice->forceDateLimiteCatalogueService2Tickets($_POST);
        if ($_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
            Html::redirect($CFG_GLPI["root_doc"]."/front/helpdesk.public.php?show=user&id=".$id_tickets);
        }
        else
         {
            Html::redirect($CFG_GLPI["root_doc"]."/front/ticket.form.php?id=".$id_tickets);
         }
}
elseif (isset($_POST["add"])) {
        $newid = $catalogueservice->addCatalogueService($_POST);
        Html::redirect($CFG_GLPI["root_doc"]."/plugins/gestioncatalogueservice/index.php");
}
elseif (isset($_POST["modify"])) {
        $newid = $catalogueservice->updateCatalogueService($_POST);
        Html::redirect($CFG_GLPI["root_doc"]."/plugins/gestioncatalogueservice/index.php");
}
elseif (isset($_POST["delete"])) {
        $name= $catalogueservice->deleteCatalogueservice($_POST);
        Html::redirect($CFG_GLPI["root_doc"]."/plugins/gestioncatalogueservice/index.php");
}
else
{
        // insertion de l'entête générale
        Html::header($LANG['plugin_gestioncatalogueservice'][0],$_SERVER['PHP_SELF'],"plugins","gestioncatalogueservice");
        // Initialise les libellés :
        $title = $LANG['plugin_gestioncatalogueservice'][16];
        $but_label = $LANG['plugin_gestioncatalogueservice'][16];
        $but_name = "add";
        $name = "";
        $short_description = "";
        $long_description = "";
        $delais_theorique_resolution = "";
        //$urgence = "";
        $pourcentage_avant_envoi = "";
        $clients_eligibles = "";
        $demandeur = "";
        $valideur = "";
        $perimetre_service = "";
      
        if (isset($_GET["id"]))
        {
            $newid = $_GET["id"];
        }
        // Lit les informations actuelles :
        if (isset($newid) AND $newid!=""){
              $query = "SELECT * FROM glpi_plugin_gestioncatalogueservice_gestioncatalogueservices WHERE id=".$newid;
              $result = $DB->query($query);
              if ($result){
                if ($DB->numrows($result) > 0) {
                        $row = $DB->fetch_assoc($result);
                        // actions
                        $title = $LANG['plugin_gestioncatalogueservice'][17];
                        $but_label = $LANG['plugin_gestioncatalogueservice'][17];
                        $but_name = "modify";
                        // fields
                        $name = $row['name'];
                        $short_description = $row['short_description'];
                        $long_description = $row['long_description'];
                        $delais_theorique_resolution = $row['delais_theorique_resolution'];
                        //$urgence = $row['urgence'];
                        $pourcentage_avant_envoi = $row['pourcentage_avant_envoi'];
                        $clients_eligibles = $row['clients_eligibles'];
                        $demandeur = $row['demandeur'];
                        $valideur = $row['valideur'];
                        $perimetre_service = $row['perimetre_service'];
                }
              }
        }
  // Affichage du formulaire :
  echo "<div align='center' id='tabsbody' name='form'>";
  echo "<form action=\"".$CFG_GLPI["root_doc"]."/plugins/gestioncatalogueservice/front/gestioncatalogueservice.form.php\" method='post'>\n";

  if (isset($newid)){
  echo "<input type='hidden' name='id' value='".$newid."'>";
  }
  echo "<table class='tab_cadre_fix' cellpadding='2'>\n";
  echo " <tr class='tab_bg_2'><td colspan='2' align='center'>$name</td></tr>\n";
  echo " <tr class='tab_bg_1'>\n";
  echo " <td>".$LANG['plugin_gestioncatalogueservice'][4]." : </td>\n";
  echo " <td><input type='text' name='gestioncatalogueservice_name' size='80' value=\"$name\" /></td>\n";
  echo " </tr>\n";
  echo " <tr class='tab_bg_1'>\n";
  echo " <td>".$LANG['plugin_gestioncatalogueservice'][5]." : </td>";
  echo " <td><textarea name='gestioncatalogueservice_short_description' rows='4' cols='80'>".$short_description."</textarea></td>\n";
  echo " </tr>\n";
  echo " <tr class='tab_bg_1'>\n";
  echo " <td>".$LANG['plugin_gestioncatalogueservice'][6]." : </td>";
  echo " <td><textarea name='gestioncatalogueservice_long_description' rows='4' cols='80'>".$long_description."</textarea></td>\n";
  echo " </tr>\n";
  echo " <tr class='tab_bg_1'>\n";
  echo " <td>".$LANG['plugin_gestioncatalogueservice'][7]." : </td>";
  echo " <td> <select name='gestioncatalogueservice_delais_theorique'>";
  
  $result = $DB->query("SELECT * FROM glpi_plugin_gestioncatalogueservice_configuration");
  if ($result){
        $row = $DB->fetch_assoc($result);
        $nb_heure_max = $row['nb_heure_max_list'];;
        $temps_max_journee = $row['heure_fin_service'] - $row['heure_debut_service'] - ($row['heure_fin_pause_midi'] - $row['heure_debut_pause_midi']);
  }
  else
  {
    die("Aucun paramètres spécifiés pour le plugin de gestion de catalogues de service");
  }

  $nb_heure_max++;
  $nb_heure_max_avant_affichage_jour = $row['nb_heure_avant_bascule_heure_jour'];
  $unite = " heures";
  $i=0;
  echo $nb_heure_max;
  while($i<$nb_heure_max)
  {
        $selected='';
        $j=$i;
        if($i>$nb_heure_max_avant_affichage_jour)
        {
             $unite = " jours";
             $j=ceil($j/$temps_max_journee);
             $i=$j*$temps_max_journee;
        }
        if($i==$delais_theorique_resolution)
        $selected = 'selected';
        echo "<option ".$selected." value='".$i."'>".$j." ".$unite."</option>";
        $i++;
  }
  echo " </select> (en heures)</td>\n";
  echo " </tr>\n";
  echo " <tr class='tab_bg_1'>\n";
  echo " <td>".$LANG['plugin_gestioncatalogueservice'][10]." : </td>\n";
  echo " <td> <select name='gestioncatalogueservice_pourcentage_avant_envoi'>\n";
  for($i=0;$i<110;$i=$i+10)
  {
         $selected='';
        if($pourcentage_avant_envoi==$i)
        $selected = 'selected';
        echo "<option ".$selected." value='$i'>".$i." %</option>\n";
  }
  echo " </select></td>\n";
  echo " </tr>\n";
  echo " <tr class='tab_bg_1'>\n";
  echo " <td>".$LANG['plugin_gestioncatalogueservice'][11]." : </td>\n";
  echo " <td><textarea name='gestioncatalogueservice_clients_eligibles' rows='4' cols='80'>".$clients_eligibles."</textarea></td>\n";
  echo " </tr>\n";
  echo " <tr class='tab_bg_1'>\n";
  echo " <td>".$LANG['plugin_gestioncatalogueservice'][12]." : </td>\n";
  echo   "<td><input type='text' name='gestioncatalogueservice_demandeur' size='80' value=\"$demandeur\"></td>\n";
  echo " </tr>\n";
  echo " <tr class='tab_bg_1'>\n";
  echo " <td>".$LANG['plugin_gestioncatalogueservice'][13]." : </td>\n";
  echo " <td><input type='text' name='gestioncatalogueservice_valideur' size='80' value=\"$valideur\"></td>\n";
  echo " </tr>\n";
  echo " <tr class='tab_bg_1'>\n";
  echo " <td>".$LANG['plugin_gestioncatalogueservice'][14]." : </td>\n";
  echo " <td><input type='text' name='gestioncatalogueservice_perimetre_service' size='80' value=\"$perimetre_service\"></td>\n";
  echo " </tr>\n";
  echo " <tr class='tab_bg_2'>\n";
  echo " <td colspan= '2' align='center'>\n";
   if (isset($newid) AND $newid!=""){
        echo " <input type='submit' name='$but_name' class='submit' value='$but_label'>\n";
        echo " <input type='submit' name='delete' class='submit' value='".$LANG['plugin_gestioncatalogueservice'][31]."' onclick=\"return confirm('Etes vous certains de vouloir supprimer le catalogue de service ".addslashes($name)."?')\"></td>\n";
  }
  else{
        echo " <input type='submit' name='$but_name' class='submit' value='$but_label'></td>\n";
  }
  echo " </tr>\n";
  echo "</table>\n";
  Html::closeForm();
  echo "</div>\n";
  Html::footer();
}
?>
