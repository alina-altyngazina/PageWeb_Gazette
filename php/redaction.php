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
ah_aff_entete('rédaction', 'rédaction', '..',$css='gazette.css',$pseudo,$droits);

// connection bd
$bd = em_bd_connecter();

echo ' <main>';
aa_aff_une_debRedact();

// génération detous les membres redacteur 
$sql0 = 'SELECT rePseudo, reFonction, reBio, utPrenom,
utNom FROM redacteur , utilisateur
WHERE rePseudo = utPseudo AND reCategorie = 1';
$tab0 = aa_bd_select_articles($bd, $sql0);
aa_aff_vignettesActus('Notre rédacteur en chef', $tab0);
// génération detous les membres redacteur 
$sql0 = 'SELECT rePseudo, reFonction, reBio, utPrenom,
utNom FROM redacteur , utilisateur
WHERE rePseudo = utPseudo AND reCategorie = 2';
$tab0 = aa_bd_select_articles($bd, $sql0);
aa_aff_vignettesActus('Nos premiers violons', $tab0);
// génération detous les membres redacteur 
$sql0 = 'SELECT rePseudo, reFonction, reBio, utPrenom,
utNom FROM redacteur , utilisateur
WHERE rePseudo = utPseudo AND reCategorie = 3';
$tab0 = aa_bd_select_articles($bd, $sql0);
aa_aff_vignettesActus('Notre sous-fifre', $tab0);




aa_aff_une_finRedact();

em_aff_pied();

ob_end_flush(); //FIN DU SCRIPT

//---------------------------------------------------------------fonctions page-------------------------------------------------------------------------//
//_______________________________________________________________
/**
 *  Affichage d'une tableau d'articles sous forme de vignettes.
 *  @param  String  $titre  le titre de la <section>
 *  @param  array   $tab    le tableau des enregistrements à afficher (issus de la table "article")
 */
function aa_aff_vignettesActus($titre, $tab) {
    
    echo '<section><h2>', $titre, '</h2>';
    
    foreach ($tab as $value) {
        aa_aff_une_vignetteActus($value);   
    }
    
    echo '</section>';   
}
    

//_______________________________________________________________
/**
 *  Affichage d'un article sous forme de vignette (image + titre de l'article)
 *  @param  array   $value  tableau associatif issu des enregistrements de la table "article"  
 */
function aa_aff_une_vignetteActus($value) {
   
    $value = em_html_proteger_sortie($value);
    $id = $value['rePseudo'];
    $img=strtolower($value['utPrenom']);
    $decodeBio=toHtml(strip_tags($value['reBio']),false,$charset='utf8');
    echo    '<article class="redacteur" id="', $id, '">
    <img src="../images/', $img, '.jpg" width="150" height="200" alt="', $value['utPrenom'], ' ', $value['utNom'], '">
    <h3>', $value['utPrenom'], ' ', $value['utNom'], '</h3>
    <h4>', $value['reFonction'], '</h4>
    <p>', $decodeBio, ' 
    </p>
    </article>';
               
}

//_______________________________________________________________
/** 
 *  Calcule le résultat d'une requête SQL et place ceux-ci dans un tableau. 
 *  @param  Object  $bd     la connexion à la base de données
 *  @param  String  $sql    la requête SQL à considérer
 */
function aa_bd_select_articles($bd, $sql) {
    
    // envoi de la requête au serveur de bases de données
    $res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    
    // tableau de résultat (à remplir)
    $ret = array();
    
    // parcours des résultats
    while ($t = mysqli_fetch_assoc($res)) {
        $ret[$t['rePseudo']] = $t;
    }
    
    mysqli_free_result($res);
    
    return $ret;
}

//_______________________________________________________________
/**
 *  Affichage d'un article sous forme de vignette (image + titre de l'article)
 *  @param  array   $value  tableau associatif issu des enregistrements de la table "article"  
 */
function aa_aff_une_debRedact() {
echo '
<section>
    <h2>Le mot de la rédaction</h2>
    <p>Passionnés par le journalisme d\'investigation depuis notre plus jeune âge, nous avons créé en 2019 ce site pour répondre à un 
        réel besoin : celui de fournir une information fiable et précise sur la vie de la 
        <abbr title="Licence Informatique">L-INFO</abbr>
        de l\'<a href="http://www.univ-fcomte.fr" target="_blank">Université de Franche-Comté</a>.</p>
    <p>Découvrez les hommes et les femmes qui composent l\'équipe de choc de la Gazette de L-INFO. </p>
</section>'; 
}
//_______________________________________________________________
/**
 *  Affichage d'un article sous forme de vignette (image + titre de l'article)
 *  @param  array   $value  tableau associatif issu des enregistrements de la table "article"  
 */
function aa_aff_une_finRedact() {
echo '
<section>
    <h2>La Gazette de L-INFO recrute !</h2>
    <p>Si vous souhaitez vous aussi faire partie de notre team, rien de plus simple. Envoyez-nous un mail grâce au lien dans le menu de navigation, et rejoignez l\'équipe. </p>
</section>

</main>';
}








?>
