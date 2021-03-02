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
//
if(isset($_POST['btnNouveau'])) {
    $erreurs=ahl_traitement_creation_article();
}else{
    $erreurs=FALSE;
}

// génération de la page
ah_aff_entete('Nouveau', 'Nouveau', '..',$css='gazette.css',$pseudo,$droits);
ahl_aff_creer_article($erreurs);

//ah_aff_formulaireNouveau($erreurs);

em_aff_pied();

ob_end_flush(); //FIN DU SCRIPT


//---------------------------------------------------------------fonctions page-------------------------------------------------------------------------//
/**
 * Contenu de la page : affichage du formulaire connection
 *
 * En absence de soumission, $erreurs est égal à FALSE
 * Quand l'inscription échoue, $erreurs est un tableau de chaînes  
 *
 *  @param mixed    $erreurs
 *  @global array   $_POST
 */
function ah_aff_formulaireNouveau($erreurs,$pseudo) {


    
    /* Des attributs required ont été ajoutés sur tous les champs que l'utilisateur doit obligatoirement remplir */
    echo
        '<main>',
        '<section>',
            '<h2>Rédaction d\'un nouvel article</h2>',
            '<p>Pour rédigée un nouvel article, remplissez le formulaire ci-dessous.</p>',            
            '<form action="inscription.php" method="post">';
    

    if ($erreurs) {
        echo '<div class="erreur">Les erreurs suivantes ont été relevées lors de votre inscription :<ul>';
        foreach ($erreurs as $err) {
            echo '<li>', $err, '</li>';   
        }
        echo '</ul></div>';
    }
    
    echo '<table>';
   
    em_aff_ligne_input('text', ' Titre :', ' titre', '', array('placeholder' => '4 caractères minimum', 'required' => 0));  

    em_aff_ligne_input('text', ' Résumé :', ' résumé', '', array('placeholder' => '4 caractères minimum', 'required' => 0));

    em_aff_ligne_input('text', ' Article :', ' article', '', array('placeholder' => '4 caractères minimum', 'required' => 0));
    
    
           
    echo    '</td></tr>',
            '<tr>',
                '<td colspan="2">',
                    '<input type="submit" name="btnValidation" value="Enregistrer">',
                    '<input type="reset" value="Annuler">', 
                '</td>',
            '</tr>',
        '</table>',
        '<p>  </p>',
        '</form>',
        '</section></main>';
}
/**
 *  Traitement d'une demande d'inscription. 
 *  
 *  Si l'inscription réussit, un nouvel enregistrement est ajouté dans la table utilisateur, 
 *  la variable de session $_SESSION['user'] est créée et l'utilisateur est redirigé vers la
 *  page index.php
 *
 *  @global array    $_POST
 *  @global array    $_SESSION
 *  @return array    un tableau contenant les erreurs s'il y en a
 */
function ah_traitementNouveau_connection() {
    
    /*
    * Toutes les erreurs détectées qui nécessitent une modification du code HTML sont considérées comme des tentatives de piratage 
    * et donc entraînent l'appel de la fonction em_session_exit() sauf les éventuelles suppressions des attributs required 
    * car l'attribut required est une nouveauté apparue dans la version HTML5 et nous souhaitons que l'application fonctionne également 
    * correctement sur les vieux navigateurs qui ne supportent pas encore HTML5
    *
    */
    if( !em_parametres_controle('post', array('titre','résumé','article','btnValidation'))) {
        em_session_exit();   
    }
    
    $erreurs = array();
    
    // vérification du titre
    $titre = trim($_POST['titre']);
    if (empty($titre)) { 
        $erreurs[] = 'titre ne doit pas etre vide';
    }
    
     // vérification du resume
     $résumé = trim($_POST['résumé']);
     if (empty($résumé)) { 
         $erreurs[] = 'résumé ne doit pas etre vide';
     }
      // vérification du nouvelle article
    $article = trim($_POST['article']);
    if (empty($article)) { 
        $erreurs[] = 'article ne doit pas etre vide';
    }
    
    // si erreurs --> retour
    if (count($erreurs) > 0) {
        return $erreurs;   //===> FIN DE LA FONCTION
    }
    
    // ouverture de la connexion à la base 
    $bd = em_bd_connecter();
    
 

    // si erreurs --> retour
    if (count($erreurs) > 0) {
        // fermeture de la connexion à la base de données
        mysqli_close($bd);
        return $erreurs;   //===> FIN DE LA FONCTION
    }
    

    // fermeture de la connexion à la base de données
    mysqli_close($bd);
    
    // redirection sur la page protegee.php
    //header('location: ./protegee.php');    // TODO : A MODIFIER DANS LE PROJET
    //exit(); //===> Fin du script
}
/**
 * Affiche zones pour creer un article
 * Zones: Titre, Résumé, Texte et Image
 *  
 * @param  array    $erreurs    Erreurs lors de l'envoie de l'ajout d'article'.
 */
function ahl_aff_creer_article($erreurs){
    
    echo '<main>',
        '<section>',
            '<h2>Création d\'article</h2>',
            '<p>Veuillez écrire un texte, un titre et un résumé puis éventuellement choisir une photo pour publier un article.</p>'; 

    //Si presence d'erreurs alors on les affiches
    if($erreurs){
        echo '<div class="erreur">Les erreurs suivantes ont été relevées lors de la création de l\'article<ul>';
        foreach ($erreurs as $err) {
            echo '<li>', $err, '</li>';   
        }
        echo '</ul></div>';
    }
    echo '</section></main>';
    
    echo '<main>',
        '<section>',
            '<h2>Informations</h2>', 
            '<form enctype="multipart/form-data" action="nouveau.php" method="post">';

    echo '<p><fieldset class="cadre_titre"><legend>Ajoutez le titre</legend>',
            '<textarea id="titre" name="titre" rows="1" cols="100"></textarea>',
            '</fieldset></p>',        
        '<p><fieldset class="cadre_resume"><legend>Ajoutez le résumé</legend>',
            '<textarea id="resume" name="resume" rows="5" cols="100"></textarea>',
            '</fieldset></p>',        
        '<p><fieldset class="cadre_texte"><legend>Ajoutez le texte</legend>',
            '<textarea id="texte" name="texte" rows="40" cols="100"></textarea>',
        '</fieldset></p>',
        '<p><fieldset class="cadre_image"><legend>Ajoutez une image</legend>',
            '<input type="file" name="btnPhotoArticle">', 
        '</fieldset></p>',
        '<table>',
            '<tr>',
                '<td colspan="2">',
                    '<input type="submit" name="btnNouveau" value="Créer">',
                    '<input type="reset" value="Réinitialiser">', 
                '</td>',
            '</tr>',
        '</table>',   
     '</form>',
    '</section></main>';

}

/**
 *  Traitement d'une demande de création d'article'. 
 *  
 *  Si la création réussit, on insert les informations dans la table article, 
 * 
 *  @global array    $_POST
 *  @global array    $_SESSION
 *  @return array    un tableau contenant les erreurs s'il y en a
 */
function ahl_traitement_creation_article(){
    $erreurs = array();

    $bd = em_bd_connecter();
    //Requete qui recupere le nombre d'article
    $sql="SELECT COUNT(arID) as Nb_Article FROM article";
    $res=mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    $tab=mysqli_fetch_assoc($res);
    $tab=em_html_proteger_sortie($tab);
    $arId=$tab['Nb_Article'];
    mysqli_free_result($res);

    //Requete qui recupere les id's des articles
    $sql="SELECT arID FROM article";
    $res=mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    while($tab2=mysqli_fetch_assoc($res)){
        $tab2=em_html_proteger_sortie($tab2);
        if($arId==$tab2['arID']){
            $arId++;
        }
    }
    mysqli_free_result($res);

    // vérification des titre, résumé et texte
    $arTitre=htmlspecialchars(trim($_POST['titre']));
    $arResume=htmlspecialchars(trim($_POST['resume']));
    $arTexte=htmlspecialchars(trim($_POST['texte']));
    $arTitre=mysqli_real_escape_string($bd,$arTitre);
    $arResume=mysqli_real_escape_string($bd,$arResume);
    $arTitre=mysqli_real_escape_string($bd,$arTitre);

    
    // vérification de la photo
    $oks=array('.jpg');
    if(isset($_FILES['btnPhotoArticle']['name'])){
        if($_FILES['btnPhotoArticle']['name']!= NULL){
            $nomI=$_FILES['btnPhotoArticle']['name'];
            $ext=strtolower(substr($nomI,strrpos($nomI,'.')));
            if(in_array($ext,$oks)){
                $Dest='./../upload/'.strval($arId);
                if($_FILES['btnPhotoArticle']['error']==0 && @is_uploaded_file($_FILES['btnPhotoArticle']['tmp_name']) && @move_uploaded_file($_FILES['btnPhotoArticle']['tmp_name'],$Dest.$ext)){
                    
                }else{

                    $erreurs[]='Une erreur interne a empêché l\'upload de l\'image.';
                }
            }else{
                $erreurs[]='L\'extension de l\'image doit être .jpg.';
            }
        }
        
    }

    // si erreurs --> retour
    if (count($erreurs) > 0) {
        return $erreurs;   //===> FIN DE LA FONCTION
    }

 
    $annee=date('Y');
    $mois=date('m');
    $jour=date('d');
    $heure=date('H');
    $minute=date('i');
    $arAuteur=$_SESSION['user']['pseudo'];

    
    //Requete qui insert un nouvel article
    $sql="INSERT INTO article(arID, arTitre, arResume, arTexte, arDatePublication, arDateModification, arAuteur) 
        VALUES ('{$arId}','{$arTitre}','{$arResume}','{$arTexte}', '{$annee}{$mois}{$jour}{$heure}{$minute}',NULL, '{$arAuteur}')";
    mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    // fermeture de la connexion à la base de données
    mysqli_close($bd);
    
    // redirection sur la page protegee.php
    header('location: ./article.php?id='.$arId);    // TODO : A MODIFIER DANS LE PROJET
    exit(); //===> Fin du script
}


?>
