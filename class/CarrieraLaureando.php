<?php

require_once "GestioneCarrieraStudente.php";
require_once "FiltroEsami.php";

class CarrieraLaureando{
    private string $matricola;
    private string $nome;
    private string $cognome;
    private string $email;
    private string $dataLaurea;
    protected int $annoImm;     //usata nella classe figlia LaureandoInformatica,
                                //inserita come variabile locale per non fare nuovo accesso al file JSON
    protected array $esami = [];       //usata nella classe figlia LaureandoInformatica

    private static array $instances = [];

    public function __construct(string $matricola, string $dataLaurea, string $cdl)
    {
        $dati = new GestioneCarrieraStudente();

        //il secondo paramentro a true crea direttamente array associativo
        $anagrafica = json_decode($dati->restituisciAnagraficaStudente($matricola), true);
        $carriera = json_decode($dati->restituisciCarrieraStudente($matricola), true);

        //semplifico array
        $anagrafica = $anagrafica['Entries']['Entry'];
        $carriera = $carriera['Esami']['Esame'];

        //Inserisco i dati
        $this->matricola = $matricola;
        $this->nome = $anagrafica['nome'];
        $this->cognome = $anagrafica['cognome'];
        $this->email = $anagrafica['email_ate'];
        $this->dataLaurea = $dataLaurea;
        $this->annoImm = $carriera[0]['ANNO_IMM'];


        //Prendo i dati da filtro esami
        $filtro = new FiltroEsami($cdl);

        //crea il vettore esami
        $this->esami = array();
        foreach ($carriera as $esame){
            if($esame['SOVRAN_FLG'] != 0) {
                continue;
            }

            if(in_array($esame['DES'], $filtro->getAttributes()['nonCurr'])){
                continue;
            }

            if(in_array($esame['DES'], $filtro->getAttributes()['nonAVG'])){         //per gli esami di idoneità
            $this->esami[] = array(
                        "Esame" => $esame['DES'], "CFU" => $esame['PESO'],
                        "Voto" => 0, "Media" => false,
                        "Data" => date_create_from_format('d/m/Y', $esame['DATA_ESAME']));
            } else {
                $this->esami[] = array(
                            "Esame" => $esame['DES'], "CFU" => $esame['PESO'],
                            "Voto" => ($esame['VOTO'] == '30  e lode')? 33 : $esame['VOTO'], "Media" => true,
                            "Data" => date_create_from_format('d/m/Y', $esame['DATA_ESAME']));
            }
        }

    }

    public function calcolaMediaPesata(): float
    {
        $num = 0;
        $den = $this->calcolaCreditiMedia();

        foreach ($this->esami as $esame){
            if($esame['Media']) {
                $num += $esame['Voto'] * $esame['CFU'];
            }
        }

        $mediapesata = $num/$den;
        return round($mediapesata, 3);
    }

    public function calcolaCreditiConseguiti(): int
    {
        $crediti = 0;
        foreach ($this->esami as $esame){
                $crediti += $esame['CFU'];
        }
        return $crediti;
    }

    public function calcolaCreditiMedia(): int
    {
        $crediti = 0;
        foreach ($this->esami as $esame){
            if($esame['Media']) {
                $crediti += $esame['CFU'];
            }
        }
        return $crediti;
    }

    //usata da ProspettoPDFLaureando
    public function getAttributi(): array
    {

        // Utilizza usort per ordinare l'array usando la funzione di confronto
        usort($this->esami, function ($a, $b) {
            $dateA = $a['Data']->getTimestamp();
            $dateB = $b['Data']->getTimestamp();

            return ($dateA - $dateB);
        });

        return [
            'matricola' => $this->matricola,
            'nome' => $this->nome,
            'cognome' => $this->cognome,
            'email' => $this->email,
            'dataLaurea' => $this->dataLaurea,
            'esami' => $this->esami,
        ];
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    //Usata per invio mail
    public static function ottieniEmail(string $matricola): string
    {
        if (isset(self::$instances[$matricola])) {
            return self::$instances[$matricola]->getEmail();
        } else {
            $dati = file_get_contents(__DIR__ . '/../data/' . $matricola . '_anagrafica.json');
            $anagrafica = json_decode($dati, true);

            return $anagrafica['Entries']['Entry']['email_ate'];
        }
    }
}
