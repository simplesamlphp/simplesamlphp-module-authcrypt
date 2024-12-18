<?php

declare(strict_types=1);

namespace SimpleSAML\Module\authcrypt\Auth\Source;

use Exception;
use SimpleSAML\{Error, Logger, Utils};
use SimpleSAML\Module\core\Auth\UserPassBase;

use function explode;
use function is_string;

/**
 * Authentication source for username & hashed password.
 *
 * This class is an authentication source which stores all username/hashes in an array,
 * and authenticates users against this array.
 *
 * @package SimpleSAMLphp
 */

class Hash extends UserPassBase
{
    /**
     * Our users, stored in an associative array. The key of the array is "<username>:<passwordhash>",
     * while the value of each element is a new array with the attributes for each user.
     *
     * @var array<string, mixed>
     */
    private array $users;


    /**
     * Constructor for this authentication source.
     *
     * @param array<string, mixed> $info Information about this authentication source.
     * @param array<string, mixed> $config Configuration.
     *
     * @throws \Exception in case of a configuration error.
     */
    public function __construct(array $info, array $config)
    {
        // Call the parent constructor first, as required by the interface
        parent::__construct($info, $config);

        $this->users = [];

        // Validate and parse our configuration
        foreach ($config as $userpass => $attributes) {
            if (!is_string($userpass)) {
                throw new Exception('Invalid <username>:<passwordhash> for authentication source ' .
                    $this->authId . ': ' . $userpass);
            }

            $userpass = explode(':', $userpass, 2);
            if (count($userpass) !== 2) {
                throw new Exception('Invalid <username>:<passwordhash> for authentication source ' .
                    $this->authId . ': ' . $userpass[0]);
            }
            $username = $userpass[0];
            $passwordhash = $userpass[1];

            try {
                $attrUtils = new Utils\Attributes();
                $attributes = $attrUtils->normalizeAttributesArray($attributes);
            } catch (Exception $e) {
                throw new Exception('Invalid attributes for user ' . $username .
                    ' in authentication source ' . $this->authId . ': ' .
                    $e->getMessage());
            }

            $this->users[$username . ':' . $passwordhash] = $attributes;
        }
    }


    /**
     * Attempt to log in using the given username and password.
     *
     * On a successful login, this function should return the users attributes. On failure,
     * it should throw an exception. If the error was caused by the user entering the wrong
     * username OR password, a \SimpleSAML\Error\Error('WRONGUSERPASS') should be thrown.
     *
     * The username is UTF-8 encoded, and the hash is base64 encoded.
     *
     * @param string $username The username the user wrote.
     * @param string $password The password the user wrote.
     *
     * @return array<string, mixed> Associative array with the users attributes.
     *
     * @throws \SimpleSAML\Error\Error if authentication fails.
     */
    protected function login(
        string $username,
        #[\SensitiveParameter]
        string $password,
    ): array {
        $cryptoUtils = new Utils\Crypto();
        foreach ($this->users as $userpass => $attrs) {
            $matches = explode(':', $userpass, 2);
            if ($matches[0] === $username) {
                if ($cryptoUtils->pwValid($matches[1], $password)) {
                    return $attrs;
                } else {
                    Logger::debug('Incorrect password "' . $password . '" for user ' . $username);
                }
            }
        }
        throw new Error\Error('WRONGUSERPASS');
    }
}
