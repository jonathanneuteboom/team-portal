<?php

namespace TeamPortal\Entities;

class DwfTimeout extends DwfActie
{
    public function __construct(int $puntenThuisTeam, int $puntenUitTeam, string $team)
    {
        $this->puntenThuisTeam = $puntenThuisTeam;
        $this->puntenUitTeam = $puntenUitTeam;
        $this->team = $team;
    }

    public function WisselTeams()
    {
        $this->WisselPunten();

        $this->team = ThuisUit::WisselTeam($this->team);
    }
}
