<?php
include 'IInteractor.php';
include 'JoomlaGateway.php';

class IsWebcie implements IInteractor
{

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute()
    {
        $isWebcie = false;
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId != null) {
            $isWebcie = $this->joomlaGateway->IsWebcie($userId);
        }
        exit(json_encode($isWebcie));
    }
}