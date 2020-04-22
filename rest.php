<?php
class Rest 
{
    protected $_content_type; 	// type de contenu retourné par la réponse
    protected $_donnees_req;    // données transmises par la requête
    protected $_methode_req;    // methode de la requête transmise
    protected $_code_statut;	// code statut HTTP retourné

    /*
    *  Constructeur de la classe
    */
    public function __construct() 
	{
        $this->_methode_req = $_SERVER['REQUEST_METHOD'];	// récupération du type de méthode (PUT, GET, POST ou DELETE)
        $this->_donnees_req = array();						// création du tableau qui contiendra les données de la requête 
        $this->_content_type = "application/json";			// reponse de type JSON
		
		// récupèration des données transmises 
		switch ($this->_methode_req) {
			/*
			*  On récupére les paramètres de la requête $_REQUEST
			*  et on les stocke dans le champ $_donnees_req
			*  Une erreur est envoyée si la demande concerne
			*  une méthode autre que GET, POST, PUT ou DELETE
			*/
            case "POST" :
            case "GET" :
            case "DELETE" :
            case "PUT" :
                $this->_donnees_req = $this->formate_donnees($_REQUEST);
                break;
            default :
				// méthode incorrecte => retour du code statut 406
                $this->reponse('', 406);
                break;
        }
    }
	
	/*
	*   Méthode formate_donnees
	*
    *   Cette méthode analyse les données transmises par la requête et les reformatte éventuellement 
    *   afin d'obtenir un tableau associatif contenant les données formatées transmises par la requête
    */
    private function formate_donnees($data) 
	{
        $formate = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $formate[$k] = $this->formate_donnees($v);
            }
        }
        else {
            if (get_magic_quotes_gpc()) {
                $data = trim(stripslashes($data));
            }
            $data = strip_tags($data);
            $formate = trim($data);
        }
        return $formate;
    }
	
    /*
	*   Méthode reponse
	*
    *   Cette méthode met en forme la réponse à la requête
    */
    public function reponse($data, $statut) 
	{
		// code statut retourné = celui passé à la méthode OU 200 si aucune valeur n'est transmise dans $statut
		if ($statut) {
			$this->_code_statut = $statut;
		} else {
			$this->_code_statut = 200;
		}
		// récupération du libellé du code statut  
		$libelle_code_statut = $this->get_libelle_code_statut();
		
		// entêtes HTTP de la réponse
        header("HTTP/1.1 " . $this->_code_statut. " " . $libelle_code_statut);
        header("Content-Type:" . $this->_content_type);
        echo $data;
        exit;
    }


    /*
	*   Méthode get_libelle_code_statut
	*
    *  Cette méthode définit le libellé associé au code statut HTTP (en anglais HTTP status) 
    *  Norme RFC 2616
    *  100 ==> 118 : codes d'information
    *  200 ==> 206 : codes de succès
    *  300 ==> 310 : codes de redirection
    *  400 ==> 417 : codes d'erreur du client
    *  500 ==> 505 : codes d'erreur du serveur
    */
    private function get_libelle_code_statut() 
	{
		// définition du tableau qui contient les codes statut de leur libellé
		//  
        $tab_statut = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			118 => 'Connection timed out',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Moved Temporarily',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			310 => 'Too many Redirects',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
        );
		// on retourne le libellé de l'erreur correspondant au code erreur contenu dans l'attribut _code_statut
		// si le _code_statut contient une valeur qui n'existe pas dans le tableau, on retourne le libellé de l'erreur n°500
        return ($tab_statut[$this->_code_statut]) ? $tab_statut[$this->_code_statut] : $tab_statut[500];
    }
}