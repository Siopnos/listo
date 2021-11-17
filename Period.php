<?php

class Period {

  private String $firstDay;
  private String $lastDay;
  private String $format = "Y-m-d H:i:s";

  /* Constructeur
  une période est forcément définies avec une date de début et une date de fin
  on peut donc créer l'objet en y passant en paramètre la date de début et la date de fin
  mais on peut également simplement y passer une date seul afin d'obtenir la période de paiement correspondante
  ex : 12/2021 donnera la période 01/12/2021 00:00:00 à 31/12/2021 23:59:59
  */
  public function __construct($first, $last = null)
  {
    if ($last == null)
    {
      $first = $this->getValideDateFormat($first);
      $parts = explode("-", trim($first));

      $this->firstDay = date($this->format, mktime(0,0,0, $parts[1], 1, $parts[0]));
      $this->lastDay = date($this->format, mktime(23,59,59, $parts[1]+1, 0, $parts[0]));
    } else {
      $this->setFirstDay($first);
      $this->setLastDay($last);
    }
  }

  /* setter
  Vérifie que le format passer en paramètre est valide puis attribue la date de début */
  public function setFirstDay($first)
  {
    if($this->getValideDateFormat($first)) {
      $first = $this->getValideDateFormat($first);
      $parts = explode("-", trim($first));
      echo $first;
      $this->firstDay = date($this->format, mktime(0,0,0,$parts[1], $parts[2], $parts[0]));
    } else {
      $this->firstDay = null;
    }
  }

  /* Setter
  Même fonction que le setter de date dé début */
  public function setLastDay($last)
  {
    if($this->getValideDateFormat($last)) {
      $last = $this->getValideDateFormat($last);
      $parts = explode("-", trim($last));
      $this->lastDay = date($this->format, mktime(23,59,59,$parts[1], $parts[2], $parts[0]));
    } else {
      $this->lastDay = null;
    }
  }

  /* Getter */
  public function getFirstDay()
  {
    return $this->firstDay;
  }

  /* Getter */
  public function getLastDay ()
  {
    return $this->lastDay;
  }

  /* Fonction qui vérifie le format passé en entrée
  Les formats valides sont DD/MM/YYYY ou DD-MM-YYYY
  La fonction retourne la date au format YYYY-MM-DD
  Si le format passé en entrée correspond à MM/YYYY ou MM-YYYY, la fonction retourne une date au format YYYY-MM-01 pour avoir le premier jour du mois
  */
  private function getValideDateFormat($date)
  {
    if (preg_match("/[0-3][0-9]\/[0-1][0-9]\/[0-9]{4}/", trim($date)))
    {
        $parts = explode("/", trim($date));
        $fdate = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
        return $fdate;
    } elseif (preg_match("/[0-3][0-9]\-[0-1][0-9]\-[0-9]{4}/", trim($date))) {
      return $date;
    } elseif (preg_match("/[0-1][0-9]\-[0-9]{4}/", trim($date))) {
      $parts = explode("-", trim($date));
      $fdate = $parts[1] . "-" . $parts[0] . "-01";
      return $fdate;
    } elseif (preg_match("/[0-1][0-9]\/[0-9]{4}/", trim($date))) {
      $parts = explode("/", trim($date));
      $fdate = $parts[1] . "-" . $parts[0] . "-01";
      return $fdate;
    }  else {
      return false;
    }
  }

  /* Fonction permettant de vérifier sur la période est incluse dans une autre période donnée
  Renvoie true si la période est entièrement incluse
  Renvoie false sinon */
  public function isIncludedIn($testPeriod)
  {
    if ($this->firstDay >= $testPeriod->getfirstDay() && $this->firstDay <= $testPeriod->getlastDay()) {
      if ($this->lastDay >= $testPeriod->getfirstDay() && $this->lastDay <= $testPeriod->getlastDay()) {
        return true;
      }
    }
    return false;
  }

  /* Fonction permettant de vérifier si le dernier jour de la période est inclus dans une période donnée */
  private function lastDayIsIncludedIn($testPeriod)
  {
    if ($this->lastDay >= $testPeriod->getfirstDay() && $this->lastDay <= $testPeriod->getlastDay()) {
      return true;
    }
    return false;
  }

  /* Fonction permettant d'avoir la période de paiment du mois suivant */
  public function getNextPaymentMonth()
  {
    $f = date("d/m/Y", strtotime($this->getFirstDay(). "+1 month"));
    return new Period($f);
  }

  /* Fonction permettant d'obtenir l'ensemble des périodes de congés découpées selon les périodes de paiements mensuelles
  Retourne les périodes sous forme d'objet Period au sein d'un tableau */
  public function getPaymentPeriod()
  {
    $holidayPaymentPeriods = Array();
    $paymentPeriod = new Period(date("d/m/Y", strtotime($this->firstDay)));

    /* Si la période de congés est inclus entièrement dans une seule période de paiement mensuelle,
    alors on retourne le tableau avec pour seule entrée la période de congés */
    if ($this->isIncludedIn($paymentPeriod))
    {
      array_push($holidayPaymentPeriods, $this);
      return $holidayPaymentPeriods;
    }

    else {
      /* Si ce n'est pas le cas, la première période est égale à :
      - date de début du congés jusqu'à date de fin de mois */
      $thisPaymentPeriod = new Period(date("d/m/Y", strtotime($this->firstDay)), date("d/m/Y", strtotime($paymentPeriod->getLastDay())));
      array_push($holidayPaymentPeriods, $thisPaymentPeriod);
      while(true)
      {
        /* On récupère la période de paiment mensuel suivante */
        $nextPaymentPeriod = $paymentPeriod->getNextPaymentMonth();

        /* On vérifie si le dernier jour de la période de congés est inclus dans la période de paiement mensuelle
          Si c'est le cas, alors la période de congé à prendre en compte est égale à :
          - date de début de mois jusqu'à date de fin de congés */
        if ($this->lastDayIsIncludedIn($nextPaymentPeriod))
        {
          $thisPaymentPeriod = new Period(date("d/m/Y", strtotime($nextPaymentPeriod->getFirstDay())), date("d/m/Y", strtotime($this->getLastDay())));
          array_push($holidayPaymentPeriods, $thisPaymentPeriod);
          break;
        }

        /* Si la date de congés n'est pas incluse dans la période de paiement mensuelle, alors la date de congés à prendre en compte est également à
        la période de paiement mensuelle dans son ensemble.
        */
        $paymentPeriod = $nextPaymentPeriod;
        array_push($holidayPaymentPeriods, $paymentPeriod);

      }
      return $holidayPaymentPeriods;
    }
  }
}
?>
