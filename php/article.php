<?php
require_once('./bibli_gazette.php');
require_once('./bibli_generale.php');

// bufferisation des sorties
ob_start();

// démarrage de la session
session_start();

// si l'utilisateur est déjà authentifié
if (isset($_SESSION['user'])){
    $pseudo=$_SESSION['user']['pseudo'];
    $droits=array($_SESSION['user']['redacteur'],$_SESSION['user']['administrateur']);
}else{
    $pseudo='';
    $droits=array(false,false); 
}

// si il y a une autre clé que id dans $_GET, piratage ?
// => l'utilisateur est redirigé vers index.php
if (!em_parametres_controle('get', array(), array('id'))) {
    header('Location: ../index.php');
    exit;
}

// affichage de l'entête

ah_aff_entete('L\'actu', '', '..',$css='gazette.css',$pseudo,$droits);
// affichage du contenu (article + commentaires)
eml_aff_article();

// pied de page
em_aff_pied();

// fin du script
ob_end_flush();


/**
 * Affichage de l'article et de ses commentaires
 */
function eml_aff_article() {
    
    // vérification du format du paramètre dans l'URL
    if (!isset($_GET['id'])) {
        eml_aff_erreur ('Identifiant d\'article non fourni.');
        return;     // ==> fin de la fonction
    }
        
    if (!em_est_entier($_GET['id']) || $_GET['id'] <= 0) {
        eml_aff_erreur ('Identifiant d\'article invalide.');
        return;     // ==> fin de la fonction
    }
    $id = (int)$_GET['id'];
    
    // ouverture de la connexion à la base de données
    $bd = em_bd_connecter();
    
    // Récupération de l'article, des informations sur son auteur (y compris ses éentuelles infos renseignées dans la table 'redacteur'),
    // de ses éventuelles commentaires
    $sql = "SELECT *  
            FROM ((article INNER JOIN utilisateur ON arAuteur = utPseudo)
            LEFT OUTER JOIN redacteur ON utPseudo = rePseudo)
            LEFT OUTER JOIN commentaire ON arID = coArticle
            WHERE arID = {$id}
            ORDER BY coDate DESC, coID DESC";

    $res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    
    // pas d'articles --> fin de la fonction
    if (mysqli_num_rows($res) == 0) {
        eml_aff_erreur ('Identifiant d\'article non reconnu.');
        mysqli_free_result($res);
        mysqli_close($bd);
        return;         // ==> fin de la fonction
    }

    // ---------------- GENERATION DE L'ARTICLE ------------------

    // affichage de l'article et des commentaires associés
    echo '<main>';

    $tab = mysqli_fetch_assoc($res);
    $coArticle=$tab['arID'];
    // Mise en forme du prénom et du nom de l'auteur pour affichage dans le pied du texte de l'article
    // Par exemple, pour 'johnny' 'bigOUde', ça donne 'J. Bigoude'
    // A faire avant la protection avec htmlentities() à cause des éventuels accents
    $auteur = eml_mb_ucfirst_lcremainder(mb_substr($tab['utPrenom'], 0, 1, 'UTF-8')) . '. ' . eml_mb_ucfirst_lcremainder($tab['utNom']);
    
    // protection contre les attaques XSS
    $auteur = em_html_proteger_sortie($auteur);

    // protection contre les attaques XSS
    $tab = em_html_proteger_sortie($tab);
    
    $imgFile = "../upload/{$id}.jpg";
    
    
    // génération du bloc <article>
    $texte=$tab['arTexte'];
    $texteShtlm=htmlspecialchars_decode($texte);
    $texteDecode=toHtml($texteShtlm,false,$charset='utf8');

    $titre=$tab['arTitre'];
    $tirte=htmlspecialchars_decode($titre);
    $titre=toHtml($titre,false,$charset='utf8');
    
    echo '<article>', 
            '<h3>', em_html_proteger_sortie($titre), '</h3>',
            ((file_exists($imgFile)) ? "<img src='{$imgFile}' alt=\"Photo d\'illustration | {$tab['arTitre']}\">" : ''),
            $texteDecode,        
            '<footer>Par ',
            // si l'auteur a encore le droit de rédacteur et si il a enregistré des informations dans la table redacteur
            // on affiche un lien vers sa présentation sur la page redaction.php, 
            // sinon on affiche uniquement $auteur
            ((isset($tab['rePseudo']) && ($tab['utStatut'] == 1 || $tab['utStatut'] == 3)) ?
            "<a href='../php/redaction.php#{$tab['utPseudo']}'>$auteur</a>" : $auteur), 
            '. Publié le ', eml_date_to_string($tab['arDatePublication']);

    // ajout dans le pied d'article d'une éventuelle date de modification
    if (isset($tab['arDateModification'])) {
        echo ', modifié le '. eml_date_to_string($tab['arDateModification']);
    }
    
    // fin du bloc <article>
    echo '</footer>',                
        '</article>';

    //pour accéder une seconde fois au premier enregistrement de la sélection
    mysqli_data_seek($res, 0); 

    // Génération du début de la zone de commentaires
    echo '<section>',
            '<h2>Réactions</h2>';    
    
    // s'il existe des commentaires, on les affiche un par un.
    if (isset($tab['coID'])) {
        echo '<ul>';
        while ($tab = mysqli_fetch_assoc($res)) {
            echo '<li>',
                    '<p>Commentaire de <strong>', em_html_proteger_sortie($tab['coAuteur']), '</strong>, le ',
                        eml_date_to_string($tab['coDate']), 
                    '</p>',
                    '<blockquote>', em_html_proteger_sortie($tab['coTexte']), '</blockquote>', 
                '</li>';
        }
        echo '</ul>';
    }
    // sinon on indique qu'il n'y a pas de commentaires
    else {
        echo '<p>Il n\'y a pas de commentaires à cet article. </p>';  
    }
    
    // libération des ressources
    mysqli_free_result($res);
    
    // fermeture de la connexion à la base de données
    mysqli_close($bd);

   
    if(isset($_SESSION['user'])){
        $coTexte=null;
        //
    echo  '<form enctype="multipart/form-data" action="article.php" method="get">','<table>','<tr>','<td colspan="2">',
    '<input type="text" name="coTexte" value="',$coTexte,'"size="40">',
    '<input type="submit" name="btnCom" value="Enregistrer">','</td>',
    '</tr>','</table>';
    $bd3 = em_bd_connecter();
    //Requete qui recupere le nombre d'article
    $sql3="SELECT COUNT(coID) as nbCo  FROM commentaire";
    $res3=mysqli_query($bd3, $sql3) or em_bd_erreur($bd3, $sql3);
    $tab1=mysqli_fetch_assoc($res3);
    $tab1=em_html_proteger_sortie($tab1);
    $x=$tab1['nbCo'];
    mysqli_free_result($res3);

    //Requete qui recupere les id's des commentaires
  
    $bd2 = em_bd_connecter();
    $sql2="SELECT coId FROM `commentaire`";
    $res2=mysqli_query($bd2, $sql2) or em_bd_erreur($bd2, $sql2);
    while($tab2=mysqli_fetch_assoc($res2)){
     $tab2=em_html_proteger_sortie($tab2);
     if($x==$tab2['coId']){
         $x++;
     }
 }
 mysqli_free_result($res2);

    
    $annee=date('Y');
    $mois=date('m');
    $jour=date('d');
    $heure=date('H');
    $minute=date('i');
    $coAuteur=$_SESSION['user']['pseudo'];
        if(($x!=null)&&($coArticle!=null)&&($coTexte!=null)){
        //Requete qui insert un nouvel article
            $sql="INSERT INTO commentaire(coID,coAuteur, coTexte, coDate,coArticle) 
             VALUES ('{$x}','{$coAuteur}','{$coTexte}', '{$annee}{$mois}{$jour}{$heure}{$minute}','{$coArticle}')";
            mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
        // fermeture de la connexion à la base de données
            mysqli_close($bd);


        }
        
}else{
    echo '<p>', '<a href="connexion.php">Connectez-vous</a> ou <a href="inscription.php">inscrivez-vous</a> ',
    'pour pouvoir commenter cet article !',
    '</p>', '</section></main>';


}
    
    
    

    
      

}
//___________________________________________________________________
/**
 * Renvoie une copie de la chaîne UTF8 transmise en paramètre après avoir mis sa
 * première lettre en majuscule et toutes les suivantes en minuscule
 *
 * @param  string   $str    la chaîne à transformer
 * @return string           la chaîne résultat
 */
function eml_mb_ucfirst_lcremainder($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $fc = mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'));
    return $fc.mb_substr($str, 1, mb_strlen($str), 'UTF-8');
}

//_______________________________________________________________
/**
 *  Affchage d'un message d'erreur dans une zone dédiée de la page.
 *  @param  String  $msg    le message d'erreur à afficher.
 */
function eml_aff_erreur($msg) {
    echo '<main>', 
            '<section>', 
                '<h2>Oups, il y a une erreur...</h2>',
                '<p>La page que vous avez demandée a terminé son exécution avec le message d\'erreur suivant :</p>',
                '<blockquote>', $msg, '</blockquote>', 
            '</section>', 
        '</main>';
}
function ah_ligne($var){
    return str_replace(array('\\r\\n','\r\\n','r\\n','\r\n', '\n', '\r'), '<br />', ah_ligne($var));
}
/**
	 *
	 * removes bbcode from text
	 * @param string $text
	 * @return string text cleaned
	 */
	function ah_remove($text)
	{
		return strip_tags(str_replace(array('[',']'), array('<','>'), $text));
	}


   

?>
