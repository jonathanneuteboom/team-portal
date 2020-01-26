<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities;
use TeamPortal\Gateways;

class MijnOverzicht implements Interactor
{
    public function __construct(
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\ZaalwachtGateway $zaalwachtGateway,
        Gateways\BarcieGateway $barcieGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute(object $data = null)
    {
        $overzicht = [];

        $user = $this->joomlaGateway->GetUser();

        $zaalwachten = $this->zaalwachtGateway->GetZaalwachtenOfUser($user);
        foreach ($zaalwachten as $zaalwacht) {
            $this->AddZaalwachtToOverzicht($overzicht, $zaalwacht);
        }

        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        $wedstrijden = $this->telFluitGateway->GetFluitEnTelbeurten($user);
        foreach ($wedstrijden as $wedstrijd) {
            $uscMatch = Entities\Wedstrijd::GetWedstrijdWithMatchId($uscWedstrijden, $wedstrijd->matchId);
            $wedstrijd->AppendInformation($uscMatch);
            $this->AddWedstrijdToOverzicht($overzicht, $wedstrijd);
        }

        $bardiensten = $this->barcieGateway->GetBardienstenForUser($user);
        foreach ($bardiensten as $bardienst) {
            $this->AddBardienstToOverzicht($overzicht, $bardienst);
        }

        $team = $this->joomlaGateway->GetTeam($user);
        $speelWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
        foreach ($speelWedstrijden as $wedstrijd) {
            $fluitEnTelWedstrijd = $this->telFluitGateway->GetWedstrijd($wedstrijd->matchId);
            $wedstrijd->AppendInformation($fluitEnTelWedstrijd);

            $this->AddWedstrijdToOverzicht($overzicht, $wedstrijd);
        }

        if ($user->coachteam) {
            $wedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($user->coachteam);
            foreach ($wedstrijden as $wedstrijd) {
                $this->AddWedstrijdToOverzicht($overzicht, $wedstrijd);
            }
        }

        usort($overzicht, Entities\Wedstrijddag::class . "::Compare");
        foreach ($overzicht as $dag) {
            usort($dag->speeltijden, Entities\Speeltijd::class . "::Compare");
        }

        return $this->MapToUseCaseModel($overzicht, $user);
    }

    private function MapToUseCaseModel(array $wedstrijddagen, Entities\Persoon $persoon): array
    {
        $result = [];
        foreach ($wedstrijddagen as $wedstrijddag) {
            $newWedstrijddag = new WedstrijddagModel($wedstrijddag);
            foreach ($newWedstrijddag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    $wedstrijd->SetPersonalInformation($persoon);
                }
            }
            $result[] = $newWedstrijddag;
        }

        return $result;
    }

    private function AddZaalwachtToOverzicht(array &$dagen, Entities\Zaalwacht $zaalwacht): void
    {
        foreach ($dagen as $dag) {
            if (DateFunctions::AreDatesEqual($dag->date, $zaalwacht->date)) {
                $dag->zaalwacht = $zaalwacht;
                return;
            }
        }
        $dag = new Entities\Wedstrijddag($zaalwacht->date);
        $dag->zaalwacht = $zaalwacht;
        $dagen[] = $dag;
    }

    private function AddWedstrijdToOverzicht(array &$dagen, Entities\Wedstrijd $wedstrijd): void
    {
        foreach ($dagen as $dag) {
            if (DateFunctions::AreDatesEqual($dag->date, $wedstrijd->timestamp)) {
                foreach ($dag->speeltijden as $speeltijd) {
                    if ($speeltijd->time == $wedstrijd->timestamp) {
                        $speeltijd->wedstrijden[] = $wedstrijd;
                        return;
                    }
                }
                $speeltijd = new Entities\Speeltijd($wedstrijd->timestamp);
                $speeltijd->wedstrijden[] = $wedstrijd;
                $dag->speeltijden[] = $speeltijd;
                return;
            }
        }

        $dag = new Entities\Wedstrijddag($wedstrijd->timestamp);
        $speeltijd = new Entities\Speeltijd($wedstrijd->timestamp);
        $speeltijd->wedstrijden[] = $wedstrijd;
        $dag->speeltijden[] = $speeltijd;
        $dagen[] = $dag;
    }

    private function AddBardienstToOverzicht(array &$dagen, Entities\Bardienst $dienst)
    {
        foreach ($dagen as $dag) {
            if (DateFunctions::AreDatesEqual($dag->date, $dienst->bardag->date)) {
                $dag->bardiensten[] = $dienst;
                return;
            }
        }

        $dag = new Entities\Wedstrijddag($dienst->bardag->date);
        $dag->bardiensten[] = $dienst;
        $dagen[] = $dag;
    }
}
