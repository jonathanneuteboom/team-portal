<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Team;
use TeamPortal\Gateways\GespeeldeWedstrijdenGateway;
use TeamPortal\Gateways\JoomlaGateway;
use UnexpectedValueException;

class GetDwfPunten implements Interactor
{
    public function __construct(
        GespeeldeWedstrijdenGateway $gespeeldeWedstrijdenGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->gespeeldeWedstrijdenGateway = $gespeeldeWedstrijdenGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $matchId = $data->matchId ?? null;
        $matchIdregex = "/3000(B){0,1}[H|D]\d[A-Z] [(\d{2})|[A-Z]{2}/";
        if ($matchId && !preg_match_all($matchIdregex, $matchId)) {
            throw new UnexpectedValueException("matchId klopt niet: '$matchId'. Bv: 3000 H4G DG");
        }

        $team = !empty($data->team) ? new Team($data->team) : null;
        $teamRegex = "/SKC [HD]S \d{1,2}/";
        if ($team && !preg_match_all($teamRegex, $team->naam)) {
            throw new UnexpectedValueException("team klopt niet: '$team->naam'. Bv: SKC HS 2");
        }

        if ($team === null && $matchId === null) {
            $punten = $this->gespeeldeWedstrijdenGateway->GetAlleSkcPunten();
        } else if ($team !== null && $matchId !== null) {
            $punten = $this->gespeeldeWedstrijdenGateway->GetAllePuntenByMatchId($matchId, $team);
        } else if ($team !== null) {
            $punten = $this->gespeeldeWedstrijdenGateway->GetAllePuntenByTeam($team);
        } else {
            throw new UnexpectedValueException("Error: team: '$matchId', '$team'");
        }

        return $punten;
    }
}
