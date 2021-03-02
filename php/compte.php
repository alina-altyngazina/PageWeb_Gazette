<?php
   include 'bibli_generale.php';
   include 'bibli_gazette.php';
 //  include("config.php");
   session_start();
   //
   ob_start();
// si l'utilisateur est déjà authentifié
if (isset($_SESSION['user'])){
    $pseudo=$_SESSION['user']['pseudo'];
    $droits=array($_SESSION['user']['redacteur'],$_SESSION['user']['administrateur']);
}else{
    $pseudo='';
    $droits=array(false,false); 
}

   // affichage de l'entête$_SESSION['pseud$_SESSION['pseudo']o']
   ah_aff_entete('Mon Compte', 'Mon Compte', '..',$css='gazette.css',$pseudo,$droits);

   $erreurs=null;
   ah_aff_formulaireCompte($erreurs,$pseudo);
    ah_aff_formulaireMdp($erreurs);
    //ah_traitement_modifProfil();

   em_aff_pied();

   ob_end_flush(); //FIN DU SCRIPT


/**
 * Contenu de la page : affichage du formulaire d'inscription
 *
 * En absence de soumission, $erreurs est égal à FALSE
 * Quand l'inscription échoue, $erreurs est un tableau de chaînes  
 *
 *  @param mixed    $erreurs
 *  @global array   $_POST
 */
function ah_aff_formulaireCompte($erreurs,$pseudo) {

    // connection bd
    $bd = em_bd_connecter();
    //$pseudo = em_html_proteger_sortie(trim($_POST['pseudo']));    
    // de ses éventuelles commentaires
    $sql = "SELECT `utNom`,`utPrenom`,`utEmail`,`utCivilite`,`utMailsPourris`,`utDateNaissance` FROM `utilisateur` WHERE `utPseudo`='{$pseudo}'";
    $res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    $tab = mysqli_fetch_assoc($res);
    $tab = em_html_proteger_sortie($tab);

    $anneeCourante = (int) date('Y');

    // affectation des valeurs à afficher dans les zones du formulaire
    if (isset($_POST['btnEnregistrerInfo'])){
        $pseudo = em_html_proteger_sortie(trim($_POST['pseudo']));
        $nom = em_html_proteger_sortie(trim($_POST['nom']));
        $prenom = em_html_proteger_sortie(trim($_POST['prenom']));
        $email = em_html_proteger_sortie(trim($_POST['email']));
        $jour = (int)$_POST['naissance_j'];
        $mois = (int)$_POST['naissance_m'];
        $annee = (int)$_POST['naissance_a'];
        $civilite = (isset($_POST['radSexe'])) ? (int)$_POST['radSexe'] : 3;
        $mails_pourris = isset($_POST['cbSpam']);
    }
    else{
        $pseudo = $nom = $prenom = $email = '';
        $jour = $mois = 1;
        $annee = $anneeCourante;
        $civilite = 3;
        $mails_pourris = true;
    }
    
    /* Des attributs required ont été ajoutés sur tous les champs que l'utilisateur doit obligatoirement remplir */
    echo
        '<main>',
        '<section>',
            '<h2>Informations personnelles</h2>',
            '<p>Vous pouvez modifier les informations suivantes.</p>',            
            '<form action="inscription.php" method="post">';
    

    if ($erreurs) {
        echo '<div class="erreur">Les erreurs suivantes ont été relevées lors de votre inscription :<ul>';
        foreach ($erreurs as $err) {
            echo '<li>', $err, '</li>';   
        }
        echo '</ul></div>';
    }
    
    echo '<table>';
    $attributs_checkbox1 = array();
    if ($civilite){
        // l'attribut checked est un attribut booléen qui n'a pas de valeur
        $attributs_checkbox1['checked'] = 0;
    }
    em_aff_ligne_input_radio('Votre civilité :', 'radSexe', array(1 => 'Monsieur', 2 => 'Madame'), 1,  $attributs_checkbox1);
    em_aff_ligne_input('text', 'Votre nom :', 'nom', $nom, array('placeholder' => $tab['utNom'], 'required' => 0));
    em_aff_ligne_input('text', 'Votre prénom :', 'prenom', $prenom, array('placeholder' => $tab['utPrenom'], 'required' => 0));
    
    em_aff_ligne_date('Votre date de naissance :', 'naissance', $anneeCourante - NB_ANNEE_DATE_NAISSANCE + 1, $anneeCourante, $jour, $mois, $annee);
    
    em_aff_ligne_input('email', 'Votre email :', 'email', $email, array('placeholder' => $tab['utEmail'], 'required' => 0));
   
    echo    '<tr>', '<td colspan="2">';
    
    $attributs_checkbox = array();
    if ($mails_pourris){
        // l'attribut checked est un attribut booléen qui n'a pas de valeur
        $attributs_checkbox['checked'] =0;
    }
    em_aff_input_checkbox('J\'accepte de recevoir des tonnes de mails pourris', 'cbSpam', 1, $attributs_checkbox);
                
    echo    '</td></tr>',
            '<tr>',
                '<td colspan="2">',
                    '<input type="submit" name="btnEnregistrerInfo" value="Enregistrer">',
                    '<input type="reset" value="Réinitialiser">', 
                '</td>',
            '</tr>',
        '</table>',
        '</form>',
        '</section></main>';
}
/**
 * Contenu de la page : affichage du formulaire d'inscription
 *
 * En absence de soumission, $erreurs est égal à FALSE
 * Quand l'inscription échoue, $erreurs est un tableau de chaînes  
 *
 *  @param mixed    $erreurs
 *  @global array   $_POST
 */
function ah_aff_formulaireMdp($erreurs) {

   $anneeCourante = (int) date('Y');

   // affectation des valeurs à afficher dans les zones du formulaire
   if (isset($_POST['btnEnregistrer'])){

   }
   else{
       $pseudo = $nom = $prenom = $email = '';
       $jour = $mois = 1;
       $annee = $anneeCourante;
       $civilite = 3;
       $mails_pourris = true;
   }
   
   /* Des attributs required ont été ajoutés sur tous les champs que l'utilisateur doit obligatoirement remplir */
   echo
       '<main>',
       '<section>',
           '<h2>Autjentification</h2>',
           '<p>Vous pouvez modifier votre mot de passe ci-dessous.</p>',            
           '<form action="inscription.php" method="post">';
   
   if ($erreurs) {
       echo '<div class="erreur">Les erreurs suivantes ont été relevées lors de votre inscription :<ul>';
       foreach ($erreurs as $err) {
           echo '<li>', $err, '</li>';   
       }
       echo '</ul></div>';
   }
   
   echo '<table>';

   em_aff_ligne_input('password', 'Choisissez un mot de passe :', 'passe1', '', array('required' => 0));
   em_aff_ligne_input('password', 'Répétez le mot de passe :', 'passe2', '', array('required' => 0));
   
   echo    '<tr>', '<td colspan="2">';          
   echo    '</td></tr>',
           '<tr>',
               '<td colspan="2">',
                   '<input type="submit" name="btnEnregistrer" value="Enregistrer">',
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
function ah_traitement_modifProfil() {
    
    
    /*
    * Toutes les erreurs détectées qui nécessitent une modification du code HTML sont considérées comme des tentatives de piratage 
    * et donc entraînent l'appel de la fonction em_session_exit() sauf les éventuelles suppressions des attributs required 
    * car l'attribut required est une nouveauté apparue dans la version HTML5 et nous souhaitons que l'application fonctionne également 
    * correctement sur les vieux navigateurs qui ne supportent pas encore HTML5
    *
    */
    if( !em_parametres_controle('post', array('pseudo', 'nom', 'prenom', 'naissance_j', 'naissance_m', 'naissance_a', 
                                              'passe1', 'passe2', 'email', 'btnInscription'), array('cbCGU', 'cbSpam', 'radSexe'))) {
        em_session_exit();   
    }
    
    $erreurs = array();
    
    // vérification du pseudo
    $pseudo = trim($_POST['pseudo']);
    if (!preg_match('/^[0-9a-z]{'. LMIN_PSEUDO . ',' . LMAX_PSEUDO . '}$/',$pseudo)) { 
        $erreurs[] = 'Le pseudo doit contenir entre ' . LMIN_PSEUDO . ' et ' . LMAX_PSEUDO . ' lettres minuscules (sans accent) ou chiffres.';
    }
    
    // vérification de la civilité
    if (! isset($_POST['radSexe'])){
        $erreurs[] = 'Vous devez choisir une civilité.';
    }
    else if (! (em_est_entier($_POST['radSexe']) && em_est_entre($_POST['radSexe'], 1, 2))){
        em_session_exit(); 
    }
    
    // vérification des noms et prénoms
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    eml_verifier_texte($nom, 'Le nom', $erreurs, LMAX_NOM);
    eml_verifier_texte($prenom, 'Le prénom', $erreurs, LMAX_PRENOM);
    
    // vérification de la date
    if (! (em_est_entier($_POST['naissance_j']) && em_est_entre($_POST['naissance_j'], 1, 31))){
        em_session_exit(); 
    }
    
    if (! (em_est_entier($_POST['naissance_m']) && em_est_entre($_POST['naissance_m'], 1, 12))){
        em_session_exit(); 
    }
    $anneeCourante = (int) date('Y');
    if (! (em_est_entier($_POST['naissance_a']) && em_est_entre($_POST['naissance_a'], $anneeCourante  - NB_ANNEE_DATE_NAISSANCE + 1, $anneeCourante))){
        em_session_exit(); 
    }
    
    $jour = (int)$_POST['naissance_j'];
    $mois = (int)$_POST['naissance_m'];
    $annee = (int)$_POST['naissance_a'];
    if (!checkdate($mois, $jour, $annee)) {
        $erreurs[] = 'La date de naissance n\'est pas valide.';
    }
    else if (mktime(0,0,0,$mois,$jour,$annee+18) > time()) {
        $erreurs[] = 'Vous devez avoir au moins 18 ans pour vous inscrire.'; 
    }
    
    // vérification du format de l'adresse email
    $email = trim($_POST['email']);
    if (empty($email)){
        $erreurs[] = 'L\'adresse mail ne doit pas être vide.'; 
    }
    else if (mb_strlen($email, 'UTF-8') > LMAX_EMAIL){
        $erreurs[] = 'L\'adresse mail ne peut pas dépasser '.LMAX_EMAIL.' caractères.';
    }
    // la validation faite par le navigateur en utilisant le type email pour l'élément HTML input
    // est moins forte que celle faite ci-dessous avec la fonction filter_var()
    // Exemple : 'l@i' passe la validation faite par le navigateur et ne passe pas
    // celle faite ci-dessous
    else if(! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'adresse mail n\'est pas valide.';
    }

    // vérification des mots de passe
    $passe1 = trim($_POST['passe1']);
    $passe2 = trim($_POST['passe2']);
    if (empty($passe1) || empty($passe2)) {
        $erreurs[] = 'Les mots de passe ne doivent pas être vides.';
    }
    else if ($passe1 !== $passe2) {
        $erreurs[] = 'Les mots de passe doivent être identiques.';
    }
    
    // vérification de la valeur de l'élément cbCGU
    if (! isset($_POST['cbCGU'])){
        $erreurs[] = 'Vous devez accepter les conditions générales d\'utilisation.';
    }
    else if (! (em_est_entier($_POST['cbCGU']) && $_POST['cbCGU'] == 1)){
        em_session_exit(); 
    }
    
    // vérification si l'utilisateur accepte de recevoir les mails pourris
    if (isset($_POST['cbSpam']) && ! (em_est_entier($_POST['cbSpam']) && $_POST['cbSpam'] == 1)){
        em_session_exit(); 
    }
    
    // si erreurs --> retour
    if (count($erreurs) > 0) {
        return $erreurs;   //===> FIN DE LA FONCTION
    }

    // on vérifie si le pseudo et l'adresse mail ne sont pas encore utilisés que si toutes les autres vérifications
    // réussissent car ces 2 dernières vérifications coûtent un bras !
    
    // ouverture de la connexion à la base 
    $bd = em_bd_connecter();
    
    // vérification de l'existence du pseudo ou de l'email
    $pseudoe = mysqli_real_escape_string($bd, $pseudo); // fait par principe, mais inutile ici car on a déjà vérifié que le pseudo
                                            // ne contenait que des caractères alphanumériques
    $emaile = mysqli_real_escape_string($bd, $email);
    $sql = "SELECT utPseudo, utEmail FROM utilisateur WHERE utPseudo = '{$pseudoe}' OR utEmail = '{$emaile}'";
    $res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    
    while($tab = mysqli_fetch_assoc($res)) {
        if ($tab['utPseudo'] == $pseudo){
            $erreurs[] = 'Le pseudo choisi existe déjà.';
        }
        if ($tab['utEmail'] == $email){
            $erreurs[] = 'Cette adresse email est déjà inscrite.';
        }
    }
    // Libération de la mémoire associée au résultat de la requête
    mysqli_free_result($res);

    // si erreurs --> retour
    if (count($erreurs) > 0) {
        // fermeture de la connexion à la base de données
        mysqli_close($bd);
        return $erreurs;   //===> FIN DE LA FONCTION
    }
    
    // calcul du hash du mot de passe pour enregistrement dans la base.
    $passe = password_hash($passe1, PASSWORD_DEFAULT);
    
    $passe = mysqli_real_escape_string($bd, $passe);
    
    if ($mois < 10) {
        $mois = '0' . $mois;   
    }
    if ($jour < 10) {
        $jour = '0' . $jour;   
    }
    $civilite = (int) $_POST['radSexe'];
    $civilite = $civilite == 1 ? 'h' : 'f';
    
    $mailsPourris = isset($_POST['cbSpam']) ? 1 : 0;
    
    $nom = mysqli_real_escape_string($bd, $nom);
    $prenom = mysqli_real_escape_string($bd, $prenom);
    
    $sql = "INSERT INTO utilisateur(utPseudo, utPasse, utEmail, utNom, utPrenom, utDateNaissance, utCivilite, utMailsPourris) 
            VALUES ('{$pseudoe}','{$passe}','{$emaile}', '{$nom}', '{$prenom}', {$annee}{$mois}{$jour}, '$civilite', $mailsPourris)";
        
    mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
    
    // enregistrement dans la variable de session du pseudo avant passage par la fonction mysqli_real_escape_string()
    // car, d'une façon générale, celle-ci risque de rajouter des antislashs
    // Rappel : ici, elle ne rajoute jamais d'antislash car le pseudo ne peut contenir que des caractères alphanumériques
    $_SESSION['user'] = array('pseudo' => $pseudo, 'redacteur' => false, 'administrateur' => false);
    
    // fermeture de la connexion à la base de données
    mysqli_close($bd);
    
    // redirection sur la page protegee.php
    header('location: ./protegee.php');    // TODO : A MODIFIER DANS LE PROJET
    exit(); //===> Fin du script
}

//___________________________________________________________________
/**
 * Vérification des champs nom et prénom
 *
 * @param  string       $texte champ à vérifier
 * @param  string       $nom chaîne à ajouter dans celle qui décrit l'erreur
 * @param  array        $erreurs tableau dans lequel les erreurs sont ajoutées
 * @param  int          $long longueur maximale du champ correspondant dans la base de données
 */
function eml_verifier_texte($texte, $nom, &$erreurs, $long = -1){
    mb_regex_encoding ('UTF-8'); //définition de l'encodage des caractères pour les expressions rationnelles multi-octets
    if (empty($texte)){
        $erreurs[] = "$nom ne doit pas être vide.";
    }
    else if(strip_tags($texte) != $texte){
        $erreurs[] = "$nom ne doit pas contenir de tags HTML";
    }
    elseif ($long > 0 && mb_strlen($texte, 'UTF-8') > $long){
        // mb_* -> pour l'UTF-8, voir : https://www.php.net/manual/fr/function.mb-strlen.php
        $erreurs[] = "$nom ne peut pas dépasser $long caractères";
    }
    elseif(!mb_ereg_match('^[[:alpha:]]([\' -]?[[:alpha:]]+)*$', $texte)){
        $erreurs[] = "$nom contient des caractères non autorisés";
    }
}

?>
