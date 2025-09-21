<?php

require_once "CarrieraLaureando.php";

class LaureandoInformatica extends CarrieraLaureando {
    private bool $bonus;
    private static array $esamiInf = [];

    public function __construct(string $matricola, string $dataLaurea, string $cdl) {
        parent::__construct($matricola, $dataLaurea, $cdl);

        //CONTROLLO IL BONUS
        $laurea = strtotime($dataLaurea);

        $imm = strtotime($this->annoImm . "-05-31");
        $dataLimite = strtotime("+4 years", $imm);

        $this->bonus = ($laurea <= $dataLimite);


        //SCELGO ESAME DA TOGLIERE DALLA MEDIA
        if($this->bonus) {
            usort($this->esami, function ($a, $b) {
                if ($a['Voto'] == $b['Voto']) {
                    if($a['CFU'] == $b['CFU']){
                        $dataA = $a['Data']->getTimestamp();
                        $dataB = $b['Data']->getTimestamp();
                        return ($dataA - $dataB);
                    }
                    return $b['CFU'] - $a['CFU'];
                }
                return $a['Voto'] - $b['Voto'];
            });

            foreach ($this->esami as &$esame) {
                if ($esame['Media']) {
                    $esame['Media'] = false;
                    break;
                }
            }
        }


        //AGGIUNGO IL FLAG AGLI ESAMI INFORMATICI
        if (empty(self::$esamiInf)) {
            $dati = file_get_contents(__DIR__ . '/../configurazione/esami_informatici.json');
            self::$esamiInf = json_decode($dati, true)['esami_informatici'];
        }

        foreach ($this->esami as &$esame) {             //prendo il referimento
            if (in_array($esame['Esame'], self::$esamiInf)) {
                $esame["INF"] = true;
            } else {
                $esame["INF"] = false;
            }
        }
    }

    public function calcolaMediaPesataINF(): float
    {
        $num = 0;
        $den = 0;

        foreach ($this->esami as $esame) {
            if ($esame["INF"]) {
                $num += $esame['Voto'] * $esame['CFU'];
                $den += $esame['CFU'];
            }
        }

        $mediapesata = $num/$den;
        return round($mediapesata, 3);
    }

    public function getBonus(): string
    {
        return ($this->bonus)? 'SI' : 'NO';
    }
}