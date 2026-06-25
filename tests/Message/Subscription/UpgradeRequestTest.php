<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\UpgradeRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class UpgradeRequestTest extends TestCase
{
    private UpgradeRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new UpgradeRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_123');
        $this->request->setNewPricingPlanReferenceCode('plan_ref_456');
        $this->request->setUpgradePeriod('NOW');
        $this->request->setUseTrial(true);
        $this->request->setResetRecurrenceCount(false);
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame('sub_ref_123', $data['subscriptionReferenceCode']);
        $this->assertSame('plan_ref_456', $data['newPricingPlanReferenceCode']);
        $this->assertSame('NOW', $data['upgradePeriod']);
        $this->assertTrue($data['useTrial']);
        $this->assertFalse($data['resetRecurrenceCount']);
    }

    public function testGetDataThrowsWhenSubscriptionReferenceCodeMissing(): void
    {
        $this->request->setNewPricingPlanReferenceCode('plan_ref_456');

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The subscriptionReferenceCode parameter is required');

        $this->request->getData();
    }

    public function testGetDataThrowsWhenNewPricingPlanReferenceCodeMissing(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_123');

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The newPricingPlanReferenceCode parameter is required');

        $this->request->getData();
    }

    public function testGetSubscriptionReferenceCode(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_456');

        $this->assertSame('sub_ref_456', $this->request->getSubscriptionReferenceCode());
    }

    public function testSetSubscriptionReferenceCodeReturnsSelf(): void
    {
        $result = $this->request->setSubscriptionReferenceCode('sub_ref_456');

        $this->assertSame($this->request, $result);
    }

    public function testGetNewPricingPlanReferenceCode(): void
    {
        $this->request->setNewPricingPlanReferenceCode('plan_ref_789');

        $this->assertSame('plan_ref_789', $this->request->getNewPricingPlanReferenceCode());
    }

    public function testSetNewPricingPlanReferenceCodeReturnsSelf(): void
    {
        $result = $this->request->setNewPricingPlanReferenceCode('plan_ref_789');

        $this->assertSame($this->request, $result);
    }

    public function testGetUpgradePeriod(): void
    {
        $this->request->setUpgradePeriod('PERIOD_END');

        $this->assertSame('PERIOD_END', $this->request->getUpgradePeriod());
    }

    public function testSetUpgradePeriodReturnsSelf(): void
    {
        $result = $this->request->setUpgradePeriod('PERIOD_END');

        $this->assertSame($this->request, $result);
    }

    public function testGetUseTrial(): void
    {
        $this->request->setUseTrial(true);

        $this->assertTrue($this->request->getUseTrial());
    }

    public function testSetUseTrialReturnsSelf(): void
    {
        $result = $this->request->setUseTrial(true);

        $this->assertSame($this->request, $result);
    }

    public function testGetResetRecurrenceCount(): void
    {
        $this->request->setResetRecurrenceCount(false);

        $this->assertFalse($this->request->getResetRecurrenceCount());
    }

    public function testSetResetRecurrenceCountReturnsSelf(): void
    {
        $result = $this->request->setResetRecurrenceCount(false);

        $this->assertSame($this->request, $result);
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_123');
        $this->request->setNewPricingPlanReferenceCode('plan_ref_456');
        $this->request->setUpgradePeriod('NOW');
        $this->request->setUseTrial(true);
        $this->request->setResetRecurrenceCount(false);
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_123',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_fail');
        $this->request->setNewPricingPlanReferenceCode('plan_ref_fail');
        $this->request->setUpgradePeriod('NOW');
        $this->request->setUseTrial(false);
        $this->request->setResetRecurrenceCount(false);
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fail');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5007',
                'errorMessage' => 'Subscription upgrade failed',
                'errorGroup' => 'UPGRADE_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5007', $response->getCode());
        $this->assertStringContainsString('Subscription upgrade failed', $response->getMessage());
    }
}
