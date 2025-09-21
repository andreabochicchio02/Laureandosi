<?php

class Configurazione{
    private string $cdlShort;
    private string $formulaVoto;
    private int $cfuRichiesti;
    private array $parametriTesi;
    private array $parametriCommissione;
    private int $lode;
    private string $note;
    private string $oggettoMessaggio;
    private string $textEmail;
    private static array $instances = [];


    public function __construct(string $cdl)
    {
        $dati = file_get_contents(__DIR__ . '/../configurazione/calcolo_e_reportistica.json');
        $info = json_decode($dati, true)[$cdl];

        $this->cdlShort = $info['cdl_short'];
        $this->formulaVoto = $info['formula_voto'];
        $this->cfuRichiesti = $info['tot_CFU'];
        $this->parametriTesi = array(
                        "min" => $info['Tmin'],
                        "max" => $info['Tmax'],
                        "step" => $info['Tstep']
                        );
        $this->parametriCommissione = array(
            "min" => $info['Cmin'],
            "max" => $info['Cmax'],
            "step" => $info['Cstep']
        );

        $this->lode = $info['lode'];
        $this->note = $info['note'];
        $this->oggettoMessaggio = $info['oggetto_messaggio'];
        $this->textEmail = $info['text_email'];

    }

    public function getInfoCdl(): array
    {
        return [
            'cdlShort' => $this->cdlShort,
            'formulaVoto' => $this->formulaVoto,
            'cfuRichiesti' => $this->cfuRichiesti,
            'parametriTesi' => $this->parametriTesi,
            'parametriCommissione' => $this->parametriCommissione,
            'lode' => $this->lode,
            'nota' => $this->note
        ];
    }

    public function getInfoMail(): array
    {
        return [
            'oggetto' => $this->oggettoMessaggio,
            'testoMail' => $this->textEmail
        ];
    }

    public static function ottieniIstanza(string $cdl): Configurazione
    {
        if (!isset(self::$instances[$cdl])) {
            self::$instances[$cdl] = new Configurazione($cdl);
        }
        return self::$instances[$cdl];
    }
}



