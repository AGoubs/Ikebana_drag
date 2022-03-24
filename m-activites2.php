<?php
// Ikebana
// Intranet du Patrimoine Végétal
// version 5 - 01/04/2019

session_start();
$_SESSION["ikebana"]["session"]["page"] = basename($_SERVER["PHP_SELF"]);

//---------------------------------------------------------------------------------------

// paramêtre pour tout le site
require("../include/param.inc.php");

// fonctions communes au site
require("../include/fonctions.inc.php");

//---------------------------------------------------------------------------------------

// droits d'accès
droits(1, basename($_SERVER["PHP_SELF"]));

//---------------------------------------------------------------------------------------

// initialisation
init();

// mouchard sur logs
if ($_SESSION["ikebana"]["session"]["loggué"] == 1) { inserer_logs("page"); }

//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------

// la date
	// depuis date du début de page
	if (isset($_POST["aller"])) { 
	 	$_SESSION["ikebana"]["session"]["datedebut"] = $_POST["aller"];
	}
	if (isset($_GET["a"])) { 
	 	$_SESSION["ikebana"]["session"]["datedebut"] = $_GET["a"];
	}
	// depuis formulaire d'enregistrement (on garde la même date)
	if (isset($_POST["datetach"])) {
		$_SESSION["ikebana"]["session"]["datedebut"] = $_POST["datetach"];
	}
	if (!isset($_SESSION["ikebana"]["session"]["datedebut"])) {
		$_SESSION["ikebana"]["session"]["datedebut"] = date("Y-m-d");
	}
	// on détruit un enregistrement
	if ((isset($_GET["ac"])) AND ($_GET["ac"]=="d"))
	{
		$efface = "DELETE FROM travaux WHERE tr_id={$_GET["tr"]}";
		$ok = mysql_query($efface) or die(mysql_error());

		header("Location:m-activites.php");
	}
	// on détruit une tâche et tous ceux qui y ont participé
	if ((isset($_GET["ac"])) AND ($_GET["ac"]=="e"))
	{
		// on récupère les infos de cette tâche
		$sql_tache = "SELECT * FROM travaux WHERE tr_id = {$_GET["tr_id"]}";
		$req_tache = mysql_query($sql_tache) or die(mysql_error());
		$tache = mysql_fetch_array($req_tache);
		
		$efface = "DELETE FROM travaux WHERE tr_date = '{$tache["tr_date"]}' AND TAC_ID = {$tache["TAC_ID"]} AND tr_chef = {$tache["tr_chef"]}";
		$ok = mysql_query($efface) or die(mysql_error());

		header("Location:m-activites.php");
	}

	// on ajoute la tâche pour chaque équipier
	if ((isset($_POST["action"])) AND ($_POST["action"]=="tache"))
	{
		// $_POST["datetach"] = ecrire_date_date("",$_POST["datetach"],"fr","en");
		$_POST["notes"] = prepare_texte($_POST["tr_notes"], "BDD");
		// on saisit le travail des équipiers
		foreach($_POST["equipiers"] as $key => $value)
		{
			$trx="INSERT INTO travaux (tr_id,tr_date,RH_ID,TAC_ID,li_id,tr_duree,tr_chef, tr_notes ) VALUES (NULL, '{$_POST["datetach"]}', {$value}, {$_POST["tache"]}, '{$_POST["lieu"]}', '{$_POST["duree"]}',{$_POST["chef"]} , '{$_POST["tr_notes"]}' )";
			$travaux = mysql_query($trx) or die(mysql_error());
		}
		$_SESSION["ikebana"]["session"]["actdate"] = $_POST["datetach"];
		header("Location:m-activites.php");
	}
	
	//  on récupère l'activité la plus pratiquée cette année
	$sql_last = "SELECT TAC_ID, SUM(tr_duree) AS tot FROM travaux WHERE RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]} AND YEAR(tr_date) = YEAR(NOW()) GROUP BY TAC_ID ORDER BY tot DESC LIMIT 1";
	$req_last = mysql_query($sql_last) or die(mysql_error());
	$last = mysql_fetch_array($req_last);
	
	// on récupère mes activités
	$sql_mesact = "SELECT TAC_ID, TAC_NOM, TAC_DESCRIPTION, SUM(tr_duree) as tot FROM travaux NATURAL JOIN tache WHERE RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]} AND YEAR(tr_date) = YEAR(NOW()) GROUP BY travaux.TAC_ID ORDER BY TAC_NOM";
	$req_mesact = mysql_query($sql_mesact) or die(mysql_error());

	// on récupère mes lieux
	$sql_meslieux = "SELECT li_id, li_nom, li_type, li_entite, li_description, SUM(tr_duree) AS tot FROM travaux NATURAL JOIN lieux WHERE RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]} AND YEAR(tr_date) = YEAR(NOW()) GROUP BY travaux.li_id ORDER BY li_nom";
	$req_meslieux = mysql_query($sql_meslieux) or die(mysql_error());

	// on récupère le lieu favori
	$sql_monlieu = "SELECT li_id, li_nom, SUM(tr_duree) AS tot FROM travaux NATURAL JOIN lieux WHERE RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]} AND YEAR(tr_date) = YEAR(NOW()) GROUP BY travaux.li_id ORDER BY tot DESC LIMIT 1";
	$req_monlieu = mysql_query($sql_monlieu) or die(mysql_error());
	$monlieu = mysql_fetch_array($req_monlieu);

	
	include_once("../include/datemanager.class.php");
	$DateJour = new DateManager();
	$DateJour-> langage = "fr";
	$DateJour-> input_format = "yyyy-mm-dd";
	$DateJour-> date = $_SESSION["ikebana"]["session"]["datedebut"];
	$DateJour-> Initialize();
?>
<!DOCTYPE html>
<html>
<head>
<title>Patrimoine Végétal nomade</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="Description" content="Mobilité Végétale" />
<meta name="Keywords" content="" />
<meta name="Author" content="Pascal Goubier" />
<meta name="Reply-to" content="pgoubier@grandlyon.com" />
<meta name="Identifier-URL" content="http://ikebana.grandlyon.fr/m" />
<meta name="Copyright" content="Patrimoine Végétal" />
<link rel="stylesheet" href="css.css" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script type="text/javascript" src="menus.js"></script>
</head>
<body>
<div class="row">
  <div class="col-12 menu">
    <div class="topnav" id="myTopnav">

      <?php menus_mobiles("m-activites.php","Activités"); ?>
    <a href="javascript:void(0);" class="icon" onClick="myFunction()">
    <i class="fa fa-bars"></i>
  </a>
</div>
  </div>
  <div class="col-4">
    <h1>
    <form name="myform" method="post" action="m-activites.php">
    <select name="aller" onChange="this.form.submit()" >
    <?php for ($d=-50;$d<=10;$d++) { 			
		$lejour = date("Y-m-d", mktime(0,0,0,date("m"),date("d")+$d,date("Y"))); 
		$DateJour-> date = $lejour;
		$DateJour-> Initialize(); ?>
      <option <?php if($lejour==date("Y-m-d")) { ?>style="color:#900"<?php } ?> value="<?php print($lejour); ?>" <?php if (!(strcmp($lejour , $_SESSION["ikebana"]["session"]["datedebut"]))) {echo "SELECTED";} ?>>&raquo;&raquo; <?php print(ucwords($DateJour-> GetDayName())); ?> <?php print($DateJour-> GetDay()); ?> <?php print(ucwords($DateJour-> GetMonthName())); ?> <?php print($DateJour-> GetFullYear()); if($lejour==date("Y-m-d")) { print(" (aujourd'hui)"); } ?></option>
      <?php } ?> 
</select>
</form></h1>

  <form action="m-activites.php" enctype="multipart/form-data" method="post" > 
    		Chef d'équipe
<select name="chef" title="Le chef d'équipe">
		     <?php 
			if($_SESSION["ikebana"]["session"]["niveau"]==1)
			{ 
				$chefs = "SELECT RH_ID as ID, RH_NOM, RH_PRENOM FROM ressources_humaines WHERE RH_ACTIF = 1 AND RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]} ORDER BY RH_NOM";
			}
			elseif($_SESSION["ikebana"]["session"]["niveau"]==2)
			{
				$chefs = "SELECT RH_ID as ID, RH_NOM, RH_PRENOM FROM ressources_humaines WHERE RH_ACTIF = 1 AND (RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]} OR RH_SUPERIEUR = {$_SESSION["ikebana"]["session"]["RH_ID"]}) ORDER BY RH_NOM";
			}
			else
			{
				$chefs = "SELECT RH_ID as ID, RH_NOM, RH_PRENOM FROM ressources_humaines WHERE RH_ACTIF = 1 ORDER BY RH_NOM";
			}
			$req_chef = mysql_query($chefs) or die(mysql_error());

			while ($chef=mysql_fetch_array($req_chef))
			{
			?>
		     <option value="<?php print ($chef['ID']) ?>" <?php if (!(strcmp($chef['ID'], $_SESSION["ikebana"]["session"]["RH_ID"]))) {echo "SELECTED";} ?>><?php print ($chef['RH_NOM']."&nbsp;".$chef['RH_PRENOM']) ?></option>
		     <?php }
			?>
		     </select>
             Activité
             <select name="tache" >
		  <optgroup label="Mes activités déjà saisies cette année"  style="color: #FF6600">
  <?php
			while ($mesact = mysql_fetch_array($req_mesact))
			{
				// on met dans une variable pour l'utiliser plus loin
				$_SESSION["ikebana"]["session"]["mesact"][$mesact['TAC_ID']] = $mesact['TAC_NOM'];
?>
		    <option value="<?php print ($mesact['TAC_ID']) ?>" <?php if($mesact['TAC_DESCRIPTION']!="") { ?> title="<?php print ("=> ".$mesact['TAC_DESCRIPTION']) ?>"<?php } ?> <?php if (!(strcmp($mesact['TAC_ID'], $last["TAC_ID"]))) {echo "SELECTED";} ?>><?php print ($mesact['TAC_NOM']); ?> &plusmn; <?php print($mesact['tot']); ?>h</option>
		    <?php
			}
			?>
		    </optgroup>
		  <?php
			$sql_categ = "SELECT TAC_CATEG FROM tache WHERE TAC_TYPE=1 GROUP BY TAC_CATEG ORDER BY TAC_CATEG";
			$req_categ = mysql_query($sql_categ) or die(mysql_error());
			?>
		  <optgroup label="Nouvelle activité par catégorie..."></optgroup> 
		  <?php		
			while ($categ=mysql_fetch_array($req_categ))
			{
				$sql_taches = "SELECT * FROM tache WHERE TAC_TYPE=1 AND TAC_CATEG = '{$categ["TAC_CATEG"]}' ORDER BY TAC_NOM";
				$req_taches = mysql_query($sql_taches) or die(mysql_error());
			?>
		  <optgroup label="<?php print($categ["TAC_CATEG"]); ?>">
		    <?php
				while ($tache=mysql_fetch_array($req_taches))
				{
					// on vérifie si pas déjà dans mes activités
					if (!isset($_SESSION["ikebana"]["session"]["mesact"][$tache['TAC_ID']]))
					{
			?>
		    <option value="<?php print ($tache['TAC_ID']) ?>" <?php if($tache['TAC_DESCRIPTION']!="") { ?> title="<?php print ("=> ".$tache['TAC_DESCRIPTION']) ?>"<?php } ?> <?php if (!(strcmp($tache['TAC_ID'], $last["TAC_ID"]))) {echo "SELECTED";} ?>><?php print ($tache['TAC_NOM']); ?></option>
		    <?php
					}				
				}
			?>
		    </optgroup>
		  <?php
			}
		?>
		  </select></p>
         <p>Durée en heures
             <table width="100%">
             <tr>
             <?php for($d=1;$d<=10;$d++) { ?>
             <td align="center"><?php print($d); ?></td>
             <?php } ?>
             </tr><tr>
            <?php for($d=1;$d<=10;$d++) { ?>
             <td align="center"><input type="radio" name="duree" value="<?php print($d); ?>" <?php if($d==4) { print("checked"); } ?>/></td>
             <?php } ?>
             </tr>
             </table>
            Lieu
            <select name="lieu" >
		     <optgroup label="Mes lieux déjà utilisés cette année" style="color:  #FF6600">
		       <?php
			while ($meslieux = mysql_fetch_array($req_meslieux))
			{
				// on met dans une variable pour l'utiliser plus loin
				$_SESSION["ikebana"]["session"]["meslieux"][$meslieux["li_id"]] = $meslieux['li_nom'];			
?>
		       <option value="<?php print ($meslieux['li_id']) ?>" <?php if($meslieux['li_description']!="") { ?> title="<?php print ("=> ".$meslieux['li_description']) ?>"<?php } ?> <?php if (!(strcmp($meslieux['li_id'], $monlieu["li_id"]))) {echo "SELECTED";} ?>><?php print ($meslieux['li_entite']." / ".$meslieux['li_type']." / ".$meslieux['li_nom']); ?> &plusmn; <?php print($meslieux['tot']); ?>h</option>
		       <?php
			}
			?>
		       </optgroup>
		     <optgroup label="Lieux encore non utilisés cette année"></optgroup>
		     <?php 
			// les lieux
			$sql_type = "SELECT li_type FROM lieux GROUP BY li_type ORDER BY li_type";
			$req_type = mysql_query($sql_type) or die(mysql_error());
			
			while ($type = mysql_fetch_array($req_type))
			{
				// les lieux de ce type
				$sql_lieu = "SELECT * FROM lieux WHERE li_type = '{$type["li_type"]}' ORDER BY li_nom";
				$req_lieu = mysql_query($sql_lieu) or die(mysql_error());
?>
		     <optgroup label="<?php print ($type["li_type"]); ?>" 
              <?php
			 	if($type["li_type"] == "Parc de Parilly") print('style="color: #069"'); 
                if($type["li_type"] == "Domaine de Lacroix-Laval") print('style="color: #060"');
				if($type["li_type"] == "Collège") print('style="color: #033"');
				if($type["li_type"] == "Extérieur") print('style="color: #930"');
				if($type["li_type"] == "Métropole") print('style="color: #C00"');
				if($type["li_type"] == "Parcs & Jardins") print('style="color: #093"'); ?>  >
		       <?php 
				while ($lieu = mysql_fetch_array($req_lieu))
				{ 
					// on vérifie si pas déjà dans mes lieux
					if (!isset($_SESSION["ikebana"]["session"]["meslieux"][$lieu["li_id"]]))
					{
				?>
				   <option value="<?php print ($lieu['li_id']) ?>" <?php if($lieu['li_description']!="") { ?> title="<?php print ("=> ".$lieu['li_description']) ?>"<?php } ?>><?php print ($lieu['li_entite']." / ".$lieu['li_type']." / ".$lieu['li_nom']); ?></option>
				   <?php 
					}			
				} 
			?>
		       </optgroup>

		       <?php
			} 

			?>
	        </select>
            Agents
             <select name="equipiers[]" size="6" multiple class="saisiemultilignestandard" title="Les agents qui participent à l'activité">
               <?php 
		// on recherche les équipiers les plus récents (30 jours)
		$sql_recents = "SELECT travaux.RH_ID, CONCAT(rh.RH_NOM,' ',rh.RH_PRENOM) AS nom FROM travaux,ressources_humaines AS rh WHERE travaux.RH_ID = rh.RH_ID AND tr_chef = {$_SESSION["ikebana"]["session"]["RH_ID"]} AND tr_date >= NOW() - INTERVAL 30 DAY GROUP BY travaux.RH_ID ORDER BY nom";
		$req_recents = mysql_query($sql_recents) or die(mysql_error());
?>
               <optgroup label='&Eacute;quipiers de 30 jours' style='color: #C00;'>
                 <?php        
		while ($recents = mysql_fetch_array($req_recents))
		{
				$oublie[$recents["RH_ID"]]="oui";	
?>
                 <option value="<?php print($recents['RH_ID']); ?>" selected="selected"><?php print($recents['nom']); ?></option>
                 <?php        	
		}
		// on récupère tous les autres agents pour les lister (moins ceux déjà 	
		$sql_agents = "SELECT RH_ID, CONCAT(RH_NOM,' ',RH_PRENOM) AS nom FROM ressources_humaines WHERE RH_ACTIF = 1 ORDER BY RH_NOM";
		$req_agents = mysql_query($sql_agents) or die(mysql_error());
?>
               </optgroup>
               <optgroup label='Tous les autres agents...' style='color: #066;'>
                 <?php
		while ($agents = mysql_fetch_array($req_agents))
		{
			if(!isset($oublie[$agents["RH_ID"]]))
			{
 ?>
                 <option value="<?php print($agents['RH_ID']); ?>"><?php print($agents['nom']); ?></option>
                 <?php
			}
		}
?>
               </optgroup>
             </select>
            <p align="center">
            <input name="datetach" type="hidden" id="datetach" value="<?php print($_SESSION["ikebana"]["session"]["datedebut"]); ?>" />
             <input name="action" type="hidden" value="tache" />
             <input name="Submit" type="submit" value=" Enregistrer cette activité " /> 
             </form></p>
 </div>
  <div class="col-5">
  <h1>Heures déjà saisies...</h1>
 <table width="100%">
		 <tr>
         <?php
		 // on liste les 14 jours précédents
		 for ($j = -14; $j <= 0; $j++)
			{
				$_jour = date("Y-m-d", mktime(0,0,0,date("m"),date("d")+$j,date("Y")));
				
				// on liste les temps de cette journée
				$sql_temps = "SELECT tr_date, SUM(tr_duree) AS tps FROM travaux WHERE RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]} AND tr_date = '$_jour'  GROUP BY tr_date";
				$req_temps = mysql_query($sql_temps) or die(mysql_error());
				$temps = mysql_fetch_array($req_temps);
	
				$DateJour-> date = $_jour;
				$DateJour-> Initialize();
			?>
         <td align="center" valign="top" <?php if($_jour==$_SESSION["ikebana"]["session"]["datedebut"]) {?>style="background-color:#CCC;"<?php } ?>><a href="m-activites.php?a=<?php print($_jour); ?>"><?php print(substr(ucwords($DateJour-> GetDayName()),0,1)); ?><?php print($DateJour-> GetDay()); ?><br><?php print($temps["tps"]); ?></a></td>
         <?php } ?>
         </tr>
         </table>
  <table width="100%">
		 <tr>
		 <?php
				
				// on récupère les travaux de cette journée
				$sql_tache = "SELECT travaux.*, SUM(tr_duree) AS tps, TAC_NOM, GROUP_CONCAT(DISTINCT li_nom ORDER BY li_nom DESC SEPARATOR ' & ') as lieux FROM travaux NATURAL JOIN tache NATURAL JOIN lieux WHERE (tr_chef = {$_SESSION["ikebana"]["session"]["RH_ID"]} OR RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]}) AND tr_date = '{$_SESSION["ikebana"]["session"]["datedebut"]}' GROUP BY tr_date, TAC_ID";
				$req_tache = mysql_query($sql_tache) or die(mysql_error());
				// initialisation des dates pour les titre des jours
				
				$DateJour-> date = $_SESSION["ikebana"]["session"]["datedebut"];
				$DateJour-> Initialize();
		?>
			<td valign="top">
				<table width="100%">
				<tr>
					<th colspan="2" align="right"><?php print(ucwords($DateJour-> GetDayName())); ?> <?php print($DateJour-> GetDay()); ?> <?php print(ucwords($DateJour-> GetMonthName())); ?> <?php print($DateJour-> GetFullYear()); ?> &laquo;&laquo;</th>
				</tr>
				<?php
				$tot_taches = $tot_tps = 0;
				$tache = array();
				while ($tache = mysql_fetch_array($req_tache))
				{	
					$tot_taches +=1;
					
					// recherche de tous ceux qui ont fait cette tâche ce jour
					$sql_autres ="SELECT CONCAT(RH_NOM,' ',RH_PRENOM) as nom  FROM travaux NATURAL JOIN ressources_humaines WHERE tr_date = '{$tache["tr_date"]}' AND TAC_ID = {$tache["TAC_ID"]} AND tr_chef != {$_SESSION["ikebana"]["session"]["RH_ID"]} GROUP BY RH_ID";
					$req_autres = mysql_query($sql_autres) or die(mysql_error());
					$tot_autres = mysql_num_rows($req_autres);
					$noms = array();
					while ($autres = mysql_fetch_array($req_autres))
					{
						$noms[$tache["tr_id"]] .= " + ".$autres["nom"]."<br>";
					}
				
				?>
				<tr class="lignecoloree">
					<td><img onMouseOver="this.src='../images/icones/mini/delete-folder-red.gif'" src='../images/icones/24x24/146.gif' onMouseOut="this.src='../images/icones/mini/user-group.gif'" border="0" /> <b><?php print($tache["TAC_NOM"]); ?></b><br />
				  <?php print($tache["lieux"]); ?></td>
					<td align="center"><img src="../images/icones/horloge.gif" /> <b> <?php print(sprintf('%02d',$tache["tps"])."h"); ?></b></td>
				</tr>
				<tr>
					<td colspan="2">
					<?php 

					// on récupère les participants
					$sql_agents = "SELECT travaux.*, SUM(tr_duree) as total, RH_NOM, RH_PRENOM, GROUP_CONCAT(CONCAT(li_nom,' : ', tr_duree,'h') ORDER BY li_nom DESC SEPARATOR '<br>') as lieux, GROUP_CONCAT(DISTINCT tr_notes SEPARATOR '&') as notes FROM travaux NATURAL JOIN ressources_humaines NATURAL JOIN lieux WHERE tr_date = '{$_SESSION["ikebana"]["session"]["datedebut"]}' AND (tr_chef = {$_SESSION["ikebana"]["session"]["RH_ID"]} OR RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]}) AND TAC_ID = {$tache["TAC_ID"]} GROUP BY RH_NOM, RH_PRENOM ORDER BY RH_NOM, RH_PRENOM ";
					$req_agents = mysql_query($sql_agents) or die(mysql_error());
					while ($agent = mysql_fetch_array($req_agents))
					{
					?>
						 <a href="m-activites.php?ac=d&tr=<?php print($agent["tr_id"]); ?>" onClick="if (window.confirm('Etes-vous sûr de vouloir effacer la tâche de <?php print($agent["RH_PRENOM"]); ?> ?')) {return true;} else {return false;}" title="Effacer la tâche de <?php print($agent["RH_PRENOM"]); ?> ?" ><img src="../images/icones/24x24/111.gif" border="0" /></a> <?php print($agent["RH_NOM"]." ".$agent["RH_PRENOM"]); ?><br>
                  <?php
						
						$tot_tps += $agent["total"];

						$bulle = "<b>Durées non standard de 7-8h</b><br>";
						// on liste les agents qui ont moins de 7h de travail ou  plus de 8h
						$sql_totagent = "SELECT CONCAT(RH_PRENOM,' ',RH_NOM) AS Nom, SUM(tr_duree) AS totagent FROM travaux NATURAL JOIN ressources_humaines WHERE tr_date = '{$_SESSION["ikebana"]["session"]["datedebut"]}' AND (tr_chef = {$_SESSION["ikebana"]["session"]["RH_ID"]} OR RH_ID = {$_SESSION["ikebana"]["session"]["RH_ID"]}) GROUP BY RH_ID HAVING ((SUM(tr_duree) > 0 AND SUM(tr_duree) < 7) OR (SUM(tr_duree) > 8))";
						$req_totagent =  mysql_query($sql_totagent) or die(mysql_error());
						$nbagents = mysql_num_rows($req_totagent);
						while ($totagent = mysql_fetch_array($req_totagent))
						{
							$bulle .= $totagent["Nom"]." : ".$totagent["totagent"]."h<br>";
						}
					}
                    ?>				  </td>
				</tr>
				<?php
				}
				?>
				<tr class="lignecoloree3">
				  <td align="center"><b>
				  <?php print($tot_taches); ?> Activité<?php if($tot_taches>1) { ?>s<?php } ?></b></td>
				  <td align="center"><img src="../images/icones/horloge.gif" /> <b><?php print($tot_tps."h"); ?></b></td>
				 </tr>
				</table>
			</td>

		 </tr>
		 </table>
</div>
</div>
<div class="footer">
  <?php mfooter(); ?>
</div>
</body>
</html>