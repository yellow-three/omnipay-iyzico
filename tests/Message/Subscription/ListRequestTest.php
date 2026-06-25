<?php

namespace Omnipay\Iyzico\Tests\Message\Subscription;

use Iyzipay\ApiResource;
use Iyzipay\HttpClient;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Iyzico\Message\Response;
use Omnipay\Iyzico\Message\Subscription\ListRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ListRequestTest extends TestCase
{
    private ListRequest $request;

    protected function setUp(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpRequest = $this->createMock(HttpRequest::class);
        $this->request = new ListRequest($httpClient, $httpRequest);
    }

    protected function tearDown(): void
    {
        ApiResource::setHttpClient(null);
    }

    public function testGetDataReturnsCorrectArray(): void
    {
        $this->request->setPage(1);
        $this->request->setCount(10);
        $this->request->setSubscriptionReferenceCode('sub_ref_123');
        $this->request->setSubscriptionStatus('ACTIVE');
        $this->request->setCustomerReferenceCode('cust_ref_456');
        $this->request->setParentReferenceCode('parent_ref_789');
        $this->request->setPricingPlanReferenceCode('plan_ref_000');
        $this->request->setStartDate('2024-01-01');
        $this->request->setEndDate('2024-12-31');
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_123', $data['conversationId']);
        $this->assertSame(1, $data['page']);
        $this->assertSame(10, $data['count']);
        $this->assertSame('sub_ref_123', $data['subscriptionReferenceCode']);
        $this->assertSame('ACTIVE', $data['subscriptionStatus']);
        $this->assertSame('cust_ref_456', $data['customerReferenceCode']);
        $this->assertSame('parent_ref_789', $data['parentReferenceCode']);
        $this->assertSame('plan_ref_000', $data['pricingPlanReferenceCode']);
        $this->assertSame('2024-01-01', $data['startDate']);
        $this->assertSame('2024-12-31', $data['endDate']);
    }

    public function testGetDataReturnsDefaultsForOptionalFields(): void
    {
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_default');

        $data = $this->request->getData();

        $this->assertSame('TR', $data['locale']);
        $this->assertSame('conv_default', $data['conversationId']);
        $this->assertNull($data['page']);
        $this->assertNull($data['count']);
        $this->assertNull($data['subscriptionReferenceCode']);
        $this->assertNull($data['subscriptionStatus']);
        $this->assertNull($data['customerReferenceCode']);
        $this->assertNull($data['parentReferenceCode']);
        $this->assertNull($data['pricingPlanReferenceCode']);
        $this->assertNull($data['startDate']);
        $this->assertNull($data['endDate']);
    }

    public function testGetPage(): void
    {
        $this->request->setPage(2);

        $this->assertSame(2, $this->request->getPage());
    }

    public function testSetPageReturnsSelf(): void
    {
        $result = $this->request->setPage(2);

        $this->assertSame($this->request, $result);
    }

    public function testGetCount(): void
    {
        $this->request->setCount(20);

        $this->assertSame(20, $this->request->getCount());
    }

    public function testGetSubscriptionReferenceCode(): void
    {
        $this->request->setSubscriptionReferenceCode('sub_ref_456');

        $this->assertSame('sub_ref_456', $this->request->getSubscriptionReferenceCode());
    }

    public function testGetSubscriptionStatus(): void
    {
        $this->request->setSubscriptionStatus('PENDING');

        $this->assertSame('PENDING', $this->request->getSubscriptionStatus());
    }

    public function testGetCustomerReferenceCode(): void
    {
        $this->request->setCustomerReferenceCode('cust_ref_789');

        $this->assertSame('cust_ref_789', $this->request->getCustomerReferenceCode());
    }

    public function testGetParentReferenceCode(): void
    {
        $this->request->setParentReferenceCode('parent_ref_000');

        $this->assertSame('parent_ref_000', $this->request->getParentReferenceCode());
    }

    public function testGetPricingPlanReferenceCode(): void
    {
        $this->request->setPricingPlanReferenceCode('plan_ref_111');

        $this->assertSame('plan_ref_111', $this->request->getPricingPlanReferenceCode());
    }

    public function testGetStartDate(): void
    {
        $this->request->setStartDate('2024-06-01');

        $this->assertSame('2024-06-01', $this->request->getStartDate());
    }

    public function testGetEndDate(): void
    {
        $this->request->setEndDate('2024-07-01');

        $this->assertSame('2024-07-01', $this->request->getEndDate());
    }

    public function testSendDataReturnsResponse(): void
    {
        $this->request->setPage(1);
        $this->request->setCount(10);
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_123');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('getV2')
            ->willReturn(json_encode([
                'status' => 'success',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_123',
                'totalCount' => 5,
                'currentPage' => 1,
                'pageCount' => 1,
                'items' => [],
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('conv_123', $response->getConversationId());
    }

    public function testSendDataWithFailedRequestReturnsFailedResponse(): void
    {
        $this->request->setPage(1);
        $this->request->setCount(10);
        $this->request->setLocale('TR');
        $this->request->setConversationId('conv_fail');
        $this->request->setApiKey('fake-api-key');
        $this->request->setSecretKey('fake-secret-key');
        $this->request->setBaseUrl('https://sandbox-api.iyzipay.com');

        $data = $this->request->getData();

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('getV2')
            ->willReturn(json_encode([
                'status' => 'failure',
                'errorCode' => '5008',
                'errorMessage' => 'Subscription listing failed',
                'errorGroup' => 'LIST_ERROR',
                'locale' => 'TR',
                'systemTime' => '1458545234852',
                'conversationId' => 'conv_fail',
            ]));

        ApiResource::setHttpClient($httpClient);

        $response = $this->request->sendData($data);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('5008', $response->getCode());
        $this->assertStringContainsString('Subscription listing failed', $response->getMessage());
    }
}
