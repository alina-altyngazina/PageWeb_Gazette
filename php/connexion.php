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
    header ('location: ../index.php');
    exit();
}

// si formulaire soumis, traitement de la demande d'inscription
if (isset($_POST['btnConnection'])) {
    $erreurs = ah_traitement_connection();
}else{
    $erreurs = FALSE;
}

// génération de la page
ah_aff_entete('Connection', 'Connection', '..',$css='gazette.css',$pseudo="",$droits = array(true, false));
ah_aff_formulaire($erreurs);

em_aff_pied();

ob_end_flush(); //FIN DU SCRIPT


//---------------------------------------------------------------fonctions page-------------------------------------------------------------------------//
/**
 * Contenu de la page : affichage du formulaire d'inscription
 *
 * En absence de soumission, $erreurs est égal à FALSE
 * Quand l'inscription échoue, $erreurs est un tableau de chaînes  
 *
 *  @param mixed    $erreurs
 *  @global array   $_POST
 */
function ah_aff_formulaire($erreurs) {

    $anneeCourante = (int) date('Y');

    // affectation des valeurs à afficher dans les zones du formulaire
    if (isset($_POST['btnConnection'])){
        $pseudo = em_html_proteger_sortie(trim($_POST['pseudo']));
    }
    else{
        $pseudo = '';
    }
    
    /* Des attributs required ont été ajoutés sur tous les champs que l'utilisateur doit obligatoirement remplir */
    echo
        '<main>',
        '<section>',
            '<h2>Formulaire Connection</h2>',
            '<p>Pour vous Connecter, remplissez le formulaire ci-dessous.</p>',            
            '<form action="connexion.php" method="post">';
    

    if ($erreurs) {
        echo '<div class="erreur">Les erreurs suivantes ont été relevées lors de votre Connection :<ul>';
        foreach ($erreurs as $err) {
            echo '<li>', $err, '</li>';   
        }
        echo '</ul></div>';
    }
    
    echo '<table>';
    em_aff_ligne_input('text', 'Choisissez un pseudo :', 'pseudo', $pseudo, array('placeholder' => '4 caractères minimum', 'required' => 0));
  
    em_aff_ligne_input('password', 'Choisissez un mot de passe :', 'passe1', '', array('required' => 0));
   
    
    
    echo    '<tr>', '<td colspan="2">';
    
    
    
    echo    '</td></tr>',
            '<tr>',
                '<td colspan="2">',
                    '<input type="submit" name="btnConnection" value="Se Connecter">',
                    '<input type="reset" value="Annuler">', 
                '</td>',
            '</tr>',
        '</table>',
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
function ah_traitement_connection() {
    
    /*
    * Toutes les erreurs détectées qui nécessitent une modification du code HTML sont considérées comme des tentatives de piratage 
    * et donc entraînent l'appel de la fonction em_session_exit() sauf les éventuelles suppressions des attributs required 
    * car l'attribut required est une nouveauté apparue dans la version HTML5 et nous souhaitons que l'application fonctionne également 
    * correctement sur les vieux navigateurs qui ne supportent pas encore HTML5
    *
    */
    if( !em_parametres_controle('post', array('pseudo','passe1',  'btnConnection'), array(''))) {
        em_session_exit();   
    }
    
    $erreurs = array();
    
    // vérification du pseudo
    $pseudo = trim($_POST['pseudo']);
    if (!preg_match('/^[0-9a-z]{'. LMIN_PSEUDO . ',' . LMAX_PSEUDO . '}$/',$pseudo)) { 
        $erreurs[] = 'Le pseudo doit contenir entre ' . LMIN_PSEUDO . ' et ' . LMAX_PSEUDO . ' lettres minuscules (sans accent) ou chiffres.';
    }
    

    // vérification des mots de passe
    $passe1 = trim($_POST['passe1']);
    if (empty($passe1)) {
        $erreurs[] = 'Les mots de passe ne doivent pas être vides.';
    }

    // si erreurs --> retour
    if (count($erreurs) > 0) {
        return $erreurs;   //===> FIN DE LA FONCTION
    }

    // on vérifie si le pseudo nest sont pas encore utilisés que si toutes les autres vérifications
    // réussissent car ces 2 dernières vérifications coûtent un bras !
    
    // ouverture de la connexion à la base 
    $bd = em_bd_connecter();
    
    // vérification de l'existence du pseudo ou de l'email
    $pseudoe = mysqli_real_escape_string($bd, $pseudo); // fait par principe, mais inutile ici car on a déjà vérifié que le pseudo
                                            // ne contenait que des caractères alphanumériques
    
    $passe1 = mysqli_real_escape_string($bd, $passe1);
    $sql = "SELECT utPseudo, utPasse, utStatut FROM utilisateur WHERE utPseudo = '{$pseudoe}' OR utPasse = '{$passe1}'";
    $res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    $tab = mysqli_fetch_assoc($res); 
    
    //virifier que le pseudo existe bien dans la base de donnée
        if (strcmp ($tab['utPseudo'],$pseudo)<0){
            $erreurs[] = 'Le pseudo choisi existe pas.';
        }else{
            if (strcmp ($tab['utPseudo'],$pseudo)>0){
                $erreurs[] = 'Le pseudo choisi existe pas.';
            }else{

            }
        }
    //verifier que le mot de passe lier au pseudo et bien correct
        if (!password_verify(($passe1),($tab['utPasse']))){
            $erreurs[] = 'mot de passe incorect.';
        }
    
    //si les deux condition son réuni j'inisialise mon boolean a true pour la connexion et le passage a l'espace personnel
    if (password_verify(($passe1),($tab['utPasse']))&&(strcmp ($tab['utPseudo'],$pseudo)==0)){
        $loginOk=true;
    }else{
        $loginOk=false;
    }
   
    // si erreurs --> retour
    if (count($erreurs) > 0) {
        // fermeture de la connexion à la base de données
        mysqli_close($bd);
        return $erreurs;   //===> FIN DE LA FONCTION
    }
    if ($loginOk==true){
         

        if($tab['utStatut']!=0){
                if($tab['utStatut']==1){
                    $_SESSION['user'] = array('pseudo' => $pseudo, 'redacteur' => true, 'administrateur' => false);
                }
                    if($tab['utStatut']==2){
                        $_SESSION['user'] = array('pseudo' => $pseudo, 'redacteur' => false, 'administrateur' => true);
                    }
                        if($tab['utStatut']==3){
                            $_SESSION['user'] = array('pseudo' => $pseudo, 'redacteur' => true, 'administrateur' => true);
                        }
                  

              

        }else{
        $_SESSION['user'] = array('pseudo' => $pseudo, 'redacteur' => false, 'administrateur' => false);    
        }
        
        
        mysqli_free_result($res3);
        header('Location: ../index.php');
        exit();
        }
    
    
    
    
    // enregistrement dans la variable de session du pseudo avant passage par la fonction mysqli_real_escape_string()
    // car, d'une façon générale, celle-ci risque de rajouter des antislashs
    // Rappel : ici, elle ne rajoute jamais d'antislash car le pseudo ne peut contenir que des caractères alphanumériques
   // 
    
    // fermeture de la connexion à la base de données
    mysqli_close($bd);
    
    // redirection sur la page protegee.php
    header('location: ./connexion.php');    // TODO : A MODIFIER DANS LE PROJET
    exit(); //===> Fin du script
    }


?>