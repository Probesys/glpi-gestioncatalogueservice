<?php

include ("../../../inc/includes.php");

//include_once "../inc/gestioncatalogueservice.class.php";
Session::checkRight("config", "w");

global $LANG;
$NEEDED_ITEMS = array("setup");

$catalogueservice = new PluginGestioncatalogueserviceGestioncatalogueservice();
if (isset($_POST["modify"])) {
    $result = $catalogueservice->updateCatalogueServiceConfiguration($_POST);

	Html::redirect($_SERVER['HTTP_REFERER']);
}

Html::header("Configuration",$_SERVER['PHP_SELF'],"config","plugins"); //Configuration

$heure_debut_service = '';
$heure_debut_pause_midi = '';
$heure_fin_pause_midi = '';
$heure_fin_service = '';
$nb_heure_max_list = '';
$nb_heure_avant_bascule_heure_jour = '';
$but_name = 'modify';
$but_label = $LANG['plugin_gestioncatalogueservice'][25];
$name = 'Configuration du plugin de gestion des catalogues de services';
$tab_heures = array();
for($i=5;$i<20;$i++)
{
    $tab_heures["$i"]=$i.":00";
    $j=$i+0.5;
    $tab_heures["$j"]=$i.":30";
}


$query = "SELECT * FROM glpi_plugin_gestioncatalogueservice_configuration";
$result = $DB->query($query);
if ($result){
    if ($DB->numrows($result) > 0) {
         $row = $DB->fetch_assoc($result);
         $heure_debut_service = $row['heure_debut_service'];
         $heure_debut_pause_midi = $row['heure_debut_pause_midi'];
         $heure_fin_pause_midi = $row['heure_fin_pause_midi'];
         $heure_fin_service = $row['heure_fin_service'];
         $nb_heure_max_list = $row['nb_heure_max_list'];
         $nb_heure_avant_bascule_heure_jour = $row['nb_heure_avant_bascule_heure_jour'];
         $progressbar_colors = $row['progressbar_colors'];
    }
}
echo "<div align='center' id='tabsbody' name='form'>";
echo "<form action=\"".$CFG_GLPI["root_doc"]."/plugins/gestioncatalogueservice/front/gestioncatalogueservice.config.php\" method='post'>\n";
echo "<table class='tab_cadre_fixe' cellpadding='2'>\n";
echo " <tr class='tab_bg_2'><th colspan='2' align='center'>$name</th></tr>\n";
echo " <tr class='tab_bg_1'>\n";
echo " <td>".$LANG['plugin_gestioncatalogueservice'][20]." : </td>\n";
echo " <td> <select name='gestioncatalogueservice_heure_debut_service'>\n";
foreach ($tab_heures as $key => $value)
{
    $selected='';
    if($heure_debut_service==$key)
    	$selected = 'selected';
    echo "<option ".$selected." value='$key'>$value</option>\n";
}
echo " </select></td>\n";
echo " </tr>\n";
echo " <tr class='tab_bg_2'>\n";
echo " <td>".$LANG['plugin_gestioncatalogueservice'][21]." : </td>\n";
echo " <td> <select name='gestioncatalogueservice_heure_debut_pause_midi'>\n";
foreach ($tab_heures as $key => $value)
{
    $selected='';
    if($heure_debut_pause_midi==$key)
    	$selected = 'selected';
    echo "<option ".$selected." value='$key'>$value</option>\n";
}
echo " </select></td>\n";
echo " </tr>\n";
echo " <tr class='tab_bg_1'>\n";
echo " <td>".$LANG['plugin_gestioncatalogueservice'][22]." : </td>\n";
echo " <td> <select name='gestioncatalogueservice_heure_fin_pause_midi'>\n";
foreach ($tab_heures as $key => $value)
{
    $selected='';
    if($heure_fin_pause_midi==$key)
    	$selected = 'selected';
    echo "<option ".$selected." value='$key'>$value</option>\n";
}
echo " </select></td>\n";
echo " </tr>\n";
echo " <tr class='tab_bg_2'>\n";
echo " <td>".$LANG['plugin_gestioncatalogueservice'][23]." : </td>\n";
echo " <td> <select name='gestioncatalogueservice_heure_fin_service'>\n";
foreach ($tab_heures as $key => $value)
{
    $selected='';
    if($heure_fin_service==$key)
    	$selected = 'selected';
    echo "<option ".$selected." value='$key'>$value</option>\n";
}
echo " </select></td>\n";
echo " </tr>\n";
echo " <tr class='tab_bg_1'>\n";
echo " <td>".$LANG['plugin_gestioncatalogueservice'][24]." : </td>\n";
echo " <td><input type='text' name='gestioncatalogueservice_nb_heure_max_list' size='10' value='$nb_heure_max_list'></td>\n";
echo " </tr>\n";
echo " <tr class='tab_bg_1'>\n";
echo " <td>".$LANG['plugin_gestioncatalogueservice'][29]." : </td>\n";
echo " <td><input type='text' name='gestioncatalogueservice_nb_heure_avant_bascule_heure_jour' size='10' value='$nb_heure_avant_bascule_heure_jour'></td>\n";
echo " </tr>\n";

// Progress bar colors
//jscolor
echo "<script type='text/javascript' src='../script/jscolor.js'></script>";
$_SESSION['pbcolors'] = unserialize($progressbar_colors);
$pbcolors = $_SESSION['pbcolors'];
echo "<tr class='tab_bg_2'><th colspan='2' align='center'>".$LANG['plugin_gestioncatalogueservice'][33]."</th></tr>\n";
echo "<tr class='tab_bg_2'>";
echo "<td colspan='2'>";
echo "<table size='100%' align='center'><tr>";

$i = 0;
$colornumber = (count($pbcolors)-1);
$ecart = 100/$colornumber;
$intmin = 0;

foreach($pbcolors as $item) {
	$intmax = "-".$ecart*($i+1);
	if ($i == $colornumber) {
		$intmax = "";
	}
	echo "<td>". $intmin . $intmax ."%&nbsp;:&nbsp;";
	echo "<input class='color' type='text' name='pbcolor".$i."' size='7' value='".$item."'></td>";
	$intmin += $ecart;
	$i++;
}

echo "</tr></table>";
echo "</td></tr>";

echo " <tr class='tab_bg_2'>\n";
echo " <td colspan= '2' align='center'>\n";
echo " <input type='submit' name='$but_name' class='submit' value='$but_label'></td>\n";
echo " </tr>\n";
echo "</table>\n";
Html::closeForm();
echo "</div>\n";

Html::footer();
?>


