<?php

declare(strict_types=1);

namespace TeamPortal\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Teamportal\Tests\GatewayMocks;
use TeamPortal\UseCases\AddBarcieAanwezigheid;

class BarcieTest extends TestCase
{
    public function TestWhenBarlidIdIsNullThrowException()
    {
        // Arrange
        $gatewayMocks = new GatewayMocks();
        $joomlaGateway = $gatewayMocks->GetJoomlaGateway();
        $barcieGateway = $gatewayMocks->GetBarcieGateway();

        $interactor = new AddBarcieAanwezigheid($joomlaGateway, $barcieGateway);


        $data = new AddBarcieRequestModel();
        $data->date = (new DateTime())->format("Y-m-d");
        $data->shift = 1;

        $this->expectException(\InvalidArgumentException::class);

        // act
        $response = $interactor->Execute($data);

        // assert
        $this->assertNull($response);
    }
}
