<?php

namespace TeamPortal\Entities;

class DwfPunt extends DwfActie
{
    public function __construct(
        int $puntenThuisTeam,
        int $puntenUitTeam,
        string $scorendTeam,
        string $serverendTeam,
        int $serveerder
    ) {
        $this->puntenThuisTeam = $puntenThuisTeam;
        $this->puntenUitTeam = $puntenUitTeam;
        $this->scorendTeam = $scorendTeam;
        $this->serverendTeam = $serverendTeam;
        $this->serveerder = $serveerder;
    }

    public function WisselTeams()
    {
        $this->WisselPunten();

        $this->scorendTeam = ThuisUit::WisselTeam($this->scorendTeam);
        $this->serverendTeam = ThuisUit::WisselTeam($this->serverendTeam);
    }
}
