<?php

/*********************************************************
 *        Bibliothèque de fonctions spécifiques          *
 *        à l'application Gazette de L-Info              *
 *********************************************************/


/** Constantes : les paramètres de connexion au serveur MySQL */
define ('BD_NAME', 'gazette_bd');
define ('BD_USER', '???');
define ('BD_PASS', '???');
define ('BD_SERVER', 'localhost');

define('LMIN_PSEUDO', 4);
define('LMAX_PSEUDO', 20);

define('LMAX_NOM', 50);
define('LMAX_PRENOM', 60);

define('LMAX_EMAIL', 255);

define('NB_ANNEE_DATE_NAISSANCE', 100);

//_______________________________________________________________
/**
 *  Affichage du début de la page (jusqu'au tag ouvrant de l'élément body)
 *
 *
 *  @param  string  $title      Le titre de la page (<head>)
 *  @param  string  $prefix     Le chemin relatif vers le répertoire racine du site
 *  @param  array   $css        Le nom de la feuille de style à inclure
 */
function em_aff_debut($title = '', $prefix='..', $css = 'gazette.css') {
    
    echo 
        '<!doctype html>', 
        '<html lang="fr">',
            '<head>',   
                '<meta charset="UTF-8">',
                '<title>La gazette de L-INFO', ($title != '') ? ' | ' : '', $title, '</title>',
                $css != '' ? "<link rel='stylesheet' type='text/css' href='{$prefix}/styles/{$css}'>" : '',
            '</head>',
            '<body>';
}
    


//_______________________________________________________________
/**
 *  Affiche le code du menu de navigation. 
 * 
 *  Il y a donc cinq "types" d'utilisateurs :
 *  les utilisateurs non authentifiés
 *  les utilisateurs authentifiés (et donc inscrits) sans aucun droit
 *  les utilisateurs authentifiés simple rédacteur (qui ont uniquement le droit de rédacteur)
 *  les utilisateurs authentifiés simple administrateur (qui ont uniquement le droit d'administrateur)
 *  les utilisateurs authentifiés rédacteur et administrateur (qui ont les 2 permissions) 
 *
 *  @param  string  $pseudo     chaine vide quand l'utilisateur n'est pas authentifié
 *  @param  array   $droits     Droits rédacteur à l'indice 0, et administrateur à l'indice 1  
 *  @param  String  $prefix     le préfix du chemin relatif vers la racine du site 
 */
function em_aff_menu($pseudo='', $droits = array(true, false), $prefix = '..') {
    //les utilisateurs non authentifiés
    echo '<nav><ul>',
            '<li><a href="', $prefix, '/index.php">Accueil</a></li>',
            '<li><a href="', $prefix, '/php/actus.php">Toute l\'actu</a></li>',
            '<li><a href="', $prefix, '/php/recherche.php">Recherche</a></li>',
            '<li><a href="', $prefix, '/php/redaction.php">La rédac\'</a></li>', 
            '<li>';
    
    // dernier item du menu ("se connecter" ou sous-menu)
    if ($pseudo) {
        echo '<a href="#">', $pseudo, '</a>', 
                '<ul>', 
                    '<li><a href="', $prefix, '/php/compte.php">Mon profil</a></li>',
                    $droits[0] ? "<li><a href=\"{$prefix}/php/edition.php\">Editer article</a></li>" : '',
                    $droits[0] ? "<li><a href=\"{$prefix}/php/nouveau.php\">Nouvel article</a></li>" : '',
                    $droits[1] ? "<li><a href=\"{$prefix}/php/admin.php\">Administration</a></li>" : '',
                    '<li><a href="', $prefix, '/php/deconnexion.php">Se déconnecter</a></li>', 
                '</ul>';
    }
    else {
        echo '<a href="', $prefix, '/php/connexion.php">Se connecter</a>';
    }
            
    echo '</li></ul></nav>';

}

//_______________________________________________________________
/**
 *  Affichage de l'élément header
 *
 *  @param  string  $h1         Le titre dans le bandeau (<header>)
 *  @param  string  $prefix     Le chemin relatif vers le répertoire racine du site
 */
function em_aff_header($h1, $prefix='..'){             
    echo '<header>', 
            '<img src="', $prefix, '/images/titre.png" alt="La gazette de L-INFO" width="780" height="83">',
            '<h1>', $h1, '</h1>',
        '</header>';
}

//_______________________________________________________________
/**
 *  Affichage du début de la page (de l'élément doctype jusqu'à l'élément header inclus)
 *
 *  Affiche notamment le menu de navigation en utilisant $_SESSION
 *
 *  @param  string  $h1         Le titre dans le bandeau (<header>)
 *  @param  string  $title      Le titre de la page (<head>)
 *  @param  string  $prefix     Le chemin relatif vers le répertoire racine du site
 *  @param  array   $css        Le nom de la feuille de style à inclure
 *  @global array   $_SESSION 
 */
function ah_aff_entete($h1, $title='', $prefix='..', $css = 'gazette.css',$pseudo,$droits){
    em_aff_debut($title, $prefix, $css);
    
    
    em_aff_menu($pseudo, $droits, $prefix);
    em_aff_header($h1, $prefix);
}

//_______________________________________________________________
/**
 *  Affichage du pied de page du document. 
 */
function em_aff_pied() {
    echo    '<footer>&copy; Licence Informatique - Janvier 2020 - Tous droits réservés</footer>',
        '</body>', 
    '</html>';  
}




//_______________________________________________________________
/**
 *  Génère l'URL de l'image d'illustration d'un article en fonction de son ID
 *  - si l'image ou la photo existe dans le répertoire /upload, on renvoie son url 
 *  - sinon on renvoie l'url d'une image générique 
 *  @param  int     $id         l'identifiant de l'article
 *  @param  String  $prefix     le chemin relatif vers la racine du site
 */
function em_url_image_illustration($id, $prefix='..') {

    $url = "{$prefix}/upload/{$id}.jpg";
    
    if (! file_exists($url)) {
        return "{$prefix}/images/none.jpg" ;
    }
    
    return $url;
}

//_______________________________________________________________
/**
* Vérifie si l'utilisateur est authentifié. 
*
* Termine la session et redirige l'utilisateur
* sur la page connexion.php s'il n'est pas authentifié.
*
* @global array   $_SESSION 
*/
function em_verifie_authentification() {
    if (! isset($_SESSION['user'])) {
        em_session_exit('./connexion.php');
    }
}

//_______________________________________________________________
/**
 * Termine une session et effectue une redirection vers la page transmise en paramètre
 *
 * Elle utilise :
 *   -   la fonction session_destroy() qui détruit la session existante
 *   -   la fonction session_unset() qui efface toutes les variables de session
 * Elle supprime également le cookie de session
 *
 * Cette fonction est appelée quand l'utilisateur se déconnecte "normalement" et quand une 
 * tentative de piratage est détectée. On pourrait améliorer l'application en différenciant ces
 * 2 situations. Et en cas de tentative de piratage, on pourrait faire des traitements pour 
 * stocker par exemple l'adresse IP, etc.
 * 
 * @param string    URL de la page vers laquelle l'utilisateur est redirigé
 */
function em_session_exit($page = '../index.php') {
    session_destroy();
    session_unset();
    $cookieParams = session_get_cookie_params();
    setcookie(session_name(), 
            '', 
            time() - 86400,
            $cookieParams['path'], 
            $cookieParams['domain'],
            $cookieParams['secure'],
            $cookieParams['httponly']
        );
    header("Location: $page");
    exit();
}

//_______________________________________________________________
/**
 *  Conversion d'une date format AAAAMMJJHHMM au format JJ mois AAAA à HHhMM
 *
 *  @param  int     $date   la date à afficher. 
 *  @return string          la chaîne qui reprsente la date
 */
function eml_date_to_string($date) {
    // les champs date (coDate, arDatePublication, arDateModification) sont de type BIGINT dans la base de données
    // donc pas besoin de les protéger avec htmlentities()
    
    // si un article a été publié avant l'an 1000, ça marche encore :-)
    $min = substr($date, -2);
    $heure = (int)substr($date, -4, 2); //conversion en int pour supprimer le 0 de '07' pax exemple
    $jour = (int)substr($date, -6, 2);
    $mois = substr($date, -8, 2);
    $annee = substr($date, 0, -8);
    
    $month = em_get_tableau_mois();    
    
    return $jour. ' '. mb_strtolower($month[$mois - 1], 'UTF-8'). ' '. $annee . ' à ' . $heure . 'h' . $min;
    // mb_* -> pour l'UTF-8, voir : https://www.php.net/manual/fr/function.mb-strtolower.php
}
/**
	 *
	 * cette fonction convertis le bbcode en htlm et en texte aussi si il ya
	 * en utilise deux tableau un pour stoquer le code et lautre pour sa traduction et en remlace un par un le code trouver dans le texte
	 *
	 * @param string $text
	 * @param boolean $code si true il va code le texte et si false il va le décoder
	 * @return string
	 */
	function toHtml($text,$code=false,$charset='utf8'){

		//special chars
		$text  = htmlspecialchars($text, ENT_QUOTES);

		/**
		 * je met dans ce tableau tous le bbcode utiliser
		 * @var array $basic_bbcode
		 */
		$basic_bbcode = array(
                                '[blockquote]','[\blockquote]',
                               '[#NNN]','[#xNNN]',
                               '&amp;#039;','&#039;',
                                'eacute;','&','amp;',
                                'quot;','agrave;','egrave;','ecirc;','ocirc;','[#xBE]', '[#129304]',
                                '[a:mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licencebody=Je%20vote%20pour%20le%201]Voter pour AC/DC - Highway To Hell',
                                '[a:mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licencebody=Je%20vote%20pour%20le%202]Voter pour Iron Maiden - Wasted Years',
                                '[a:mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licencebody=Je%20vote%20pour%20le%203]Voter pour Metallica - Nothing Else Matters',
                                '[youtube:250:148:https://www.youtube.com/embed/l482T0yNkeo AC/DC - Highway To Hell] ',
                                '[youtube:250:148:https://www.youtube.com/embed/Ij99dud8-0A Iron Maiden - Wasted Years]' ,
                                '[youtube:250:148:https://www.youtube.com/embed/Tj75Arhq5ho Metallica - Nothing Else Matters] ',
                                '[a:https://www.youtube.com/watch?v=ClQcUyhoxTg]',
                                '[b]', '[/b]',
                                '[liste]', '[/liste]',
                                '[br]', '[/br]',
                                '[it]', '[/it]',
                                '[citation]', '[/citation]',
                                '[a]', '[/a]',
                                '[item]', '[/item]',
                                '[p]', '[/p]',
								'[i]', '[/i]',
								'[u]', '[/u]',
								'[s]','[/s]',
								'[ul]','[/ul]',
								'[li]', '[/li]',
								'[ol]', '[/ol]',
								'[center]', '[/center]',
								'[left]', '[/left]',
                                '[right]', '[/right]',
                                '#\[color=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.+)\[/color\]#Usi',
									 '#\[size=([0-9][0-9]?)](.+)\[/size\]#Usi',
									 '#\[quote](\r\n)?(.+?)\[/quote]#si',
									 '#\[quote=(.*?)](\r\n)?(.+?)\[/quote]#si',
									 '#\[url](.+)\[/url]#Usi',
									 '#\[url=(.+)](.+)\[/url\]#Usi',
									 '#\[email]([\w\.\-]+@[a-zA-Z0-9\-]+\.?[a-zA-Z0-9\-]*\.\w{1,4})\[/email]#Usi',
									 '#\[email=([\w\.\-]+@[a-zA-Z0-9\-]+\.?[a-zA-Z0-9\-]*\.\w{1,4})](.+)\[/email]#Usi',
									 '#\[img](.+)\[/img]#Usi',
									 '#\[img=(.+)](.+)\[/img]#Usi',
									 '#\[code](\r\n)?(.+?)(\r\n)?\[/code]#si',
									 '#\[[youtube:250:148:https://www.youtube.com/embed/([0-9a-zA-Z]{1,11})\([0-9a-zA-Z]{1,11})#Usi',
									 '#\[youtube]([0-9a-zA-Z]{1,11})\[/youtube]#Usi'
		);

		/**
		 * je met dans ce tableau tous la traduction du bbcode
		 * @var array $basic_html
		 */
		$basic_html = array(
                                '<blockquote>','<\blockquote>',
                                '&#NNN;','&#xNNN;',
                                '\'','\'',
                                'e','','',
                                '"','à','è','ê','ô',':)','|-|',
                                '<li><a href="mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licence&body=Je%20vote%20pour%20le%201">Voter pour AC/DC - Highway To Hell</a></li>',
                                '<li><a href="mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licence&body=Je%20vote%20pour%20le%202">Voter pour Iron Maiden - Wasted Years</a></li>',
                                '<li><a href="mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licence&body=Je%20vote%20pour%20le%203">Voter pour Metallica - Nothing Else Matters</a></li>',
                                '<figure><iframe width="250" height="148" src="https://www.youtube.com/embed/l482T0yNkeo" allowfullscreen></iframe> <figcaption>AC/DC - Highway To Hell</figcaption></figure>',
                                '<figure><iframe width="250" height="148" src="https://www.youtube.com/embed/Ij99dud8-0A" allowfullscreen></iframe><figcaption>Iron Maiden - Wasted Years</figcaption></figure>' ,
                                '<figure><iframe width="250" height="148" src="https://www.youtube.com/embed/Tj75Arhq5ho" allowfullscreen></iframe><figcaption>Metallica - Nothing Else Matters</figcaption></figure> ',
                                '<a href=https://www.youtube.com/watch?v=ClQcUyhoxTg  target="_blank">',
                                '<b>', '</b>',
                                '<liste>', '</liste>',
                                '<br>', '</br>',
                                '<it>', '</it>',
                                '<citation>', '</citation>',
                                '<a>', '</a>',
                                '<item>', '</item>',
                                '<p>', '</p>',
								'<i>', '</i>',
								'<u>', '</u>',
								'<s>', '</s>',
								'<ul>','</ul>',
								'<li>','</li>',
								'<ol>','</ol>',
								'<div style="text-align: center;">', '</div>',
								'<div style="text-align: left;">',   '</div>',
                                '<div style="text-align: right;">',  '</div>',
                                '<span style="color: $1">$2</span>',
									 '<span style="font-size: $1px">$2</span>',
									 "<div class=\"quote\"><span class=\"quoteby\">Disse:</span>\r\n$2</div>",
									 "<div class=\"quote\"><span class=\"quoteby\">Disse <b>$1</b>:</span>\r\n$3</div>",
									 '<a rel="nofollow" target="_blank" href="$1">$1</a>',
									 '<a rel="nofollow" target="_blank" href="$1">$2</a>',
									 '<a href="mailto: $1">$1</a>',
									 '<a href="mailto: $1">$2</a>',
									 '<img src="$1" alt="$1" />',
									 '<img src="$1" alt="$2" />',
									 '<div class="code">$2</div>',
									 '<object type="application/x-shockwave-flash" style="width: 450px; height: 366px;" data="http://www.youtube.com/v/$1"><param name="movie" value="http://www.youtube.com/v/$1" /><param name="wmode" value="transparent" /></object>',
									 '<object type="application/x-shockwave-flash" style="width: 450px; height: 366px;" data="http://www.youtube.com/v/$1"><param name="movie" value="http://www.youtube.com/v/$1" /><param name="wmode" value="transparent" /></object>'
                          
		);

		/**
		 * en remplace tous ski est dans le tableau basic bbcode par le tableau basic htlm
		 */
        if ($code){
            $text = str_replace($basic_html, $basic_bbcode, $text);
        }else{
           $text = str_replace($basic_bbcode, $basic_html, $text); 
        }
		


		//en retourn le texte decripter
        return $text;

    }


?>
