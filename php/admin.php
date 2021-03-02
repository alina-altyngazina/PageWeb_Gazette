<?php
require_once('./bibli_gazette.php');
require_once('./bibli_generale.php');

// bufferisation des sorties
ob_start();

// démarrage de la session
session_start();

// si l'utilisateur est déjà authentifié
if (isset($_SESSION['user'])){
    $utPseudo=$_SESSION['user']['pseudo'];
    $droits=array($_SESSION['user']['redacteur'],$_SESSION['user']['administrateur']);
}else{
    $utPseudo='';
    $droits=array(false,false); 
}
// ouverture de la connexion à la base 
$bd = em_bd_connecter();
//Requete qui recupere le nombre de commentaire ecrit, le nombre d'article ecrit, le statut de chaque pseudo.
$sql="SELECT COUNT(coID) as Nombre_Commentaire, COUNT(arID) as Nombre_Article,utPseudo, utStatut FROM commentaire RIGHT JOIN utilisateur ON coAuteur=utPseudo LEFT JOIN article ON arAuteur=utPseudo GROUP BY utPseudo";
$res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

//Requete qui recupere la Moyenne de commentaire recu pour chaque pseudo.
$sql="SELECT utPseudo ,COUNT(coID)/ COUNT( DISTINCT ArID) as Moyenne FROM article LEFT OUTER JOIN commentaire ON arID = coArticle RIGHT OUTER JOIN utilisateur ON arAuteur=utPseudo GROUP BY utPseudo";
$res2 = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

// Page accessible uniquement aux utilisateurs authentifiés


//Si l'utilisateur clique sur modifier
if (isset($_POST['btnModifier'])) {
    ah_ModificationDroit($bd,$res);
    mysqli_free_result($res);
    //Requete qui recupere le nombre de commentaire ecrit, le nombre d'article ecrit, le statut de chaque pseudo.
    $sql="SELECT COUNT(coID) as Nombre_Commentaire, COUNT(arID) as Nombre_Article,utPseudo, utStatut FROM commentaire RIGHT JOIN utilisateur ON coAuteur=utPseudo LEFT JOIN article ON arAuteur=utPseudo GROUP BY utPseudo";
    $res = mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

}

// génération de la page
ah_aff_entete('administration', 'administration','..',$css='gazette.css',$utPseudo,$droits);
ah_affiche_Droit($res,$res2);

mysqli_free_result($res);
mysqli_free_result($res2);
//Fermeture de la connexion à la base de données
mysqli_close($bd);
em_aff_pied();

ob_end_flush();

/**
 * fonction pour affiche les droits des utilisateurs ainsi que le nombre de commentaire mis,
 * le nombre d'article écrits et le nombre moyen de commentaire reçu.
 *
 * @param bool    $res        res requete 
 * @param bool    $res2       resultat requete 
 */
function ah_affiche_Droit($res,$res2){
    //affichage des utilisateur et leur droit
    echo '<main>';
    echo '<section>',
        '<h2>Droits des utilisateurs</h2>', 
        '<p><strong>Modification Des Statut Des utilisateur</strong></p>',              
        '<form action="administration.php" method="post">';

    //si modification
    if (isset($_POST['btnModifier'])) {
        echo '<div class="reussite">Les modifications ont été effectuées.</div>';
    }
echo '<table>';
 while(($tabC=mysqli_fetch_row($res)) && ($tabM=mysqli_fetch_assoc($res2))){
echo '<article class="redacteur"> <h4>',$tabC[2],' :</h4> Commentaire(s) écrit(s):<strong>',$tabC[0],'</strong><br>  Article(s) écrit(s):<strong>',$tabC[1],'</strong><br>';
    if($tabM['Moyenne']==NULL ){
    echo 'Moyenne de commentaire recu par article écrit : <strong>0<br></strong>';
    }else{
    echo 'Moyenne de commentaire recu par article écrit : <strong>',$tabM['Moyenne'],'<br>';
    }
    em_aff_liste($tabC[2],array(0=>'Utilisateur',1=>'Rédacteur',2=>'Administrateur',3=>'Administrateur et Rédacteur'),$tabC[3]);
    echo '<br><br></article>';
    }
echo   '<tr>','<td colspan="2">',
'<input type="submit" name="btnModifier" value="Modifier">',
'<input type="reset" value="Réinitialiser">', 
'</td>','</tr></table>','</form>','</section>' ,'</main>';
}

/**
 * modifie les droits d'utilisateur.
 *
 * @param $res Resultat requete
 */
function ah_ModificationDroit($res){
    // ouverture de la connexion à la base 
    $bd = em_bd_connecter();
    //Parcours de tabC qui contien le nombre de commentaire et d'article
    while($tabC=mysqli_fetch_row($res)){
        $tabC = em_html_proteger_sortie($tabC);
        $utPseudo=$tabC[2];
        $StatutToModif=em_html_proteger_sortie($_POST[$utPseudo]);
       //si c le meme statut en fait rien sinan en change
        if($StatutToModif!=$tabC[3]){

            //modifier le statut de lutulisateur
            $sql = "UPDATE utilisateur SET utStatut='{$StatutToModif}'
            WHERE utPseudo='{$utPseudo}'";
            mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);

            //recuperer le repseudo qui correspond au utpseudo
            $sql="SELECT rePseudo FROM redacteur where rePseudo='{$utPseudo}'";
            $res3= mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
            $tab2=mysqli_fetch_assoc($res3);
            $tab2=em_html_proteger_sortie($tab2);
            //liberais les resultas
            mysqli_free_result($res3);

            //si c le meme statut en fait rien sinan en change
            if(($StatutToModif==1 || $StatutToModif==3) && ($tabC[3]==2 ||$tabC[3]==0) && $tab2['rePseudo']!=$utPseudo){
            $sql="INSERT INTO redacteur(rePseudo, reBio, reCategorie, reFonction) 
            VALUES ('{$utPseudo}','',3, NULL)";
            mysqli_query($bd, $sql) or em_bd_erreur($bd, $sql);
            }
        }

    }
}
?>