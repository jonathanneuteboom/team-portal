<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Niveau;
use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Team;

class TeamoverzichtModel
{
    public function __construct(Team $team)
    {
        $this->id = $team->id;
        $this->naam = $team->naam;
        $this->poule = $team->poule;
        foreach ($team->teamgenoten as $teamgenoot) {
            $this->teamgenoten[] = new PersoonModel($teamgenoot);
        }
        $this->niveau = Niveau::GetNiveauString($team->niveau);
        $this->facebook = $team->facebook;
        $this->trainingstijden = $team->trainingstijden;

        foreach ($team->uitslagen as $wedstrijd) {
            $this->uitslagen[] = new WedstrijdModel($wedstrijd);
        }

        $this->stand = $team->stand;

        $this->programma = [];
        foreach ($team->programma as $wedstrijd) {
            $this->programma[] = new WedstrijdModel($wedstrijd);
        }

        $trainers = array_map(function (Persoon $trainer) {
            return $trainer->naam;
        }, $team->trainers);
        $this->trainers = implode(", ", $trainers);

        $coaches = array_map(function (Persoon $coach) {
            return $coach->naam;
        }, $team->coaches);
        $this->coaches = implode(", ", $coaches);
    }
}
