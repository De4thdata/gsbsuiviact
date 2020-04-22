<?php
	header("Access-Control-Allow-Origin: *");
	
	// include des classes
    require_once("rest.php");
    require_once("restGSBSuiviAct.php");
	
    // On crée un objet instance de la classe RestGSBSuiviAct
    $api = new RestGSBSuiviAct();
	
	// on appelle la méthode traitement_demande qui va traiter 
	// la requête et retourner la réponse
    $api->traitement_demande();
	
?>

