<?php

namespace App\Tests\Behat\Customers;

use App\Tests\Behat\SendRequestTrait;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Session;

class SiteContext implements Context
{
    use SendRequestTrait;

    public function __construct(private Session $session)
    {
    }

    /**
     * @When I use the site to list customers
     */
    public function iUseTheSiteToListCustomers()
    {
        $this->sendJsonRequest('GET', sprintf('/app/customer'));
    }

    /**
     * @Then I should see in the site response the customer :arg1
     */
    public function iShouldSeeInTheSiteResponseTheCustomer($email)
    {
        $data = $this->getJsonContent();

        if (!isset($data['data'])) {
            throw new \Exception('No data found');
        }

        foreach ($data['data'] as $customer) {
            if ($customer['email'] === $email) {
                return;
            }
        }

        throw new \Exception("Can't find customer");
    }

    /**
     * @When I use the site to list customers with parameter :arg1 with value :arg2
     */
    public function iUseTheSiteToListCustomersWithParameterWithValue($filter, $value)
    {
        $this->sendJsonRequest('GET', sprintf('/app/customer?%s=%s', $filter, $value));
    }

    /**
     * @Then I should not see in the site response the customer :arg1
     */
    public function iShouldNotSeeInTheSiteResponseTheCustomer($email)
    {
        $data = $this->getJsonContent();

        if (!isset($data['data'])) {
            throw new \Exception('No data found');
        }

        foreach ($data['data'] as $customer) {
            if ($customer['email'] === $email) {
                throw new \Exception('Found customer');
            }
        }
    }

    /**
     * @Then the site response data field should be empty
     */
    public function theSiteResponseDataFieldShouldBeEmpty()
    {
        $data = $this->getJsonContent();

        if (count($data['data']) > 0) {
            throw new \Exception('Found values in the data field');
        }
    }

    /**
     * @Then I should see in the site response with only :arg1 result in the data set
     */
    public function iShouldSeeInTheSiteResponseWithOnlyResultInTheDataSet($arg1)
    {
        $data = $this->getJsonContent();

        if (count($data['data']) != intval($arg1)) {
            throw new \Exception(sprintf('Found %d results instead of %d', count($data['data']), $arg1));
        }
    }

    /**
     * @Then the I should see in the site response there are more results
     */
    public function theIShouldSeeInTheSiteResponseThereAreMoreResults()
    {
        $data = $this->getJsonContent();

        if (!$data['has_more']) {
            throw new \Exception('API Response does not say there are more');
        }
    }

    /**
     * @When I use the site to list customers with the last_key from the last response
     */
    public function iUseTheSiteToListCustomersWithTheLastKeyFromTheLastResponse()
    {
        $data = $this->getJsonContent();

        $this->sendJsonRequest('GET', sprintf('/app/customer?last_key=%s', $data['last_key']));
    }

    /**
     * @Then the I should not see in the site response there are more results
     */
    public function theIShouldNotSeeInTheSiteResponseThereAreMoreResults()
    {
        $data = $this->getJsonContent();
        if ($data['has_more']) {
            throw new \Exception('API Response does say there are more');
        }
    }

    /**
     * @When I create a customer via the app with the following info
     */
    public function iCreateACustomerViaTheAppWithTheFollowingInfo(TableNode $table)
    {
        $data = $table->getRowsHash();

        $payload = [
            'email' => $data['Email'],
            'country' => $data['Country'],
        ];

        if (isset($data['External Reference'])) {
            $payload['external_reference'] = $data['External Reference'];
        }

        if (isset($data['Reference'])) {
            $payload['reference'] = $data['Reference'];
        }

        $this->sendJsonRequest('POST', '/app/customer', $payload);
    }
}
