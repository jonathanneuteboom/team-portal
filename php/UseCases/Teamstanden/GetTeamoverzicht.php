<?php
include 'IInteractorWithData.php';

class GetTeamoverzicht implements IInteractorWithData
{

    public function __construct()
    {

    }

    public function Execute($data)
    {
        $teamnaam = $data['team'] ?? null;
        if ($teamnaam == null) {
            InternalServerError("Teamnaam is leeg");
        }

        $filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . "teamoverzichten.json";
        if (!file_exists($filename)) {
            exit("Overzichtsbestand bestaat niet");
        }

        $overzichten = json_decode(file_get_contents($filename), true);
        $teamoverzicht = $this->GetTeamOverzicht($overzichten, $teamnaam);

        $poule = GetKlasse($teamoverzicht['poule']);
        $klasseUrl = "https://www.volleybal.nl/competitie/poule/" . $poule . "/regio-west";
        $klasse = GetKlasse($teamoverzicht['poule']);
        $trainer = $teamoverzicht['trainer'];
        $stand = $teamoverzicht['stand'];
        $uitslagen = $teamoverzicht['uitslagen'];
        $programma = $teamoverzicht['programma'];
        $trainingstijden = $teamoverzicht['trainingstijden'];
        $coaches = $teamoverzicht['coaches'];
        $facebook = $teamoverzicht['facebook'];

        $template = file_get_contents("./UseCases/Teamstanden/templates/teamoverzicht.html");
        $template = str_replace("{{TEAMNAAM}}", $teamnaam, $template);
        $template = str_replace("{{KLASSE}}", GetKlasse($teamoverzicht['poule']), $template);
        $template = str_replace("{{KLASSE_URL}}", $klasseUrl, $template);
        $template = str_replace("{{TRAINER}}", $trainer, $template);
        $template = str_replace("{{TRAININGSTIJDEN}}", $trainingstijden, $template);
        $template = str_replace("{{COACHES}}", $coaches, $template);
        $template = str_replace("{{FACEBOOK}}", $facebook, $template);
        $template = str_replace("{{POULE}}", $poule, $template);

        $html = "";
        foreach ($stand as $team) {
            $style = "";
            $number = substr($teamnaam, 5);

            $html .= "<tr style=" . ($team["team"] == $teamnaam ? "font-weight: bold;" : "") . ">
                        <td>" . $team["nummer"] . "</td>
                        <td>" . $team["team"] . "</td>
                        <td>" . $team["wedstrijden"] . "</td>
                        <td>" . $team["punten"] . "</td>
                      </tr>";
        }
        $template = str_replace("{{STAND}}", $html, $template);

        if (empty($uitslagen)) {
            $html = "<tr><td colspan=3>Nog geen uitslagen</td></tr>";
        } else {
            $html = "";
            foreach ($uitslagen as $uitslag) {
                $html .= "<tr>
                            <td>" . $uitslag["team1"] . " - " . $uitslag["team2"] . "</td>
                            <td>" . $uitslag['uitslag'] . "</td>
                            <td>" . implode(", ", $uitslag['setstanden']) . "</td>
                          </tr>";
            }
        }
        $template = str_replace("{{UITSLAGEN}}", $html, $template);

        if (empty($programma)) {
            $html = "<tr><td colspan=3>Geen programma</td></tr>";
        } else {
            $html = "";
            foreach ($programma as $wedstrijd) {
                $dateTime = new DateTime($wedstrijd["timestamp"]['date']);
                $html .= "<tr>
                            <td>" . $dateTime->format("d-m-Y") . "</td>
                            <td>" . $dateTime->format("H:i") . "</td>
                            <td>" . $wedstrijd["team1"] . " - " . $wedstrijd["team2"] . "</td>
                            <td>" . $wedstrijd["locatie"] . "</td>
                          </tr>";
            }
        }
        $template = str_replace("{{PROGRAMMA}}", $html, $template);

        exit($template);
    }

    private function GetTeamOverzicht($overzichten, $teamnaam)
    {
        foreach ($overzichten as $overzicht) {
            if ($overzicht['naam'] == $teamnaam) {
                return $overzicht;
            }
        }
        exit("Team $teamnaam niet gevonden in overzichtsbestand");
    }
}