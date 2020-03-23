<?php


namespace EwsAuthAdapter;


use jamesiarmes\PhpEws\Client;
use jamesiarmes\PhpEws\Request\GetServerTimeZonesType;
use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\Authentication\Result as AuthenticationResult;
use Exception;

class Ews extends AbstractAdapter
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Ews constructor.
     * @param array $options
     * @param null $identity
     * @param null $credential
     */
    public function __construct(array $options = [], $identity = null, $credential = null)
    {
        $this->setOptions($options);
        if ($identity !== null) {
            $this->setIdentity($identity);
        }
        if ($credential !== null) {
            $this->setCredential($credential);
        }
    }

    /**
     * Returns the array of arrays of Ews options of this adapter.
     *
     * @return array|null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the array of arrays of Ews options to be used by
     * this adapter.
     *
     * @param array $options The array of arrays of Ews options
     * @return self Provides a fluent interface
     */
    public function setOptions($options)
    {
        $this->options = is_array($options) ? $options : [];

        return $this;
    }

    /**
     * Returns the username of the account being authenticated, or
     * NULL if none is set.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->getIdentity();
    }

    /**
     * Sets the username for binding
     *
     * @param string $username The username for binding
     * @return ValidatableAdapterInterface
     */
    public function setUsername($username)
    {
        return $this->setIdentity($username);
    }

    /**
     * Returns the password of the account being authenticated, or
     * NULL if none is set.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->getCredential();
    }

    /**
     * Sets the password for the account
     *
     * @param string $password The password of the account being authenticated
     * @return ValidatableAdapterInterface
     */
    public function setPassword($password)
    {
        return $this->setCredential($password);
    }


    /**
     * @return AuthenticationResult
     * @throws Exception
     */
    public function authenticate()
    {
        $messages = [];
        $username = $this->getUsername();
        $password = $this->getPassword();

        if (!$username) {
            $code       = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
            $messages[] = 'A username is required';
            return new AuthenticationResult($code, '', $messages);
        }
        if (!$password) {
            /* A password is required because some servers will
             * treat an empty password as an anonymous bind.
             */
            $code       = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $messages[] = 'A password is required';
            return new AuthenticationResult($code, '', $messages);
        }

        $clientVersion = Client::VERSION_2010;
        if (isset($this->options['clientVersion'])) {
            $clientVersion = $this->options['clientVersion'];
        }

        if (!isset($this->options['server'])) {
            throw new Exception('Missing "server"-entry in config');
        }

        $server  = $this->options['server'];
        $client  = new Client($server, $username, $password, $clientVersion);
        $request = new GetServerTimeZonesType();
        try {
            $client->GetServerTimeZones($request);

        } catch (Exception $exception) {
            $exceptionMessage = $exception->getMessage();
            $code             = AuthenticationResult::FAILURE_UNCATEGORIZED;

            if ($exceptionMessage === 'SOAP client returned status of 401.') {
                $code       = AuthenticationResult::FAILURE;
                $messages[] = 'Invalid or unknown credentials';
            }
            $messages[] = $exception->getMessage();

            return new AuthenticationResult($code, $username, $messages);
        }

        $messages[] = "$username authentication successful";
        return new AuthenticationResult(AuthenticationResult::SUCCESS, $username, $messages);
    }

    /**
     * @param mixed $identity
     * @return ValidatableAdapterInterface
     */
    public function setIdentity($identity)
    {
        if (isset($this->options['domain'])) {
            $identity = sprintf('%s\%s', $this->options['domain'], $identity);
        }

        return parent::setIdentity($identity);
    }
}