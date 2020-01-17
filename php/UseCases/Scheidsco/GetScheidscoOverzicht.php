<?php

class GetScheidscoOverzicht implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        TelFluitGateway $telFluitGateway,
        NevoboGateway $NevoboGateway,
        ZaalwachtGateway $zaalwachtGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->nevoboGateway = $NevoboGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
    }

    public function Execute(): array
    {
        $result = [];
        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN', 365);

        foreach ($wedstrijddagen as $dag) {
            $zaalwacht = $this->zaalwachtGateway->GetZaalwacht($dag->date);
            $dag->zaalwacht = $zaalwacht !== null ? $zaalwacht->team : null;
            foreach ($dag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as &$wedstrijd) {
                    $indeling = $this->telFluitGateway->GetWedstrijd($wedstrijd->matchId);
                    if ($indeling !== null) {
                        $wedstrijd->scheidsrechter = $indeling->scheidsrechter;
                        $wedstrijd->telteam = $indeling->telteam;
                    }
                }
            }


            $result[] = $this->MapToUseCaseModel($dag);
        }

        return $result;
    }

    private function MapToUseCaseModel(Wedstrijddag $dag)
    {
        $result = (object) [
            "datum" => DateFunctions::GetDutchDate($dag->date),
            "date" => DateFunctions::GetYmdNotation($dag->date),
            "speeltijden" => [],
            "zaalwacht" => $dag->zaalwacht ? $dag->zaalwacht->GetShortNotation() : null
        ];
        foreach ($dag->speeltijden as $i => $speeltijd) {
            $result->speeltijden[] = (object) [
                'tijd' => DateFunctions::GetTime($speeltijd->time),
                'wedstrijden' => [],
            ];
            foreach ($speeltijd->wedstrijden as $wedstrijd) {
                $result->speeltijden[$i]->wedstrijden[] = (object) [
                    "id" => $wedstrijd->matchId,
                    "teams" => $wedstrijd->team1->naam . " - " . $wedstrijd->team2->naam,
                    "scheidsrechter" => $wedstrijd->scheidsrechter ? $wedstrijd->scheidsrechter->naam : null,
                    "tellers" => $wedstrijd->telteam ? $wedstrijd->telteam->naam : null,
                ];
            }
        }
        return $result;
    }
}
