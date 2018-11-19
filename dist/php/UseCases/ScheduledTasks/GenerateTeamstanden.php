<?php
include_once 'IInteractor.php';
include_once 'AllSkcTeams.php';
include_once 'NevoboGateway.php';

class GenerateTeamstanden implements IInteractor
{

    public function __construct()
    {
        $this->nevoboGateway = new NevoboGateway();
    }

    public function Execute()
    {
        $teams = GetAllSkcTeams();
        foreach ($teams as &$team) {
            $rankings = $this->nevoboGateway->GetStandForPoule($team['poule']);

            $team["numberOfTeams"] = count($rankings);
            foreach ($rankings as $ranking) {
                if (strpos($ranking['team'], $team["naam"]) !== false) {
                    $team["stand"] = $ranking['nummer'];
                }
            }
        }

        if (count($teams) > 0) {
            file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . "teamstanden.json", json_encode($teams));
        }

        return [
            "teamstanden" => $teams,
        ];
    }
}
