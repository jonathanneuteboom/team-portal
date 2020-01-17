<?php

class DeleteBarcieAanwezigheid implements Interactor
{
    public function __construct(
        BarcieGateway $barcieGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data): void
    {
        if ($data->barlidId === null) {
            throw new InvalidArgumentException("barlidId is leeg");
        }
        if ($data->date === null) {
            throw new InvalidArgumentException("Date is leeg");
        }
        if ($data->shift === null) {
            throw new InvalidArgumentException("Shift is leeg");
        }

        $date = DateFunctions::CreateDateTime($data->date);
        $barlid = $this->joomlaGateway->GetUser($data->barlidId);
        $bardag = $this->barcieGateway->GetBardag($date);
        if ($bardag->id === null) {
            return;
        }

        $dienst = $this->barcieGateway->GetBardienst($bardag, $barlid, $data->shift);
        if ($dienst !== null) {
            $this->barcieGateway->DeleteBardienst($dienst);
        }
    }
}
