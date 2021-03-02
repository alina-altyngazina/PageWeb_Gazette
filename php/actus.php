<?php
require_once('./bibli_gazette.php');
require_once('./bibli_generale.php');
//---------------------------------------------------------------page-------------------------------------------------------------------------//
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

// génération de la page
ah_aff_entete('Actus', 'Actus','..',$css='gazette.css',$pseudo,$droits);

aa_aff_vignettesActus('Fevrier 2020',02, 2020);

aa_aff_vignettesActus('Janvier 2020',01, 2020);

aa_aff_vignettesActus('Décembre 2019',12, 2019);

aa_aff_vignettesActus('Novembre 2019',11, 2019);

em_aff_pied();

ob_end_flush(); //FIN DU SCRIPT

//---------------------------------------------------------------fonctions page-------------------------------------------------------------------------//
//_______________________________________________________________
/**
 *  Affichage d'une tableau d'articles sous forme de vignettes.
 *  @param  String  $titre  le titre de la <section>
 *  @param  array   $tab    le tableau des enregistrements à afficher (issus de la table "article")
 */
function aa_aff_vignettesActus($titre, $mois, $annee) {
// connection bd
$bd = em_bd_connecter();

echo '<main>';


$sql = 'SELECT arID, arTitre, arResume, arDatePublication FROM article 
ORDER BY arDatePublication DESC ';

$value =  $res = mysqli_query($bd, $sql);
    
    echo '<section><h2>', $titre, '</h2>';

 
             //Parcours des articles
             while($value=mysqli_fetch_assoc($res)){
                 $titreArticle=strtolower($value['arTitre']);
                 $resumeArticle=strtolower($value['arResume']);

                     //Affichage de l'article corespandant a la recherche demander
 
                     if((substr($value['arDatePublication'], -8, 2)==$mois)&&(substr($value['arDatePublication'], 0, -8)==$annee)){
                        (array) $value = em_html_proteger_sortie($value);
                        $id = $value["arID"];
                    
                        echo    '<article class="resume">
                    
                        <img src="../upload/', $id, '.jpg" alt="Photo d\'illustration | Un mouchard dans un corrigé de Langages du Web">
                        <h3>', $titreArticle, '</h3>
                        <p>', $resumeArticle, ' 
                        </p>
                        <footer><a href="../php/article.php?id=', $id, '">Lire l\'article</a></footer>
                        </article>';
                     }
                     $moisVerif = substr($value['arDatePublication'], -8, 2);
                     $anneeVerif = substr($value['arDatePublication'], 0, -8);   
                 
                 
             }
   
               
    
    
    echo '</section>'; 
              
echo '</main>';
   //Libération de la mémoire associée au résultat de la requête
   mysqli_free_result($res);
   // fermeture de la connexion à la base de données
   mysqli_close($bd); 
}


//_______________________________________________________________
/**
 *  Conversion d'une date format AAAAMMJJHHMM au format JJ mois AAAA à HHhMM
 *
 *  @param  int     $date   la date à afficher. 
 *  @return string          la chaîne qui reprsente la date
 */
function ah_convert_date($date) {
$dizaines = $date/1000000;
//$unites = $variable%10;
return $dizaines;
}



//_______________________________________________________________
/**
 *  Conversion d'une date format AAAAMMJJHHMM au format JJ mois AAAA à HHhMM
 *
 *  @param  int     $date   la date à afficher. 
 *  @return string          la chaîne qui reprsente la date
 */
function ah_date_to_string($date) {
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
function aff_actu($sql){
// ouverture de la connexion à la base de données
$bd = em_bd_connecter();

$res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

// pas d'articles --> fin de la fonction
if (mysqli_num_rows($res) == 0) {
    eml_aff_erreur ('Identifiant d\'article non reconnu.');
    mysqli_free_result($res);
    mysqli_close($bd);
    return;         // ==> fin de la fonction
}
$tab = mysqli_fetch_assoc($res);
// protection contre les attaques XSS
$tab = em_html_proteger_sortie($tab);
return $tab;
}


?>