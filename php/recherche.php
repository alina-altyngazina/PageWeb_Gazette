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
ah_aff_entete('Recherche', 'Recherche', '..',$css='gazette.css',$pseudo,$droits = array(true, false));

ah_aff_formulaireRechrche();

em_aff_pied();

ob_end_flush(); //FIN DU SCRIPT


//---------------------------------------------------------------fonctions page-------------------------------------------------------------------------//
/**
 * cette fonction affichage du formulaire de recherche
 *
 * En absence de soumission, pas de resutat juste affichage de la barre de recherche
 * en cas de soumission affichage de la barre de recherche plus laffichage des articles resultan   
 *
 *  @global array   $_POST
 */
function ah_aff_formulaireRechrche() {
//une fois validation de la recherche 
if(isset($_POST['btnRecherche'])){
//en recupere le mot grace a la super variable post
$SearchWord=em_html_proteger_sortie($_POST['SearchWord']);
$WordArray=explode(" ",$SearchWord);
$WordLength=false;
    //tanque que le mot taper depasse les trois caractaire en met le boolean a true
    foreach($WordArray as $value){
        if(strlen($value)<3){
        $WordLength=true;
        }
    $WordArrayTolower[]=strtolower($value);
    }
}else{
//pas encore de rechrche taper dans ce cas en initialise nos variable a null ou vide
$SearchWord="";
$WordArray=NULL;
$WordArrayTolower[]=NULL;
$WordLength=false;
}

//affichage de la barre de rechreche
echo '<main>',
    '<section>',
    '<h2>Rechercher des articles</h2>',
    '<p>Les critères de recherche doivent faire au moins 3 caractères pour être pris en compte.</p>',
    '<form action="recherche.php" method="post">',
    '<table>','<tr>','<td colspan="2">',
    '<input type="text" name="SearchWord" value="',$SearchWord,'"size="40">',
    '<input type="submit" name="btnRecherche" value="Rechercher">','</td>',
    '</tr>','</table>','</form>';
if ($WordLength) {
echo '<div class="erreur">Un mot contient moins de 3 lettres.','</div>';
}
echo '</section>';
//si le nombre de caractaire taper est superieur a 3 comme demander 
if (!$WordLength) {
//connection bd
$bd = em_bd_connecter();

//Requete qui recupere les informations d'un article trié par ordre decroissant selon la date de publi
$sql="SELECT arID, arTitre, arResume, arDatePublication FROM article ORDER BY arDatePublication DESC";
$res = mysqli_query($bd, $sql) or hm_bd_erreur($bd, $sql);
            

$Search=false;
//Parcours des articles
while($tab=mysqli_fetch_assoc($res)){
    $tab=em_html_proteger_sortie($tab);

    $CountSearch=0;
    $trSearch=strtolower($tab['arTitre']);
    $rsSearch=strtolower($tab['arResume']);

foreach($WordArrayTolower as $val){
    //en effectue la correspandance entre le mot recherche dans le titre et le resumer  
    if((strpos($trSearch,$val) !== FALSE) || (strpos($rsSearch,$val) !== FALSE)){
    $CountSearch++;
    }  
}

if($CountSearch==count($WordArrayTolower)){
    $Search=true;

    echo  '<section>',
    '<h2>',eml_date_to_string($tab['arDatePublication']),'</h2>',
    '<article class="resume">',
    '<img src="', em_url_image_illustration($tab['arID']), '">',
    '<h3>',$tab['arTitre'],'</h3>',
    $tab['arResume'],
    '<footer><a href="../php/article.php?id=',$tab['arID'],'">Lire l\'article</a></footer>',
    '</article>';
    
    }
}
//si la recherche et sans resultats
    if(!$Search){
        if(isset($_POST['btnRecherche'])){
        echo '<section><p>Aucun resultats.</p></section>';
        }
    }
}
 echo '</main>';   
// libération des ressources
mysqli_free_result($res);
// fermeture de la connexion à la base de données
mysqli_close($bd);        
}

?>