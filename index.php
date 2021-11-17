<?php
require_once "Class/Period.php";

/* Création d'une période de congés sous forme d'objet Period avec en paramètre
- une date de début sous forme "DD/MM/AAAA"
- une date de fin sous forme "DD/MM/AAAA"
 */
$conges = new Period("10/11/2021", "20/12/2021");

/* Création d'une période de paie sous forme d'objet Period avec en paramètre :
- une date sous forme MM/imageantialias*/
$paie = new Period("12/2021");


echo 'Date début congés : '.$conges->getFirstDay();
echo '<br />Date fin congés : '.$conges->getLastDay();

echo '<br /><br /><b>[Test]</b> Obtenir la période mensuelle qui a été instancier <br />';
echo '<br /> Date de début de la période de paie mensuelle : '.$paie->getFirstDay();
echo '<br />Date de fin de la période de paie mensuelle : '.$paie->getLastDay();


/* Elaboration d'un test unitaire permettant de vérifier si la période de congés est entièrement incluse ou non dans la période de paie mensuelle */
echo '<br /><br /><b>[Test]</b> Vérifier si la période de congés est incluse ou non dans la période de paie mensuelle <br />';
if ($conges->isIncludedIn($paie))
{
  echo '<br />La période de congés du '.$conges->getFirstDay().' au '.$conges->getLastDay().' est entièrement incluse dans la période de paiement mensuelle qui s\'étend du '.$paie->getFirstDay(). ' au '.$paie->getLastDay();
}
else {
  echo '<br />La période de congés du '.$conges->getFirstDay().' au '.$conges->getLastDay().' n\'est pas entièrement incluse dans la période de paiement mensuelle qui s\'étend du '.$paie->getFirstDay(). ' au '.$paie->getLastDay();
}

/* Elaboration d'un test unitaire permettant de récupérer les périodes de congés à enregistrer */
echo '<br /><br /><b>[Test]</b> Récupération de l\'ensemble des périodes de congés à enregistrer <br />';
$paymentPeriods = $conges->getPaymentPeriod();
echo '<br />Les périodes à enregistrer pour la période de congés du '.$conges->getFirstDay().' au '.$conges->getLastDay().' sont :';
foreach($paymentPeriods as $pp)
{
  echo '<br />- '.$pp->getFirstDay().' au '.$pp->getLastDay();
}

?>
