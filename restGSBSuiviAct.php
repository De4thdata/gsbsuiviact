<?php
class RestGSBSuiviAct extends Rest 
{
    private $_serveur;		// serveur MySQL
    private $_util;			// compte utilisé pour se connecter à la base de données
    private $_mot_passe; 	// mot de passe du compte utilisé pour se connecter à la base de données
    private $_nom_base;		// nom de la base de données
    private $_obj_base;	 	// objet de la classe PDO qui permet de faire des requêtes sur la base de données
	
    public function __construct() 
	{
        // Appel du constructeur de la classe mère
        parent :: __construct();
		
		// on renseigne le serveur MySQL, le compte, le mot de passe de connexion
		// et le nom de la base de données
		$this->_serveur= "localhost";
		$this->_util = "ApiGSBSuiviact";
		$this->_mot_passe = "sui2020ACT#GsB";
		$this->_nom_base = "gsb_suivi_activite";
		
        // Appel de la méthode connexion qui permet de créer une instance de PDO dans l'attribut _obj_base
		// Cette instance sera chargée de réaliser les requêtes sur la base de données.
        $this->connexion();
    }
    /*
    *  création de l'objet responsable des accès à la base de données
    */
    private function connexion() 
	{
		// on positionne les attributs pour attraper les exceptions => PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
		// et pour obtenir les données lues sous forme d'objet => PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ
        $OPTIONS = array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ);
        try {
			// on crée l'objet responsable des requêtes sur la base de données cave
            $connection_string = 'mysql:host='.$this->_serveur.';port=3306;dbname='.$this->_nom_base.';charset=utf8';
            $this->_obj_base = new PDO($connection_string, $this->_util, $this->_mot_passe, $OPTIONS);
        }
        catch (Exception $e) {
            //Base indisponible. L'erreur 503 est retournée
            $this->reponse('', 503);
        }
    }
    /*
    * Methode publique d'accès au web service REST.
    * Cette méthode permet d'appeler la méthode correspondant à la requête HTTP envoyée
    * La méthode (PUT, GET, POST ou DELETE) et l'URL utilisée permettent de définir le traitement à effectuer
    *
    */
	public function traitement_demande() 
	{
	/*	
		
		Début du traitement
		-------------------
		la fonction explode va être appelée pour récupérer dans le tableau $tab_operation les éléments séparés par un /  
		qui sont contenus dans la variable $this->_donnees_req['operation'].
		*/
		$tab_operation = explode('/', $this->_donnees_req['operation']);
		
	/*	
		On appelle la fonction array_shift pour obtenir dans $ressource la valeur de la 1ère case du tableau. 
		TRES IMPORTANT : array_shift fournit la valeur située dans la 1ère case PUIS LA SUPPRIME du tableau.
	*/
		$ressource = array_shift($tab_operation);
		
	/*	si le tableau contient encore des éléments, on appelle la méthode array_shift qui permet d'obtenir la valeur 
		de la 1ère case du tableau dans la variable $id PUIS SUPPRIME la valeur de la 1ère case du tableau.
	*/
		if (count($tab_operation) != 0) {
			$id = array_shift($tab_operation);
		}
		// à ce stade le tableau tab_operation est vide 
	/*
		on vérifie si les demandes sont conformes :
		- si la ressource demandée n'est pas valide alors on retourne le code erreur 404
		- sinon on appelle la fonction correspondant au traitement en fonction de la ressource et de la méthode
	*/
		switch($ressource)
		{
			case "medecins" :
				switch($this->_methode_req)
				{
					// méthode GET   (consultation de données)
					case "GET" :
						// la variable $id existe-t'elle ? 
						if (isset($id) == true) {
							// OUI (cela signifie que l'on souhaite obtenir les caractéristiques d'un médecin
							// on appelle la méthode lire_un_medecin en lui donnant le numéro du medecin à consulter
							$this->lire_un_medecin($id);
						} else {
							// NON (cela signifie que l'on souhaite lire tous les medecins)
							// on appelle la méthode lire_les_vins 
							$this->lire_les_medecins();
						}
						break;
					default :
					// méthode invalide => retour avec le code statut 405 (méthode incorrecte)
				    $this->reponse('', 405);
					break;
				}
				case "delegues" :
				switch($this->_methode_req)
				{
					// méthode GET   (consultation de données)
					case "GET" :
						if(isset($this->_donnees_req['identifiant']) == true && isset($this->_donnees_req['motdepasse'])== true){
                            $identifiant = $this->_donnees_req['identifiant'];
                            $mdp = $this->_donnees_req['motdepasse'];
                            $this->lire_un_delegue_connexion($identifiant, $mdp);
                        }
						// la variable $id existe-t'elle ? 
						if (isset($id) == true) {
							// OUI (cela signifie que l'on souhaite obtenir les caractéristiques d'un délégué
							// on appelle la méthode lire_un_delegue en lui donnant le numéro du délégué à consulter
							$this->lire_un_delegue($id);
						} else {
							// NON (cela signifie que l'on souhaite lire tous les délégués)
							// on appelle la méthode lire_les_delegues 
							$this->lire_les_delegues();
						}
						break; 
					default :
					// méthode invalide => retour avec le code statut 405 (méthode incorrecte)
				    $this->reponse('', 405);
					break;
				}
				case "visites" :
					switch($this->_methode_req)
					{
						// méthode GET   (consultation de données)
						case "GET" :
							// la variable $id existe-t'elle ? 
							if (isset($id) == true) {
								// OUI (cela signifie que l'on souhaite obtenir les caractéristiques d'un délégué
								// on appelle la méthode lire_un_visite en lui donnant le numéro du délégué à consulter
								$this->lire_un_visite($id);
							} else {
								// NON (cela signifie que l'on souhaite lire tous les délégués)
								// on appelle la méthode lire_les_visites
								$this->lire_les_visites();
							}
							break;
						default :
						// méthode invalide => retour avec le code statut 405 (méthode incorrecte)
						$this->reponse('', 405);
					
					break;
					}
					case "remboursements" :
						switch($this->_methode_req)
						{
							// méthode GET   (consultation de données)
							case "GET" :
								if(isset($this->_donnees_req['identifiant']) == true && isset($this->_donnees_req['motdepasse'])== true){
									$identifiant = $this->_donnees_req['identifiant'];
									$mdp = $this->_donnees_req['motdepasse'];
									$this->lire_liste_demande_remboursements_date($identifiant, $mdp);
								}
								// la variable $id existe-t'elle ? 
								if (isset($id) == true) {
									// OUI (cela signifie que l'on souhaite obtenir les caractéristiques d'un délégué
									// on appelle la méthode lire_un_delegue en lui donnant le numéro du délégué à consulter
									$this->lire_liste_demande_remboursements($id);
								} else {
									// NON (cela signifie que l'on souhaite lire tous les délégués)
									// on appelle la méthode lire_les_delegues 
									$this->lire_les_types_remboursements();
								}
								break;
							default :
							// méthode invalide => retour avec le code statut 405 (méthode incorrecte)
							$this->reponse('', 405);
						
						break;
						}
						case "cadeau" :
							switch($this->_methode_req)
							{
								// méthode GET   (consultation de données)
								case "GET" :
									if(isset($this->_donnees_req['identifiant']) == true && isset($this->_donnees_req['motdepasse'])== true){
										$identifiant = $this->_donnees_req['identifiant'];
										$mdp = $this->_donnees_req['motdepasse'];
										$this->lire_liste_demande_cadeaux_date($identifiant, $mdp);
									}
									// la variable $id existe-t'elle ? 
									if (isset($id) == true) {
										// OUI (cela signifie que l'on souhaite obtenir les caractéristiques d'un délégué
										// on appelle la méthode lire_un_delegue en lui donnant le numéro du délégué à consulter
										$this->lire_liste_cadeaux($id);
									} else {
										// NON (cela signifie que l'on souhaite lire tous les délégués)
										// on appelle la méthode lire_les_delegues 
										$this->lire_les_types_cadeaux();
									}
									break;
								default :
								// méthode invalide => retour avec le code statut 405 (méthode incorrecte)
								$this->reponse('', 405);
							
							break;
							}
							case "formation" :
								switch($this->_methode_req)
								{
									// méthode GET   (consultation de données)
									case "GET" :
										if(isset($this->_donnees_req['identifiant']) == true && isset($this->_donnees_req['motdepasse'])== true){
											$identifiant = $this->_donnees_req['identifiant'];
											$mdp = $this->_donnees_req['motdepasse'];
											$this->lire_liste_demande_cadeaux_date($identifiant, $mdp);
										}
										// la variable $id existe-t'elle ? 
										if (isset($id) == true) {
											// OUI (cela signifie que l'on souhaite obtenir les caractéristiques d'un délégué
											// on appelle la méthode lire_un_delegue en lui donnant le numéro du délégué à consulter
											$this->lire_liste_formations($id);
										} else {
											// NON (cela signifie que l'on souhaite lire tous les délégués)
											// on appelle la méthode lire_les_delegues 
											$this->lire_les_types_formations();
										}
										break;
									default :
									// méthode invalide => retour avec le code statut 405 (méthode incorrecte)
									$this->reponse('', 405);
								
								break;
								}
			default :
				// méthode invalide => retour avec le code statut 405 (méthode incorrecte)
				$this->reponse('', 404);
				break;
		}

    }
    /*
    *  Retourne la liste de tous les médecins
    *  Méhode GET
    */
    private function lire_les_medecins() 
	{
		$req = $this->_obj_base->prepare("SELECT id,nom,prenom,telephone FROM medecin");
        $req->execute();
        if ($req->rowCount() > 0) {
			$result = $req->fetchAll();
			// Statut OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertir_donnees)
			$this->reponse($this->convertir_donnees($result), 200);
        } else {
            // Si aucun enregistrement, retour avec le statut 404 (not found)
            $this->reponse('', 404);
        }
	}
	/*
    *  Retourne les informations sur un medecin
    *  Le paramètre $id contient l'identifiant du vin dont on souhaite obtenir les caractéristiques 
    *  Méthode GET
    */
    private function lire_un_medecin($id) 
	{
        if (empty($id) == false) {
			// le paramètre contient une valeur
			// on prépare et on exécute la requête permettant d'obtenir les caractéristiques du délégué médical
			$req = $this->_obj_base->prepare("SELECT medecin.id, delegue_medical.nom as medical, secteur.libelle as  secteur, ville.libelle as ville, medecin.nom, medecin.prenom, medecin.telephone FROM medecin 
			join secteur on id_secteur=secteur.id 
			join delegue_medical on id_delegue=delegue_medical.id
			join ville on id_ville=ville.id
			WHERE medecin.id = :par_id");
			$req->execute(array(':par_id' => $id));
			
            if ($req->rowCount() == 1) {
                $result = $req->fetch();
                // Status OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertirDonnees)
                $this->reponse($this->convertir_donnees($result),200);
            } else {
                // Si aucun enregistrement, statut "No Content"
                $this->reponse('', 204);
            }
        } else {
            // le paramètre transmis est vide: status Bad Request
            $this->reponse('', 400);
        }
	}
	
	/*
    *  Retourne la liste de toutes les pharmacies
    *  Méhode POST
    */
    private function lire_un_delegue_connexion($identifiant, $mdp) 
 	{
        if (empty($identifiant) == false && empty($mdp) == false) {
		// le paramètre contient une valeur
		// on prépare et on exécute la requête permettant d'obtenir les caractéristiques du délégué médical
            $req = $this->_obj_base->prepare("SELECT id FROM delegue_medical WHERE identifiant_connexion=:par_identifiant and mot_passe=:par_mdp");
			$req->execute(array(':par_identifiant' => $identifiant, ':par_mdp'=> $mdp));

			if ($req->rowCount() > 0) {
                $result = $req->fetchAll();
                // Status OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertirDonnees)
                $this->reponse($this->convertir_donnees($result),200);
            } else {
                // Si aucun enregistrement, statut "No Content"
                $this->reponse('Le compte n\'existe pas', 204);
            }
        } else {
            // le paramètre transmis est vide: status Bad Request
            $this->reponse('', 400);
		}	
	}

	/*
    *  Retourne la liste de toutes les pharmacies
    *  Méhode GET
    */
    private function lire_les_delegues() 
	{
		$req = $this->_obj_base->prepare("SELECT delegue_medical.id, nom, prenom, telephone, libelle as secteur FROM delegue_medical join secteur on id_secteur=secteur.id");
        $req->execute();
        if ($req->rowCount() > 0) {
			$result = $req->fetchAll();
			// Statut OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertir_donnees)
			$this->reponse($this->convertir_donnees($result), 200);
        } else {
            // Si aucun enregistrement, retour avec le statut 404 (not found)
            $this->reponse('', 404);
        }
    }
    /*
    *  Retourne les informations sur un vin
    *  Le paramètre $id_vin contient l'identifiant du vin dont on souhaite obtenir les caractéristiques 
    *  Méthode GET
    */
    private function lire_un_delegue($id) 
	{
        if (empty($id) == false) {
			// le paramètre contient une valeur
			// on prépare et on exécute la requête permettant d'obtenir les caractéristiques du délégué médical
            $req = $this->_obj_base->prepare("SELECT delegue_medical.id,nom,prenom,telephone,libelle as secteur FROM delegue_medical join secteur on id_secteur=secteur.id WHERE delegue_medical.id = :par_id");
			$req->execute(array(':par_id' => $id));
			
            if ($req->rowCount() == 1) {
                $result = $req->fetch();
                // Status OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertirDonnees)
                $this->reponse($this->convertir_donnees($result),200);
            } else {
                // Si aucun enregistrement, statut "No Content"
                $this->reponse('', 204);
            }
        } else {
            // le paramètre transmis est vide: status Bad Request
            $this->reponse('', 400);
        }
    }
	
	/*
    *  Retourne la liste de toutes les pharmacies
    *  Méhode GET
    */
    private function lire_les_visites() 
	{
		$req = $this->_obj_base->prepare("SELECT visite.id, date_visite, duree, commentaires, medecin.id as medecin FROM visite join medecin on id_medecin=medecin.id");
        $req->execute();
        if ($req->rowCount() > 0) {
			$result = $req->fetchAll();
			// Statut OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertir_donnees)
			$this->reponse($this->convertir_donnees($result), 200);
        } else {
            // Si aucun enregistrement, retour avec le statut 404 (not found)
            $this->reponse('', 404);
        }
    }
    /*
    *  Retourne les informations sur un vin
    *  Le paramètre $id_vin contient l'identifiant du vin dont on souhaite obtenir les caractéristiques 
    *  Méthode GET
    */
    private function lire_un_visite($id) 
	{
        if (empty($id) == false) {
			// le paramètre contient une valeur
			// on prépare et on exécute la requête permettant d'obtenir les caractéristiques du délégué médical
			$req = $this->_obj_base->prepare("SELECT visite.id, date_visite, duree, commentaires, medecin.id as medecin, medecin.nom, produit.libelle FROM visite 
				join medecin on id_medecin=medecin.id
				join produit on id_produit=produit.id
				WHERE medecin.id = :par_id");
			$req->execute(array(':par_id' => $id));
			
            if ($req->rowCount() > 0) {
                $result = $req->fetchAll();
                // Status OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertirDonnees)
                $this->reponse($this->convertir_donnees($result),200);
            } else {
                // Si aucun enregistrement, statut "No Content"
                $this->reponse('', 204);
            }
        } else {
            // le paramètre transmis est vide: status Bad Request
            $this->reponse('', 400);
        }
    }

	/*
    *  Retourne la liste de toutes les pharmacies
    *  Méhode GET
    */
    private function lire_les_types_remboursements() 
	{
		$req = $this->_obj_base->prepare("SELECT libelle, montant_max_remboursement FROM type_remboursement ORDER BY montant_max_remboursement DESC");
        $req->execute();
        if ($req->rowCount() > 0) {
			$result = $req->fetchAll();
			// Statut OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertir_donnees)
			$this->reponse($this->convertir_donnees($result), 200);
        } else {
            // Si aucun enregistrement, retour avec le statut 404 (not found)
            $this->reponse('', 404);
        }
	}
	    /*
    *  Retourne les informations sur un vin
    *  Le paramètre $id_vin contient l'identifiant du vin dont on souhaite obtenir les caractéristiques 
    *  Méthode GET
    */
    private function lire_liste_demande_remboursements($id) 
	{
        if (empty($id) == false) {
			// le paramètre contient une valeur
			// on prépare et on exécute la requête permettant d'obtenir les caractéristiques du délégué médical
			$req = $this->_obj_base->prepare("SELECT type_remboursement.libelle, demande_remboursement.commentaire_frais, demande_remboursement.date_saisie, delegue_medical.nom
			FROM demande_remboursement
			JOIN visite ON id_visite = visite.id
			JOIN type_remboursement ON id_type_remboursement = type_remboursement.id
			JOIN medecin ON id_medecin = medecin.id
			JOIN delegue_medical ON id_delegue = delegue_medical.id
			WHERE delegue_medical.id = :par_id
			ORDER BY demande_remboursement.date_saisie DESC ");
			$req->execute(array(':par_id' => $id));
			
            if ($req->rowCount() > 0) {
                $result = $req->fetchAll();
                // Status OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertirDonnees)
                $this->reponse($this->convertir_donnees($result),200);
            } else {
                // Si aucun enregistrement, statut "No Content"
                $this->reponse('', 204);
            }
        } else {
            // le paramètre transmis est vide: status Bad Request
            $this->reponse('', 400);
        }
	}
	
	/*
    *  Retourne la liste de toutes les pharmacies
    *  Méhode GET
    */
    private function lire_les_types_cadeaux() 
	{
		$req = $this->_obj_base->prepare("SELECT libelle
		FROM type_cadeau");
        $req->execute();
        if ($req->rowCount() > 0) {
			$result = $req->fetchAll();
			// Statut OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertir_donnees)
			$this->reponse($this->convertir_donnees($result), 200);
        } else {
            // Si aucun enregistrement, retour avec le statut 404 (not found)
            $this->reponse('', 404);
        }
	}
	    /*
    *  Retourne les informations sur un vin
    *  Le paramètre $id_vin contient l'identifiant du vin dont on souhaite obtenir les caractéristiques 
    *  Méthode GET
    */
    private function lire_liste_cadeaux($id) 
	{
        if (empty($id) == false) {
			// le paramètre contient une valeur
			// on prépare et on exécute la requête permettant d'obtenir les caractéristiques du délégué médical
			$req = $this->_obj_base->prepare("SELECT libelle
			FROM cadeau
			JOIN type_cadeau ON id_type_cadeau = type_cadeau.id
			JOIN delegue_medical ON id_delegue = delegue_medical.id
			JOIN medecin ON id_medecin = medecin.id
			WHERE delegue_medical.id = :par_id");
			$req->execute(array(':par_id' => $id));
			
            if ($req->rowCount() > 0) {
                $result = $req->fetchAll();
                // Status OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertirDonnees)
                $this->reponse($this->convertir_donnees($result),200);
            } else {
                // Si aucun enregistrement, statut "No Content"
                $this->reponse('', 204);
            }
        } else {
            // le paramètre transmis est vide: status Bad Request
            $this->reponse('', 400);
        }
	}

	/*
    *  Retourne la liste de toutes les pharmacies
    *  Méhode GET
    */
    private function lire_les_types_formations() 
	{
		$req = $this->_obj_base->prepare("SELECT libelle
		FROM type_formation");
        $req->execute();
        if ($req->rowCount() > 0) {
			$result = $req->fetchAll();
			// Statut OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertir_donnees)
			$this->reponse($this->convertir_donnees($result), 200);
        } else {
            // Si aucun enregistrement, retour avec le statut 404 (not found)
            $this->reponse('', 404);
        }
	}
	    /*
    *  Retourne les informations sur un vin
    *  Le paramètre $id_vin contient l'identifiant du vin dont on souhaite obtenir les caractéristiques 
    *  Méthode GET
    */
    private function lire_liste_formations($id) 
	{
        if (empty($id) == false) {
			// le paramètre contient une valeur
			// on prépare et on exécute la requête permettant d'obtenir les caractéristiques du délégué médical
			$req = $this->_obj_base->prepare("SELECT type_formation.id, demande_formation.date_debut_demande, demande_formation.date_fin_demande
			FROM demande_formation
			JOIN type_formation ON demande_formation.id_type_formation = type_formation.id
			JOIN delegue_medical ON id_delegue = delegue_medical.id
			WHERE delegue_medical.id = :par_id");
			$req->execute(array(':par_id' => $id));
			
            if ($req->rowCount() > 0) {
                $result = $req->fetchAll();
                // Status OK + mise en forme des caractéristiques au format demandé (appel de la méthode convertirDonnees)
                $this->reponse($this->convertir_donnees($result),200);
            } else {
                // Si aucun enregistrement, statut "No Content"
                $this->reponse('', 204);
            }
        } else {
            // le paramètre transmis est vide: status Bad Request
            $this->reponse('', 400);
        }
	}
	
    /*
    * Transformation des données au format JSON
    */
    private function convertir_donnees($lesEnregs) 
	{
		// la réponse contient les caractéristiques des enregistrements retournés et 
		// une donnée contenant le nombre d'enregistrements retournés
        $reponse= array();
		$reponse = $lesEnregs;
		// encodage du tableau en JSON
		return json_encode($reponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	/*
    * Transformation du message au format demandé
    */
    private function convertir_message($mess) 
	{
        return json_encode($mess, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);		
    }
}